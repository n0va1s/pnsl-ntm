# Phase 3: CRUD de equipes (Flux/Volt) - Pattern Map

**Mapped:** 2026-04-23
**Files analyzed:** 9 (3 Volt SFCs + 2 FormRequests + 1 route file + 3 test files)
**Analogs found:** 9 / 9

---

## File Classification

| New/Modified File | Role | Data Flow | Closest Analog | Match Quality |
|---|---|---|---|---|
| `resources/views/livewire/equipes/index.blade.php` | component (Volt SFC) | CRUD + request-response | `resources/views/livewire/settings/profile.blade.php` | role-match (same Volt SFC pattern, different data) |
| `resources/views/livewire/equipes/create.blade.php` | component (Volt SFC) | CRUD + request-response | `resources/views/livewire/settings/profile.blade.php` | exact (validate inline + redirect) |
| `resources/views/livewire/equipes/edit.blade.php` | component (Volt SFC) | CRUD + request-response | `resources/views/livewire/settings/profile.blade.php` | exact (Rule::unique->ignore already in analog) |
| `app/Http/Requests/EquipeStoreRequest.php` | middleware/validation | request-response | `app/Http/Requests/PessoaRequest.php` | exact (same FormRequest structure + messages()) |
| `app/Http/Requests/EquipeUpdateRequest.php` | middleware/validation | request-response | `app/Http/Requests/PessoaRequest.php` | exact |
| `routes/web.php` | config/route | request-response | `routes/web.php` lines 183-185 | exact (Volt::route dentro de Route::middleware(['auth'])) |
| `tests/Feature/Equipes/EquipeCrudTest.php` | test | request-response | `tests/Feature/Settings/ProfileUpdateTest.php` + `tests/Feature/Autorizacao/EquipePolicyHttpTest.php` | exact (Volt::test() + actingAs + beforeEach com TipoMovimento::firstOrCreate) |
| `tests/Feature/Equipes/EquipeArquivamentoTest.php` | test | CRUD | `tests/Feature/Autorizacao/EquipePolicyHttpTest.php` | role-match (mesmo beforeEach, operacoes Eloquent) |

---

## Pattern Assignments

### `resources/views/livewire/equipes/index.blade.php` (Volt SFC, CRUD)

**Analog:** `resources/views/livewire/settings/profile.blade.php`

**Imports pattern** (profile.blade.php lines 1-8):
```php
<?php

use App\Models\Equipe;
use App\Enums\PapelEquipe;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
```

**Auth/Guard pattern** — authorize no mount() (extraido de RESEARCH.md Pattern 1, confirmado por vendor/livewire/livewire/src/Component.php):
```php
public function mount(): void
{
    $this->authorize('viewAny', Equipe::class);
    $this->carregarEquipes();
}
```

**Core CRUD pattern** — carregamento de colecao + actions (baseado em profile.blade.php + Equipe model scopes):
```php
public $equipes;

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

public function restaurar(int $idtEquipe): void
{
    $equipe = Equipe::withTrashed()->findOrFail($idtEquipe);
    $this->authorize('update', $equipe);
    $equipe->restore();
    $this->carregarEquipes();
    session()->flash('success', 'Equipe restaurada com sucesso.');
}
```

**Blade/Flux template pattern** (profile.blade.php lines 70-138):
```blade
<div>
    {{-- flux:table para listagem --}}
    {{-- flux:button variant="primary" para link /equipes/create (so se can('create', Equipe::class)) --}}
    @can('create', App\Models\Equipe::class)
        <flux:button href="{{ route('equipes.create') }}" wire:navigate>
            Nova Equipe
        </flux:button>
    @endcan

    {{-- Iterar $equipes com wire:click para arquivar/restaurar --}}
    <flux:button wire:click="arquivar({{ $equipe->idt_equipe }})" variant="danger">
        Arquivar
    </flux:button>
</div>
```

**Nota critica:** Para index mostrar equipes arquivadas para coord-geral (recomendacao RESEARCH.md Pergunta 2), usar `->withTrashed()` na query de `carregarEquipes()`. Para membro-equipe, omitir `withTrashed()` e adicionar `->ativas()`.

---

### `resources/views/livewire/equipes/create.blade.php` (Volt SFC, CRUD)

**Analog:** `resources/views/livewire/settings/profile.blade.php`

