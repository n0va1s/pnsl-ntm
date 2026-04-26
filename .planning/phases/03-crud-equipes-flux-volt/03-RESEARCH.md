# Phase 3: CRUD de equipes (Flux/Volt) - Research

**Researched:** 2026-04-24
**Domain:** Laravel 12 + Livewire 3 Volt SFCs + Flux Free UI + EquipePolicy
**Confidence:** HIGH

---

## Summary

Phase 3 constrói a UI de gerenciamento de equipes sobre a fundação das Phases 1 e 2. Os modelos `Equipe`/`EquipeUsuario`, o enum `PapelEquipe`, e a `EquipePolicy` com `before()` estao todos em producao e testados. A fase entrega tres Volt SFCs (`equipes.index`, `equipes.create`, `equipes.edit`), rotas `equipes.*` com autorizacao via Gate, FormRequests separadas para store e update, e um conjunto de 3 arquivos de teste Feature.

O ponto mais critico apurado: **o `idt_movimento` do usuario logado esta no model `User`** (`users.idt_movimento`, FK para `tipo_movimento`), portanto o escopo de listagem por movimento e feito com `Equipe::paraMovimento(Auth::user()->idt_movimento)` diretamente. Nao e necessario nenhuma query adicional.

**Restricao de slug:** a unique constraint no DB e composta `(idt_movimento, des_slug)` - nao apenas `des_slug`. Isso simplifica o caso de arquivamento/restauracao: se uma equipe com mesmo nome e soft-deletada e recriada, a restricao composta continua valendo para o banco. A validacao PHP no `EquipeUpdateRequest` deve usar `Rule::unique('equipes', 'des_slug')->where('idt_movimento', $this->equipe->idt_movimento)->ignore($this->equipe->idt_equipe, 'idt_equipe')` - o `ignore()` nativo do Laravel exclui o registro atual sem precisar de logica especial de `deleted_at`.

**Padrao de validacao:** o projeto usa **FormRequest para operacoes HTTP classicas** (controladores existentes todos tem FormRequest). Volt SFCs existentes (`settings.profile`, `settings.password`) usam `$this->validate()` inline. Para Phase 3, a decisao recomendada e: criar `EquipeStoreRequest` e `EquipeUpdateRequest` como FormRequests normais e **injetar no `mount()`/action do Volt via type-hint** — o container Laravel resolve FormRequests dentro de Volt. Mas o padrao mais simples e testavel encontrado nos Volt SFCs existentes e `$this->validate([...])` inline com `Rule::unique(...)`. Ambos sao validos; usar inline simplifica e segue o padrao estabelecido nos SFCs existentes.

**Primary recommendation:** Usar `$this->validate()` inline nos Volt SFCs para create/edit (seguindo `settings.profile`), manter FormRequests (`EquipeStoreRequest`, `EquipeUpdateRequest`) como classes de documentacao/reutilizacao, e usar `$this->authorize()` via `AuthorizesRequests` (disponivel em todo Livewire Component) no `mount()` de cada SFC.

---

## Architectural Responsibility Map

| Capability | Primary Tier | Secondary Tier | Rationale |
|------------|-------------|----------------|-----------|
| Listagem de equipes (index) | Frontend Server (Volt SSR) | Database | Volt SFC renderiza no server; Eloquent scope filtra por movimento |
| Criacao de equipe (create/store) | Frontend Server (Volt SSR) | Database | Volt action faz validate + Equipe::create; nao ha endpoint HTTP separado |
| Edicao + toggle ativo (edit/update) | Frontend Server (Volt SSR) | Database | Volt action; toggle ind_ativa e uma simples atualizacao de coluna |
| Arquivamento (soft-delete) | Frontend Server (Volt SSR) | Database | `$equipe->delete()` ativa SoftDeletes; pivot equipe_usuario preservada pela FK cascadeOnDelete desativada |
| Autorizacao (viewAny/update/view) | API / Backend (Gate) | Frontend Server | `$this->authorize()` em Volt chama Gate -> EquipePolicy::before() |
| Validacao de unicidade de slug | API / Backend (FormRequest / validate()) | Database | `Rule::unique('equipes', 'des_slug')->where()->ignore()` |
| Rotas com middleware | API / Backend | — | `Volt::route()` + `->can()` ou middleware dentro do grupo `auth` |

---

## Standard Stack

### Core
| Library | Version | Purpose | Why Standard |
|---------|---------|---------|--------------|
| livewire/volt | ^1.7.0 [VERIFIED: composer.json] | Volt SFCs com class anonima | Padrao do projeto — settings.profile, auth/* todos Volt |
| livewire/flux | ^2.1.1 [VERIFIED: composer.json] | Componentes UI (Free) | Padrao do projeto — flux:input, flux:button, flux:table usados em todo o projeto |
| pestphp/pest | ^3.8 [VERIFIED: composer.json] | Testes com `Volt::test()` | Padrao do projeto — todos os testes usam Pest |

### Supporting
| Library | Version | Purpose | When to Use |
|---------|---------|---------|-------------|
| pestphp/pest-plugin-laravel | ^3.2 [VERIFIED: composer.json] | `Volt::test()`, `actingAs()` | Necessario para todos os testes de SFC Volt |
| Illuminate\Validation\Rule | Laravel 12 [VERIFIED: livewire/settings/profile.blade.php] | `Rule::unique()->ignore()` | Validacao de slug no update ignorando o proprio registro |

### Alternatives Considered
| Instead of | Could Use | Tradeoff |
|------------|-----------|----------|
| `$this->validate()` inline | FormRequest injetado no Volt action | FormRequest e HTTP-bound; Volt.test() nao dispatcha HTTP request real — `$this->validate()` inline e mais direto e testavel via `assertHasErrors` |
| `Volt::route()->can('update', Equipe::class)` | `$this->authorize('update', $this->equipe)` no `mount()` | `->can()` na rota bloqueia antes de renderizar; `authorize()` no mount() e mais preciso (modelo carregado). Recomendacao: usar ambos em camadas |

**Instalacao:** Sem instalacoes novas — todo o stack ja esta no composer.json.

---

## Architecture Patterns

### System Architecture Diagram

```
Request (usuario logado)
    |
    v
routes/web.php
    [grupo auth]
        Volt::route('/equipes', 'equipes.index')          -> nome equipes.index
        Volt::route('/equipes/create', 'equipes.create')  -> nome equipes.create
        Volt::route('/equipes/{equipe}/edit', 'equipes.edit') -> nome equipes.edit
    |
    v
Volt SFC: new class extends Component (mount())
    |-- $this->authorize('viewAny', Equipe::class)  ----> EquipePolicy::before() --> true (coord-geral) / viewAny() --> true (qualquer auth)
    |-- $this->authorize('create', Equipe::class)   ----> EquipePolicy::before() --> true (coord-geral) / 403 outros
    |-- $this->authorize('update', $equipe)         ----> EquipePolicy::before() ou update()
    |
    v
Volt action (salvar(), arquivar(), toggleAtivo())
    |-- $this->validate([rules])   -----> ValidationException -> Flux error display
    |-- Equipe::create() / $equipe->update() / $equipe->delete()
    |
    v
Eloquent (Model Equipe + SoftDeletes)
    |-- equipes table (idt_equipe, idt_movimento, nom_equipe, des_slug, des_descricao, ind_ativa, deleted_at)
    |
    v
Flash / redirect via $this->redirect(route('equipes.index'), navigate: true)
```

### Recommended Project Structure
```
resources/views/livewire/
└── equipes/
    ├── index.blade.php     # Volt SFC: lista + arquivar + toggle ativo
    ├── create.blade.php    # Volt SFC: formulario de criacao
    └── edit.blade.php      # Volt SFC: formulario de edicao + toggle ativo

app/Http/Requests/
├── EquipeStoreRequest.php  # FormRequest: validacao para store (documentacao/reutilizacao)
└── EquipeUpdateRequest.php # FormRequest: validacao para update com ignore(slug)

tests/Feature/Equipes/
├── EquipeCrudTest.php          # EQUIPE-04, EQUIPE-05, EQUIPE-06, EQUIPE-07, EQUIPE-09
└── EquipeArquivamentoTest.php  # EQUIPE-10
```

### Pattern 1: Volt SFC com authorize() no mount()

**What:** SFC usa `$this->authorize()` no `mount()` para bloquear acesso antes de qualquer render. Livewire Component herda `AuthorizesRequests` trait do Laravel, portanto `$this->authorize()` funciona identicamente ao controller.

**When to use:** Sempre que a tela inteira requer uma permissao especifica (ex: create/edit so para coord-geral).

```php
// Source: vendor/livewire/livewire/src/Component.php (usa AuthorizesRequests)
// Padrao de: tests/Feature/Autorizacao/02-02-SUMMARY.md (padrão estabelecido)

new class extends Component {
    public Equipe $equipe;

    public function mount(Equipe $equipe): void
    {
        $this->authorize('update', $equipe);  // 403 se nao autorizado
        $this->equipe = $equipe;
        $this->nom_equipe = $equipe->nom_equipe;
        $this->des_descricao = $equipe->des_descricao ?? '';
        $this->ind_ativa = $equipe->ind_ativa;
    }
    // ...
};
```

### Pattern 2: Validacao inline com Rule::unique ignore

**What:** `$this->validate()` dentro do Volt action, usando `Rule::unique()->where()->ignore()` para o update nao colidir com o proprio slug do registro sendo editado.

**When to use:** Update de equipe — slug deve ser unico dentro do mesmo movimento, exceto para o proprio registro.

**Nota critica sobre a unique constraint do banco:**
A constraint e `equipes_movimento_slug_unique` em `(idt_movimento, des_slug)`. Portanto a validacao PHP tambem deve escopar por `idt_movimento`:

```php
// Source: database/migrations/2026_04_21_000001_create_equipes_table.php
// Constraint: unique(['idt_movimento', 'des_slug'], 'equipes_movimento_slug_unique')

// EquipeUpdateRequest::rules() / validacao inline no Volt:
'des_slug' => [
    'nullable',
    'string',
    'max:120',
    'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
    Rule::unique('equipes', 'des_slug')
        ->where('idt_movimento', $this->equipe->idt_movimento)
        ->ignore($this->equipe->idt_equipe, 'idt_equipe'),
],
```

### Pattern 3: Volt SFC index com scope paraMovimento

**What:** Index filtra equipes pelo `idt_movimento` do usuario logado. O campo `idt_movimento` existe em `users` (FK, nullable). SFC carrega a colecao no `mount()`.

```php
// Source: app/Models/Equipe.php (scopeParaMovimento verificado)
// Source: database/migrations/2025_07_16_123833_add_role_to_users_table.php (users.idt_movimento confirmado)

public function mount(): void
{
    $this->authorize('viewAny', Equipe::class);
    $this->equipes = Equipe::paraMovimento(Auth::user()->idt_movimento)
        ->withTrashed()  // index mostra ativas + arquivadas para coord-geral
        ->orderBy('nom_equipe')
        ->get();
}
```

**Nota:** Para usuarios `membro-equipe`/`coord-equipe-h/m`, o index deve mostrar apenas equipes ativas. A policy `viewAny` permite qualquer autenticado — a filtragem de ativas/inativas e responsabilidade do scope na query, nao da policy.

### Pattern 4: Toggle ind_ativa e arquivamento soft-delete

**What:** Dois comportamentos distintos:
- `toggleAtivo()`: altera `ind_ativa` (true/false) sem deletar — equipe continua existindo
- `arquivar()`: executa `$equipe->delete()` que ativa `SoftDeletes` (preenche `deleted_at`)

A FK `equipe_usuario.idt_equipe` tem `cascadeOnDelete()` — isso **exclui fisicamente** os registros da pivot ao deletar a equipe. Para preservar historico (EQUIPE-10), **nao usar cascadeOnDelete** ou verificar que o cascade so existe para delete fisico (nao soft-delete).

**Verificacao critica:** [VERIFIED: database/migrations/2026_04_21_000002_create_equipe_usuario_table.php]
```php
$table->foreignId('idt_equipe')->constrained('equipes', 'idt_equipe')->cascadeOnDelete();
```
`cascadeOnDelete()` no Eloquent SoftDeletes: quando `$equipe->delete()` e chamado em um model com SoftDeletes, apenas `deleted_at` e preenchido — o cascade fisico do DB **nao e disparado** porque nenhuma linha e fisicamente deletada. A FK cascade so atua em `forceDelete()`. Portanto, os registros de `equipe_usuario` sao preservados no soft-delete. [ASSUMED: comportamento de cascade com SoftDeletes no MySQL/SQLite — baseado em conhecimento de treinamento, mas e comportamento padrao documentado do Laravel SoftDeletes]

```php
// Source: [ASSUMED] Laravel 12 SoftDeletes behavior
public function arquivar(): void
{
    $this->authorize('update', $this->equipe);
    $this->equipe->delete();  // Apenas preenche deleted_at — cascadeOnDelete nao e disparado
    $this->redirect(route('equipes.index'), navigate: true);
}

public function restaurar(int $idtEquipe): void
{
    $equipe = Equipe::withTrashed()->findOrFail($idtEquipe);
    $this->authorize('update', $equipe);
    $equipe->restore();
    // Recarregar lista
}
```

### Pattern 5: Teste de Volt SFC com Volt::test()

**What:** Padrao estabelecido no projeto em `tests/Feature/Settings/ProfileUpdateTest.php`.

```php
// Source: tests/Feature/Settings/ProfileUpdateTest.php [VERIFIED]

use Livewire\Volt\Volt;

it('coord-geral pode acessar index de equipes', function () {
    $coordGeral = User::factory()->create();
    $equipe = Equipe::factory()->create();
    $coordGeral->equipes()->attach($equipe->idt_equipe, ['papel' => PapelEquipe::CoordGeral->value]);

    $this->actingAs($coordGeral);

    $this->get(route('equipes.index'))
        ->assertOk();
});

it('coord-geral pode criar equipe', function () {
    $vem = TipoMovimento::where('des_sigla', 'VEM')->firstOrFail();
    $coordGeral = User::factory()->create(['idt_movimento' => $vem->idt_movimento]);
    $equipe = Equipe::factory()->create();
    $coordGeral->equipes()->attach($equipe->idt_equipe, ['papel' => PapelEquipe::CoordGeral->value]);

    $this->actingAs($coordGeral);

    Volt::test('equipes.create')
        ->set('nom_equipe', 'Minha Equipe')
        ->set('des_descricao', 'Descricao da equipe')
        ->call('salvar')
        ->assertHasNoErrors()
        ->assertRedirect(route('equipes.index'));

    expect(Equipe::where('nom_equipe', 'Minha Equipe')->exists())->toBeTrue();
});

it('user sem coord-geral recebe 403 no create', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('equipes.create'))
        ->assertStatus(403);
});
```

**Nota sobre withoutVite():** Testes que fazem GET em rotas que renderizam views (assertOk/status 200) precisam de `$this->withoutVite()` pois o manifest Vite nao existe no ambiente de testes. Testes 403 nao precisam (policy aborta antes de renderizar). [VERIFIED: 02-02-SUMMARY.md decision D-14]

### Pattern 6: Rotas Volt no web.php

**What:** Rotas Volt sao declaradas com `Volt::route()` dentro do grupo `auth` existente. A autorizacao e feita com `->can()` na rota (bloqueia antes de resolver o componente) E com `$this->authorize()` no `mount()` (bloqueia antes de renderizar).

```php
// Source: routes/web.php (padrao Volt::route ja em uso em settings.*)

Route::middleware(['auth'])->group(function () {
    // ... rotas existentes ...

    Volt::route('/equipes', 'equipes.index')
        ->name('equipes.index');

    Volt::route('/equipes/create', 'equipes.create')
        ->name('equipes.create')
        ->can('create', Equipe::class);

    Volt::route('/equipes/{equipe}/edit', 'equipes.edit')
        ->name('equipes.edit')
        ->can('update', 'equipe');

    // show e opcional per ROADMAP — omitir em Phase 3 se nao requerido por testes
});
```

**Nota sobre `->can()` na rota:** `->can('create', Equipe::class)` passa a classe como subject; `->can('update', 'equipe')` passa o route model binding. O Gate resolvera via `EquipePolicy::before()` para coord-geral em ambos os casos.

### Anti-Patterns to Avoid

- **FormRequest injetado diretamente no Volt action:** `public function salvar(EquipeStoreRequest $request)` — o container nao resolve FormRequest via DI em actions Volt da mesma forma que em controllers HTTP. Usar `$this->validate()` inline.
- **Abrir `equipe_usuario` para alteracao em Phase 3:** Phase 3 e CRUD de equipes (a entidade `Equipe`), nao gerenciamento de membros (Phase 4). Nao criar UI de membros nesta fase.
- **Usar `withTimestamps()` na relacao:** D-09 estabelecido — a relacao `User::equipes()` nao usa `withTimestamps()` porque pivot usa `dat_*` manual.
- **unique constraint so por slug sem escopo por movimento:** A constraint do banco e `(idt_movimento, des_slug)`. A validacao PHP deve espelhar o mesmo escopo.
- **`forceDelete()` para arquivamento:** Usar `delete()` (soft) para preservar historico da pivot (EQUIPE-10).

---

## Don't Hand-Roll

| Problem | Don't Build | Use Instead | Why |
|---------|-------------|-------------|-----|
| Validacao de unicidade com ignore | Logica custom de unique check | `Rule::unique()->where()->ignore()` | Laravel resolve corretamente ignorando o idt_equipe (PK customizada) com o segundo argumento de `ignore()` |
| Autorizacao no Volt | `if (!$user->isCoordenadorGeral()) abort(403)` | `$this->authorize('update', $equipe)` | `authorize()` ja disponivel via `AuthorizesRequests` — integra com Gate/Policy corretamente |
| Slug auto-gerado | Logica JS ou PHP separada | Mutator `setNomEquipeAttribute` ja no model | Mutator ja existe em `Equipe.php` — apenas deixar `des_slug` em branco no create e o mutator preenchem |
| Paginacao custom | Array slicing | `flux:pagination` + `->paginate()` | Flux Free tem o componente `flux:pagination`; Livewire `WithPagination` funciona em Volt |
| Toggle boolean custom | Radio/select para ind_ativa | `flux:switch` (Free) | Componente Free confirmado em stubs — `wire:model="ind_ativa"` |

**Key insight:** O mutator de slug ja existe no model e resolve acentuacao pt_BR (oração → oracao, Emaús → emaus). Nao reimplementar.

---

## Runtime State Inventory

Fase nao e rename/refactor — omitido.

---

## Common Pitfalls

### Pitfall 1: Unique constraint (idt_movimento, des_slug) ignorada na validacao

**What goes wrong:** Desenvolvedor escreve `Rule::unique('equipes', 'des_slug')->ignore(...)` sem o `->where('idt_movimento', ...)`. A validacao passa, mas o DB lanca `UNIQUE constraint failed` se outra equipe do mesmo movimento (mas diferente) ja tem o mesmo slug — ou, pior, permite duplicata cross-movimento.

**Why it happens:** A constraint no banco e composta; a validacao PHP precisa ser igualmente composta.

**How to avoid:** Sempre incluir `->where('idt_movimento', $idtMovimento)` no `Rule::unique`.

**Warning signs:** Teste cria equipe com mesmo slug mas `idt_movimento` diferente e passa — indica que a constraint nao esta sendo espelhada na validacao.

### Pitfall 2: withoutVite() faltando em testes GET que retornam 200

**What goes wrong:** `$this->get(route('equipes.index'))->assertOk()` falha com HTTP 500 (ViteManifestNotFoundException) mesmo com usuario autorizado.

**Why it happens:** O manifest Vite nao existe no ambiente de testes local (pre-existente em todo o projeto).

**How to avoid:** Adicionar `$this->withoutVite()` antes de `actingAs()` em todos os testes que assertam status 200 e renderizam views. Testes 403 nao precisam. [VERIFIED: D-14 em STATE.md e 02-02-SUMMARY.md]

### Pitfall 3: Route model binding com PK customizada

**What goes wrong:** `Volt::route('/equipes/{equipe}/edit', ...)` espera que o Laravel resolva `{equipe}` como `idt_equipe`. Por padrao, Laravel usa `id`. O model `Equipe` tem `protected $primaryKey = 'idt_equipe'` — o binding deve funcionar automaticamente, mas soft-deleted records nao sao resolvidos por padrao.

**Why it happens:** Route model binding padrao usa `findOrFail` que aplica o scope global de SoftDeletes.

**How to avoid:** Para permitir edicao de equipes arquivadas (restauracao), usar `->withTrashed()` no binding ou buscar manualmente no `mount()`. Para Phase 3 (apenas ativas), o comportamento padrao e correto — 404 em equipes arquivadas e aceitavel.

**Warning signs:** Acessar `/equipes/{id}/edit` de uma equipe arquivada retorna 404 em vez de permitir restauracao — se restauracao for necessaria na Phase 3.

### Pitfall 4: `coordGeral->equipes()->attach()` sem TipoMovimento VEM no banco

**What goes wrong:** Testes que criam coordGeral via factory e tentam `attach()` falham porque `EquipeFactory` usa `TipoMovimento::where('des_sigla', 'VEM')` que pode nao existir no banco de testes (RefreshDatabase limpa tudo).

**Why it happens:** `EquipeFactory::definition()` faz `firstOrCreate` de VEM, mas em testes que nao chamam o factory diretamente e sim constroem manualmente a equipe, o VEM pode nao existir.

**How to avoid:** No `beforeEach()` dos testes de Phase 3, usar o mesmo padrao de `EquipePolicyHttpTest.php`:
```php
$this->vem = TipoMovimento::firstOrCreate(
    ['des_sigla' => 'VEM'],
    ['nom_movimento' => 'Encontro de Adolescentes com Cristo', 'dat_inicio' => '2000-07-01']
);
$this->equipe = Equipe::factory()->create(['idt_movimento' => $this->vem->idt_movimento]);
```
[VERIFIED: tests/Feature/Autorizacao/EquipePolicyHttpTest.php]

### Pitfall 5: Pint remove import de Equipe em User.php (D-13)

**What goes wrong:** Ao adicionar `use App\Models\Equipe;` em novos arquivos no namespace `App\Models`, o Pint o remove automaticamente como `no_unused_imports` (se o arquivo ja esta no mesmo namespace).

**Why it happens:** Decision D-13 — PHP resolve via namespace sem import quando no mesmo namespace.

**How to avoid:** Em arquivos fora de `App\Models` (ex: Volt SFCs, testes, FormRequests), sempre declarar `use App\Models\Equipe;` explicitamente. Em arquivos dentro de `App\Models`, nao adicionar — Pint vai remover.

### Pitfall 6: EQUIPE-08 (validacao H+M) pertence a Phase 4

**What goes wrong:** EQUIPE-08 diz "cada equipe aceita maximo 1 coord-equipe-h e 1 coord-equipe-m". Tentar aplicar isso em Phase 3 cria uma validacao falsa, porque os formularios de create/edit de equipe nao escrevem papel de usuario na pivot.

**Clarificacao:** EQUIPE-08 foi movido para Phase 4, junto de ATRIB-06 e TEST-04. Phase 3 nao deve criar `EquipeHMValidationTest.php` nem comentario de enforcement em FormRequest. A validacao real deve acontecer no fluxo de atribuicao/troca de papel, onde existem `equipe_id`, `user_id` e `papel`.

---

## Code Examples

### EquipeStoreRequest
```php
// app/Http/Requests/EquipeStoreRequest.php
// Padrao: app/Http/Requests/PessoaRequest.php [VERIFIED]

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class EquipeStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Equipe::class);
    }

    public function rules(): array
    {
        return [
            'nom_equipe'   => ['required', 'string', 'max:60'],
            'des_slug'     => [
                'nullable', 'string', 'max:120',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('equipes', 'des_slug')
                    ->where('idt_movimento', Auth::user()->idt_movimento),
            ],
            'des_descricao' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'nom_equipe.required' => 'O nome da equipe e obrigatorio.',
            'nom_equipe.max'      => 'O nome da equipe nao pode ter mais de 60 caracteres.',
            'des_slug.unique'     => 'Ja existe uma equipe com esse slug neste movimento.',
            'des_descricao.max'   => 'A descricao nao pode ter mais de 500 caracteres.',
        ];
    }
}
```

### EquipeUpdateRequest (slug com ignore)
```php
// app/Http/Requests/EquipeUpdateRequest.php
// Rule::unique com where+ignore [VERIFIED: padrao Rule::unique->ignore em settings/profile.blade.php]

public function rules(): array
{
    $equipe = $this->route('equipe');  // Route model binding

    return [
        'nom_equipe'    => ['required', 'string', 'max:60'],
        'des_slug'      => [
            'nullable', 'string', 'max:120',
            'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            Rule::unique('equipes', 'des_slug')
                ->where('idt_movimento', $equipe->idt_movimento)
                ->ignore($equipe->idt_equipe, 'idt_equipe'),
        ],
        'des_descricao' => ['nullable', 'string', 'max:500'],
        'ind_ativa'     => ['boolean'],
    ];
}
```

### Volt SFC equipes.index (esqueleto)
```php
// resources/views/livewire/equipes/index.blade.php
// Padrao: settings/profile.blade.php [VERIFIED] + EquipePolicyHttpTest.php [VERIFIED]

<?php
use App\Models\Equipe;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public $equipes;

    public function mount(): void
    {
        $this->authorize('viewAny', Equipe::class);
        $this->carregarEquipes();
    }

    public function carregarEquipes(): void
    {
        $this->equipes = Equipe::paraMovimento(Auth::user()->idt_movimento)
            ->orderBy('nom_equipe')
            ->get();
    }

    public function arquivar(int $idtEquipe): void
    {
        $equipe = Equipe::findOrFail($idtEquipe);
        $this->authorize('update', $equipe);
        $equipe->delete();
        $this->carregarEquipes();
        session()->flash('success', 'Equipe arquivada com sucesso.');
    }
}; ?>

<div>
    <flux:heading>Equipes</flux:heading>
    {{-- flux:table com colunas nom_equipe, ind_ativa, acoes --}}
    {{-- flux:button para /equipes/create se can('create', Equipe::class) --}}
</div>
```

### Volt SFC equipes.create (esqueleto)
```php
<?php
use App\Models\Equipe;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component {
    public string $nom_equipe = '';
    public string $des_slug = '';
    public string $des_descricao = '';

    public function mount(): void
    {
        $this->authorize('create', Equipe::class);
    }

    public function salvar(): void
    {
        $validated = $this->validate([
            'nom_equipe'    => ['required', 'string', 'max:60'],
            'des_slug'      => [
                'nullable', 'string', 'max:120',
                Rule::unique('equipes', 'des_slug')
                    ->where('idt_movimento', Auth::user()->idt_movimento),
            ],
            'des_descricao' => ['nullable', 'string', 'max:500'],
        ]);

        Equipe::create(array_merge($validated, [
            'idt_movimento' => Auth::user()->idt_movimento,
        ]));

        $this->redirect(route('equipes.index'), navigate: true);
    }
}; ?>
```

---

## State of the Art

| Old Approach | Current Approach | When Changed | Impact |
|--------------|------------------|--------------|--------|
| Controllers com Blade views para CRUD | Volt SFCs com $this->validate() inline | Laravel 12 / Livewire 3 era | SFCs eliminam round-trip HTTP para validacao; testes usam Volt::test() |
| `new EquipePolicy` com `$policies` em AuthServiceProvider | Auto-discovery de policy | — | Projeto usa registro explicito (D-10) para garantir precedencia sobre auto-discovery — manter |
| `$equipe->delete()` quebra pivot | SoftDeletes nao dispara cascade fisico | Sempre foi assim | CascadeOnDelete no DB so atua em `forceDelete()`; soft-delete preserva pivot |

---

## Assumptions Log

| # | Claim | Section | Risk if Wrong |
|---|-------|---------|---------------|
| A1 | `cascadeOnDelete()` em FK de `equipe_usuario` nao e executado em soft-delete (apenas em `forceDelete()`) | Architecture Patterns - Pattern 4 | Se errado, arquivar equipe destruiria historico da pivot — requereria `forceDelete()` bloqueado |
| A2 | `$this->authorize()` disponivel em Volt SFC via heranca de `Livewire\Component` que usa `AuthorizesRequests` | Architecture Patterns - Pattern 1 | Se errado, seria necessario `Gate::authorize()` estatico ou abort(403) manual |
| A3 | `Volt::route()->can('create', Equipe::class)` funciona corretamente com `EquipePolicy::before()` para coord-geral | Architecture Patterns - Pattern 6 | Se errado, coord-geral seria bloqueado pela rota antes de chegar ao SFC |

**A1 tem baixo risco:** Laravel SoftDeletes e comportamento core e bem documentado. Verificar em teste de arquivamento.
**A2 tem risco minimo:** Confirmado que `Livewire\Component` usa `AuthorizesRequests` no vendor [VERIFIED: vendor/livewire/livewire/src/Component.php].
**A3 tem risco baixo:** `->can()` usa o Gate padrao que consulta a Policy registrada. O `before()` da `EquipePolicy` intercepta antes do metodo `create()`. [VERIFIED: EquipePolicyHttpTest.php prova que Gate funciona corretamente para coord-geral]

---

## Open Questions

1. **[RESOLVED] Slug editavel ou apenas auto-gerado?**
   - What we know: O mutator `setNomEquipeAttribute` auto-gera slug apenas quando `des_slug` esta vazio
   - Decision: O formulario permite informar `des_slug`, mas ele continua opcional. Quando omitido, o mutator do model gera o slug a partir de `nom_equipe`.
   - Rationale: Mantem o fluxo simples para CRUD comum e preserva a capacidade de correcao manual em casos de acento, abreviacao ou colisao.

2. **[RESOLVED] Index mostra equipes arquivadas para coord-geral?**
   - What we know: `Equipe::ativas()` filtra `ind_ativa = true`; `Equipe::withTrashed()` inclui soft-deleted
   - Decision: O index de coord-geral mostra equipes ativas, inativas e arquivadas do movimento, com indicador visual de status e acao de restaurar quando aplicavel.
   - Rationale: Phase 3 precisa provar arquivamento e restauracao; esconder soft-deleted impediria o fluxo de restauracao na propria UI.

3. **[RESOLVED] EQUIPE-08 em Phase 3: o que exatamente testar?**
   - What we know: Formulario de equipe (create/edit) nao atribui membros; atribuicao e Phase 4
   - Decision: EQUIPE-08 foi formalmente movido para Phase 4, junto de ATRIB-06 e TEST-04. Phase 3 nao cria `EquipeHMValidationTest.php` nem placeholder.
   - Rationale: A regra H+M depende da operacao de atribuir/trocar papel em `equipe_usuario`; o CRUD da entidade `Equipe` nao recebe papel de membro e nao consegue aplicar essa regra de forma real.

---

## Environment Availability

| Dependency | Required By | Available | Version | Fallback |
|------------|------------|-----------|---------|----------|
| PHP 8.2+ | Laravel 12 | [ASSUMED: ambiente dev XAMPP] | ^8.2 | — |
| SQLite | Tests (phpunit.xml) | Confirmado via phpunit.xml | — | — |
| Pest 3.8 | Todos os testes | VERIFIED: composer.json | ^3.8 | — |
| livewire/volt | Volt SFCs | VERIFIED: composer.json | ^1.7.0 | — |
| livewire/flux | Componentes UI | VERIFIED: composer.json | ^2.1.1 | — |

**Nota GD:** STATE.md documenta que GD extension nao esta instalada no XAMPP dev, impedindo `migrate:fresh --seed` completo (EventoSeeder usa GD). Nao afeta Phase 3 (sem imagens).

---

## Validation Architecture

### Test Framework
| Property | Value |
|----------|-------|
| Framework | Pest 3.8 + pest-plugin-laravel 3.2 |
| Config file | phpunit.xml (DB_DATABASE = database/testing.sqlite) |
| Quick run command | `./vendor/bin/pest tests/Feature/Equipes/` |
| Full suite command | `./vendor/bin/pest` |

### Phase Requirements -> Test Map
| Req ID | Behavior | Test Type | Automated Command | File Exists? |
|--------|----------|-----------|-------------------|-------------|
| EQUIPE-04 | EquipeStoreRequest + EquipeUpdateRequest com regras corretas | Unit | `./vendor/bin/pest tests/Feature/Equipes/EquipeCrudTest.php` | Nao - Wave 0 |
| EQUIPE-05 | index renderiza equipes do movimento do usuario logado | Feature | `./vendor/bin/pest tests/Feature/Equipes/EquipeCrudTest.php` | Nao - Wave 0 |
| EQUIPE-06 | create acessivel apenas por coord-geral; salva corretamente | Feature | `./vendor/bin/pest tests/Feature/Equipes/EquipeCrudTest.php` | Nao - Wave 0 |
| EQUIPE-07 | edit funciona; toggle ind_ativa persiste | Feature | `./vendor/bin/pest tests/Feature/Equipes/EquipeCrudTest.php` | Nao - Wave 0 |
| EQUIPE-09 | rotas retornam 403 para papeis nao autorizados | Feature | `./vendor/bin/pest tests/Feature/Equipes/EquipeCrudTest.php` | Nao - Wave 0 |
| EQUIPE-10 | soft-delete preserva pivot equipe_usuario; restauracao funciona | Feature | `./vendor/bin/pest tests/Feature/Equipes/EquipeArquivamentoTest.php` | Nao - Wave 0 |

### Sampling Rate
- **Per task commit:** `./vendor/bin/pest tests/Feature/Equipes/`
- **Per wave merge:** `./vendor/bin/pest`
- **Phase gate:** Suite completa verde antes de `/gsd-verify-work`

### Wave 0 Gaps
- [ ] `tests/Feature/Equipes/EquipeCrudTest.php` — EQUIPE-04, EQUIPE-05, EQUIPE-06, EQUIPE-07, EQUIPE-09
- [ ] `tests/Feature/Equipes/EquipeArquivamentoTest.php` — EQUIPE-10

---

## Security Domain

### Applicable ASVS Categories

| ASVS Category | Applies | Standard Control |
|---------------|---------|-----------------|
| V2 Authentication | nao | Auth gerenciada pelo middleware `auth` existente |
| V3 Session Management | nao | Sessao gerenciada pelo Laravel padrao |
| V4 Access Control | sim | `EquipePolicy` + `$this->authorize()` em Volt; `->can()` em rotas |
| V5 Input Validation | sim | `Rule::unique()`, max lengths, nullable, string type em todos os campos |
| V6 Cryptography | nao | Sem operacoes criptograficas nesta fase |

### Known Threat Patterns for Volt SFC + Eloquent

| Pattern | STRIDE | Standard Mitigation |
|---------|--------|---------------------|
| IDOR (acesso direto a equipe de outro movimento via URL) | Elevation of Privilege | `$this->authorize('update', $equipe)` + scope `paraMovimento` na query |
| Mass assignment em Equipe::create() | Tampering | `$fillable` definido no model [VERIFIED: app/Models/Equipe.php] |
| XSS via nom_equipe/des_descricao exibidos sem escape | XSS | Blade escapa `{{ }}` por padrao; nunca usar `{!! !!}` nesses campos |
| Slug injetado para bypassing de unique (equipe de outro movimento) | Tampering | `Rule::unique()->where('idt_movimento', ...)` escopa a validacao |

---

## Sources

### Primary (HIGH confidence)
- `app/Models/Equipe.php` [VERIFIED] — campos, fillable, scopes, mutator, relacoes
- `app/Policies/EquipePolicy.php` [VERIFIED] — habilidades, before(), update() Response
- `app/Models/User.php` [VERIFIED] — isCoordenadorGeral(), isCoordenadorDe(), isMembroDe(), idt_movimento
- `app/Enums/PapelEquipe.php` [VERIFIED] — valores snake_case, label pt_BR, opcoes()
- `database/migrations/2026_04_21_000001_create_equipes_table.php` [VERIFIED] — schema, constraint composta (idt_movimento, des_slug)
- `database/migrations/2026_04_21_000002_create_equipe_usuario_table.php` [VERIFIED] — cascadeOnDelete na FK idt_equipe
- `database/migrations/2025_07_16_123833_add_role_to_users_table.php` [VERIFIED] — users.idt_movimento existe e e FK nullable
- `resources/views/livewire/settings/profile.blade.php` [VERIFIED] — padrao Volt SFC: new class extends Component, validate inline, Rule::unique->ignore
- `resources/views/livewire/settings/password.blade.php` [VERIFIED] — padrao Volt SFC simples
- `tests/Feature/Settings/ProfileUpdateTest.php` [VERIFIED] — padrao Volt::test(), actingAs, assertHasNoErrors
- `tests/Feature/Autorizacao/EquipePolicyHttpTest.php` [VERIFIED] — padrao beforeEach com TipoMovimento::firstOrCreate, attach coord-geral
- `vendor/livewire/flux/stubs/resources/views/flux/` [VERIFIED] — lista completa de componentes Free: table/, modal/, select/, switch, input, button, error
- `vendor/livewire/livewire/src/Component.php` [VERIFIED] — usa AuthorizesRequests trait
- `routes/web.php` [VERIFIED] — estrutura de grupos, Volt::route() pattern para settings.*
- `composer.json` [VERIFIED] — livewire/flux ^2.1.1, livewire/volt ^1.7.0, pestphp/pest ^3.8
- `.planning/STATE.md` [VERIFIED] — decisions D-09, D-10, D-11, D-13, D-14, blockers pre-existentes
- `02-01-SUMMARY.md`, `02-02-SUMMARY.md` [VERIFIED] — Phase 2 entregue, contrato de autorizacao pronto

### Secondary (MEDIUM confidence)
- `.planning/ROADMAP.md` - Phase 3 success criteria, artifacts, risks
- `.planning/REQUIREMENTS.md` - EQUIPE-04 a EQUIPE-10 detalhados

### Tertiary (LOW confidence)
- Comportamento de `cascadeOnDelete` com `SoftDeletes` (A1) — conhecimento de treinamento, nao verificado via execucao

---

## Metadata

**Confidence breakdown:**
- Standard stack: HIGH — tudo verificado em composer.json e vendor/
- Architecture: HIGH — baseado em codigo existente verificado (Phase 1, 2 summaries, models, policies, Volt SFCs)
- Pitfalls: HIGH — a maioria derivada de decisions documentadas em STATE.md + code verificado
- Teste de soft-delete com cascade: MEDIUM — A1 e [ASSUMED] comportamento padrao do Laravel

**Research date:** 2026-04-24
**Valid until:** 2026-05-24 (stack estavel; Flux/Volt minor versions podem mudar APIs de componente)

---

## RESEARCH COMPLETE