**Imports pattern** (profile.blade.php lines 1-8 — adaptar):
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
```

**Auth/Guard pattern** (authorize no mount, sem model — usa classe):
```php
public function mount(): void
{
    $this->authorize('create', Equipe::class);
}
```

**Core action pattern** (profile.blade.php lines 26-49, adaptado):
```php
public function salvar(): void
{
    $validated = $this->validate([
        'nom_equipe'    => ['required', 'string', 'max:60'],
        'des_slug'      => [
            'nullable',
            'string',
            'max:120',
            'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
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
```

**Redirect pattern** (profile.blade.php line 60 — usa redirectIntended; aqui usar redirect direto):
```php
$this->redirect(route('equipes.index'), navigate: true);
```

**Flux form template pattern** (profile.blade.php lines 80-131):
```blade
<form wire:submit="salvar" class="my-6 w-full space-y-6">
    <flux:input wire:model="nom_equipe" :label="__('Nome')" type="text" required />
    <flux:input wire:model="des_slug"   :label="__('Slug')" type="text" />
    <flux:textarea wire:model="des_descricao" :label="__('Descricao')" />

    <div class="flex items-center gap-4">
        <flux:button variant="primary" type="submit">
            {{ __('Salvar') }}
        </flux:button>
    </div>
</form>
```

---

### `resources/views/livewire/equipes/edit.blade.php` (Volt SFC, CRUD)

**Analog:** `resources/views/livewire/settings/profile.blade.php` (usa Rule::unique->ignore identicamente)

**Imports pattern**:
```php
<?php

use App\Models\Equipe;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component {
    public Equipe $equipe;

    public string $nom_equipe = '';
    public string $des_slug = '';
    public string $des_descricao = '';
    public bool $ind_ativa = true;
```

**Auth/Guard pattern** — authorize com model (profile.blade.php line 37, Rule::unique->ignore):
```php
public function mount(Equipe $equipe): void
{
    $this->authorize('update', $equipe);
    $this->equipe      = $equipe;
    $this->nom_equipe  = $equipe->nom_equipe;
    $this->des_slug    = $equipe->des_slug ?? '';
    $this->des_descricao = $equipe->des_descricao ?? '';
    $this->ind_ativa   = $equipe->ind_ativa;
}
```

**Unique com ignore pattern** (profile.blade.php line 37 usa Rule::unique->ignore; aqui com where adicional):
```php
public function salvar(): void
{
    $validated = $this->validate([
        'nom_equipe'    => ['required', 'string', 'max:60'],
        'des_slug'      => [
            'nullable',
            'string',
            'max:120',
            'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            Rule::unique('equipes', 'des_slug')
                ->where('idt_movimento', $this->equipe->idt_movimento)
                ->ignore($this->equipe->idt_equipe, 'idt_equipe'),
        ],
        'des_descricao' => ['nullable', 'string', 'max:500'],
        'ind_ativa'     => ['boolean'],
    ]);

    $this->equipe->update($validated);

    $this->redirect(route('equipes.index'), navigate: true);
}
```

**Toggle ind_ativa pattern** (nao tem analog direto — inline action simples):
```php
public function toggleAtivo(): void
{
    $this->authorize('update', $this->equipe);
    $this->equipe->update(['ind_ativa' => ! $this->equipe->ind_ativa]);
    $this->ind_ativa = $this->equipe->ind_ativa;
}
```

**flux:switch pattern** (confirmado em RESEARCH.md — componente Free disponivel):
```blade
<flux:switch wire:model.live="ind_ativa" wire:click="toggleAtivo" :label="__('Ativa')" />
```

---

### `app/Http/Requests/EquipeStoreRequest.php` (FormRequest, request-response)

**Analog:** `app/Http/Requests/PessoaRequest.php`

**Full structure pattern** (PessoaRequest.php lines 1-72):
```php
<?php

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
            'nom_equipe'    => ['required', 'string', 'max:60'],
            'des_slug'      => [
                'nullable',
                'string',
                'max:120',
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

**Nota:** O mesmo namespace e estrutura de `PessoaRequest.php` (sem declare strict_types — projeto nao usa em requests).

---

### `app/Http/Requests/EquipeUpdateRequest.php` (FormRequest, request-response)

**Analog:** `app/Http/Requests/PessoaRequest.php` + `resources/views/livewire/settings/profile.blade.php` (Rule::unique->ignore)

**Rules com ignore pattern** (profile.blade.php line 37 + RESEARCH.md Pattern 2):
```php
public function rules(): array
{
    $equipe = $this->route('equipe');  // Route model binding resolve Equipe

    return [
        'nom_equipe'    => ['required', 'string', 'max:60'],
        'des_slug'      => [
            'nullable',
            'string',
            'max:120',
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

**Nota critica:** O segundo argumento de `ignore()` deve ser `'idt_equipe'` (PK customizada), nao `'id'`. O profile.blade.php usa `Rule::unique(User::class)->ignore($user->id)` — aqui deve ser `->ignore($equipe->idt_equipe, 'idt_equipe')`.

---

### `routes/web.php` (modificacao — adicao de rotas equipes.*)

**Analog:** `routes/web.php` lines 183-185 (Volt::route dentro de auth group)

**Route pattern** (web.php lines 183-185):
```php
Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
Volt::route('settings/password', 'settings.password')->name('settings.password');
Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
```

**New routes to add** (dentro do `Route::middleware(['auth'])->group` existente, apos as settings routes):
```php
Volt::route('/equipes', 'equipes.index')
    ->name('equipes.index');

Volt::route('/equipes/create', 'equipes.create')
    ->name('equipes.create')
    ->can('create', \App\Models\Equipe::class);

Volt::route('/equipes/{equipe}/edit', 'equipes.edit')
    ->name('equipes.edit')
    ->can('update', 'equipe');
```

**Nota de posicionamento:** Adicionar ANTES de `require __DIR__ . '/auth.php'` e DENTRO do `Route::middleware(['auth'])->group`. O `->can()` na rota usa o Gate (que consulta `EquipePolicy`) como primeira camada; o `$this->authorize()` no `mount()` do SFC e a segunda camada (ambos necessarios per RESEARCH.md Pattern 6).

**Import necessario** (linha 23 ja tem `use Livewire\Volt\Volt;`):
```php
use App\Models\Equipe;  // adicionar no bloco de use existente
```

---

### `tests/Feature/Equipes/EquipeCrudTest.php` (test, CRUD)

**Analog:** `tests/Feature/Autorizacao/EquipePolicyHttpTest.php` (beforeEach com TipoMovimento::firstOrCreate + attach) + `tests/Feature/Settings/ProfileUpdateTest.php` (Volt::test() pattern)

**Imports pattern** (EquipePolicyHttpTest.php lines 1-9):
```php
<?php

use App\Enums\PapelEquipe;
use App\Models\Equipe;
use App\Models\TipoMovimento;
use App\Models\User;
use Livewire\Volt\Volt;
```

**beforeEach pattern** (EquipePolicyHttpTest.php lines 12-39):
```php
beforeEach(function () {
    $this->vem = TipoMovimento::firstOrCreate(
        ['des_sigla' => 'VEM'],
        ['nom_movimento' => 'Encontro de Adolescentes com Cristo', 'dat_inicio' => '2000-07-01']
    );
    $this->equipe = Equipe::factory()->create(['idt_movimento' => $this->vem->idt_movimento]);

    $this->coordGeral = User::factory()->create(['idt_movimento' => $this->vem->idt_movimento]);
    $this->coordGeral->equipes()->attach($this->equipe->idt_equipe, [
        'papel' => PapelEquipe::CoordGeral->value,
    ]);

    $this->membroEquipe = User::factory()->create();
    $this->membroEquipe->equipes()->attach($this->equipe->idt_equipe, [
        'papel' => PapelEquipe::MembroEquipe->value,
    ]);

    $this->userSemVinculo = User::factory()->create(['role' => 'user']);
});
```

**Volt::test() + assertHasNoErrors pattern** (ProfileUpdateTest.php lines 12-28):
```php
it('coord-geral pode criar equipe (EQUIPE-06)', function () {
    $this->actingAs($this->coordGeral);

    Volt::test('equipes.create')
        ->set('nom_equipe', 'Minha Equipe')
        ->set('des_descricao', 'Descricao da equipe')
        ->call('salvar')
        ->assertHasNoErrors()
        ->assertRedirect(route('equipes.index'));

    expect(Equipe::where('nom_equipe', 'Minha Equipe')->exists())->toBeTrue();
});
```

**withoutVite() para testes GET 200** (ConfiguracoesLegacyGuardTest.php lines 17-23):
```php
it('coord-geral acessa index de equipes com 200 (EQUIPE-05)', function () {
    $this->withoutVite()
        ->actingAs($this->coordGeral)
        ->get(route('equipes.index'))
        ->assertOk();
});
```

**403 sem withoutVite() para testes de bloqueio** (ConfiguracoesLegacyGuardTest.php lines 10-14):
```php
it('user sem coord-geral recebe 403 em equipes.create (EQUIPE-09)', function () {
    $this->actingAs($this->userSemVinculo)
        ->get(route('equipes.create'))
        ->assertStatus(403);
});
```

**assertHasErrors pattern** (ProfileUpdateTest.php lines 62-69):
```php
it('slug duplicado no mesmo movimento retorna erro de validacao (EQUIPE-04)', function () {
    $this->actingAs($this->coordGeral);

    Volt::test('equipes.create')
        ->set('nom_equipe', $this->equipe->nom_equipe)
        ->call('salvar')
        ->assertHasErrors(['des_slug']);
});
```

---

### `tests/Feature/Equipes/EquipeArquivamentoTest.php` (test, CRUD + SoftDeletes)

**Analog:** `tests/Feature/Autorizacao/EquipePolicyHttpTest.php` (mesmo beforeEach)

**Imports pattern** (mesmos do EquipeCrudTest.php + SoftDeletes):
```php
<?php

use App\Enums\PapelEquipe;
use App\Models\Equipe;
use App\Models\TipoMovimento;
use App\Models\User;
use Livewire\Volt\Volt;
```

**SoftDelete preservation pattern** (EQUIPE-10 — sem analog existente, baseado em RESEARCH.md Pattern 4):
```php
it('arquivar equipe aplica soft-delete e preserva pivot equipe_usuario (EQUIPE-10)', function () {
    $this->actingAs($this->coordGeral);

    // Verificar que pivot existe antes do arquivamento
    expect($this->equipe->usuarios()->count())->toBeGreaterThan(0);

    Volt::test('equipes.index')
        ->call('arquivar', $this->equipe->idt_equipe)
        ->assertHasNoErrors();

    // Equipe soft-deletada: nao aparece em query padrao
    expect(Equipe::find($this->equipe->idt_equipe))->toBeNull();

    // Equipe existe com withTrashed
    $equipeSoftDeleted = Equipe::withTrashed()->find($this->equipe->idt_equipe);
    expect($equipeSoftDeleted)->not->toBeNull();
    expect($equipeSoftDeleted->deleted_at)->not->toBeNull();

    // Pivot PRESERVADA (cascadeOnDelete nao dispara em soft-delete)
    expect(
        \Illuminate\Support\Facades\DB::table('equipe_usuario')
            ->where('idt_equipe', $this->equipe->idt_equipe)
            ->exists()
    )->toBeTrue();
});

it('equipe arquivada pode ser restaurada (EQUIPE-10)', function () {
    $this->actingAs($this->coordGeral);

    $this->equipe->delete();

    Volt::test('equipes.index')
        ->call('restaurar', $this->equipe->idt_equipe)
        ->assertHasNoErrors();

    expect(Equipe::find($this->equipe->idt_equipe))->not->toBeNull();
});
```

---

## Shared Patterns

### Authorize no Mount (todas as SFCs)

**Source:** `vendor/livewire/livewire/src/Component.php` (usa `AuthorizesRequests`) — padrao confirmado em RESEARCH.md
**Apply to:** `equipes/index.blade.php`, `equipes/create.blade.php`, `equipes/edit.blade.php`

```php
// Sem model (create, index): passa classe
$this->authorize('create', Equipe::class);
$this->authorize('viewAny', Equipe::class);

// Com model (edit, arquivar dentro de index): passa instancia
$this->authorize('update', $equipe);
```

### Redirect apos Acao

**Source:** `resources/views/livewire/settings/profile.blade.php` line 60
**Apply to:** `equipes/create.blade.php` e `equipes/edit.blade.php` (apos salvar)

```php
$this->redirect(route('equipes.index'), navigate: true);
```

### Rule::unique com escopo por movimento

**Source:** `resources/views/livewire/settings/profile.blade.php` line 37 (usa ignore) + RESEARCH.md Pattern 2
**Apply to:** `EquipeStoreRequest`, `EquipeUpdateRequest`, `equipes/create.blade.php`, `equipes/edit.blade.php`

```php
// Store — sem ignore:
Rule::unique('equipes', 'des_slug')
    ->where('idt_movimento', Auth::user()->idt_movimento)

// Update — com ignore (PK customizada idt_equipe):
Rule::unique('equipes', 'des_slug')
    ->where('idt_movimento', $this->equipe->idt_movimento)
    ->ignore($this->equipe->idt_equipe, 'idt_equipe')
```

### FormRequest Structure

**Source:** `app/Http/Requests/PessoaRequest.php` lines 1-72
**Apply to:** `EquipeStoreRequest`, `EquipeUpdateRequest`

```php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EquipeXxxRequest extends FormRequest
{
    public function authorize(): bool { ... }
    public function rules(): array { ... }
    public function messages(): array { ... }
}
```

### beforeEach de Testes com TipoMovimento VEM

**Source:** `tests/Feature/Autorizacao/EquipePolicyHttpTest.php` lines 12-39
**Apply to:** Todos os 3 arquivos de teste Feature de equipes

```php
beforeEach(function () {
    $this->vem = TipoMovimento::firstOrCreate(
        ['des_sigla' => 'VEM'],
        ['nom_movimento' => 'Encontro de Adolescentes com Cristo', 'dat_inicio' => '2000-07-01']
    );
    $this->equipe = Equipe::factory()->create(['idt_movimento' => $this->vem->idt_movimento]);

    $this->coordGeral = User::factory()->create(['idt_movimento' => $this->vem->idt_movimento]);
    $this->coordGeral->equipes()->attach($this->equipe->idt_equipe, [
        'papel' => PapelEquipe::CoordGeral->value,
    ]);
});
```

**Nota critica:** O `coordGeral` deve ter `idt_movimento` setado para que `Equipe::paraMovimento(Auth::user()->idt_movimento)` retorne dados corretos nos testes do index.

### withoutVite() para Testes GET 200

**Source:** `tests/Feature/Autorizacao/ConfiguracoesLegacyGuardTest.php` lines 17-23
**Apply to:** Qualquer teste que faz `->get(route('equipes.*'))->assertOk()` (nao necessario em testes 403)

```php
$this->withoutVite()
    ->actingAs($this->coordGeral)
    ->get(route('equipes.index'))
    ->assertOk();
```

### Volt::test() Pattern

**Source:** `tests/Feature/Settings/ProfileUpdateTest.php` lines 12-28
**Apply to:** Todos os testes que interagem com actions Volt (salvar, arquivar, restaurar, toggleAtivo)

```php
Volt::test('equipes.create')        // nome do SFC (path relativo a resources/views/livewire/)
    ->set('nom_equipe', 'Valor')    // setar propriedade publica
    ->call('salvar')                // chamar action
    ->assertHasNoErrors()           // sem erros de validacao
    ->assertRedirect(route('equipes.index'));

// Para SFCs com mount que recebem model:
Volt::test('equipes.edit', ['equipe' => $this->equipe])
    ->set('nom_equipe', 'Novo Nome')
    ->call('salvar')
    ->assertHasNoErrors();
```

### RefreshDatabase Global

**Source:** `tests/Pest.php` lines 23-25
**Apply to:** Nenhum arquivo de teste precisa declarar RefreshDatabase manualmente — ja aplicado globalmente para `Feature` e `Unit`.

```php
// Nao adicionar use RefreshDatabase; nos arquivos de teste — ja e global:
// pest()->extend(TestCase::class)->use(RefreshDatabase::class)->in('Feature', 'Unit');
```

---

## No Analog Found

Nenhum arquivo sem analog identificado. Todos os 9 arquivos tem correspondencia clara no codebase existente.

---

## Critical Notes for Planner

1. **Volt SFC naming:** O nome passado para `Volt::test()` e `Volt::route()` usa ponto como separador de pasta: `'equipes.index'` mapeia para `resources/views/livewire/equipes/index.blade.php`.

2. **PK customizada em ignore():** `Rule::unique()->ignore($equipe->idt_equipe, 'idt_equipe')` — o segundo argumento e obrigatorio porque o Laravel usa `id` por padrao. Sem ele, o ignore nao funciona.

3. **Constraint composta (idt_movimento, des_slug):** O `->where('idt_movimento', ...)` e obrigatorio em AMBOS store e update. Sem ele, a validacao nao espelha a constraint do banco.

4. **coordGeral com idt_movimento:** Nos testes, `User::factory()->create(['idt_movimento' => $this->vem->idt_movimento])` garante que o scope `paraMovimento` retorna dados corretos.

5. **Flux Free confirmado:** `flux:input`, `flux:button`, `flux:switch`, `flux:table`, `flux:textarea` estao todos disponiveis no `livewire/flux ^2.1.1` Free. Nao usar componentes Pro.

6. **Mutator de slug:** `Equipe::setNomEquipeAttribute()` auto-preenche `des_slug` apenas quando ele esta vazio. No create, enviar `des_slug = ''` e o mutator preenche. No edit, se o usuario editou o slug manualmente, o mutator nao sobrescreve.

---

## Metadata

**Analog search scope:** `resources/views/livewire/`, `app/Http/Requests/`, `tests/Feature/`, `routes/web.php`, `app/Models/Equipe.php`
**Files scanned:** 10 (profile.blade.php, password.blade.php, PessoaRequest.php, EquipePolicyHttpTest.php, ProfileUpdateTest.php, ConfiguracoesLegacyGuardTest.php, EquipeVEMSeederTest.php, Pest.php, web.php, Equipe.php)
**Pattern extraction date:** 2026-04-23
