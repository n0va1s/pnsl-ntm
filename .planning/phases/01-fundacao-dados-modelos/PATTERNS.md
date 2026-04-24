# Fase 1: Fundação de Dados e Modelos de Equipe — Pattern Map

**Mapped:** 2026-04-21
**Phase directory:** `.planning/phases/01-fundacao-dados-modelos/`
**Files to create:** 9
**Analogs found:** 8 / 9 (1 sem análogo — `app/Enums/PapelEquipe.php`)

Consumido por: `gsd-planner` (próximo passo do `/gsd-plan-phase`).

---

## Convenções confirmadas no codebase (grep evidência)

Antes das atribuições por arquivo, estes fatos foram verificados empiricamente e devem orientar TODAS as decisões:

| Convenção | Status | Evidência |
|-----------|--------|-----------|
| `declare(strict_types=1);` | **NÃO usado** em `app/` | grep retornou zero matches |
| Diretório `app/Enums/` | **NÃO existe** | `ls app/Enums` → "No such file or directory" |
| Enums PHP 8.1+ no projeto | **Zero** | grep `enum\s+\w+.*string\|BackedEnum` → nenhum match |
| Audit cols `usr_inclusao`/`dat_inclusao` | **NÃO existem** no código | só aparecem em `.planning/REQUIREMENTS.md` e `.planning/ROADMAP.md` |
| Audit cols existentes | `usu_inclusao`/`usu_alteracao` (só em `ficha`) | `app/Models/Ficha.php` + migration `2026_04_18_133105_add_usuario_ficha_table.php` |
| PK convention | `idt_X` (ex.: `idt_equipe`, `idt_movimento`, `idt_pessoa`) | todas as migrations em `database/migrations/2025_06_06_152302_create.php` |
| Tabela `users` PK | `id` (padrão Laravel) | `app/Models/User.php` + config default |
| SoftDeletes | convenção presente em quase todos os domain models | `tipo_equipe`, `pessoa`, `ficha`, `evento` |
| Factory pattern idempotente | `defaults()` + `seedDefaults()` com `firstOrCreate` | `database/factories/TipoEquipeFactory.php` |
| Seeder idempotente | guard `if (Model::count() > 0) return;` | `database/seeders/DominiosSeeder.php` |
| Audit booted hook | `static::creating` + `static::updating` + `auth()->check()` | `app/Models/Ficha.php` |
| Helper de teste existente | `createMovimentos()` já cria 11 equipes VEM em `tipo_equipe` | `tests/Pest.php` (COLISÃO DE ESCOPO — flag no ROADMAP) |
| Teste smoke CRUD | trait `CrudBasic` + `$this->verificaOperacoes(...)` | `tests/Unit/CrudBasic.php` + `tests/Feature/TipoEquipeTest.php` |

**Regra mestra para Fase 1:** seguir convenção do projeto, não convenção global. Sem `declare(strict_types=1);`. Tabela em **snake_case singular**. PK `idt_X`. FK via `foreignId()->constrained('tabela', 'idt_X')`.

---

## File Classification

| New File | Role | Data Flow | Closest Analog | Match Quality |
|----------|------|-----------|----------------|---------------|
| `database/migrations/YYYY_MM_DD_HHMMSS_create_equipes_table.php` | migration | schema-DDL | `database/migrations/2025_06_06_152302_create.php` (bloco `tipo_equipe`) | **exact** |
| `database/migrations/YYYY_MM_DD_HHMMSS_create_equipe_usuario_table.php` | migration (pivot) | schema-DDL | `database/migrations/2025_06_06_152302_create.php` (bloco `voluntario`) + `2026_04_18_133105_add_usuario_ficha_table.php` | **role-match** (pivot + audit) |
| `app/Enums/PapelEquipe.php` | enum (value-object) | value-object | **NENHUM** — primeiro enum do projeto | **no analog** |
| `app/Models/Equipe.php` | model (domain) | CRUD + scopes + relations | `app/Models/TipoEquipe.php` | **exact** |
| `app/Models/EquipeUsuario.php` | model (pivot) | CRUD + audit booted | `app/Models/Voluntario.php` (estrutura pivot) + `app/Models/Ficha.php` (booted audit) | **role-match composto** |
| `database/seeders/EquipeVEMSeeder.php` | seeder | batch-insert idempotente | `database/seeders/DominiosSeeder.php` | **exact** |
| `database/factories/EquipeFactory.php` | factory | test-data + `seedDefaults` | `database/factories/TipoEquipeFactory.php` | **exact** |
| `database/factories/EquipeUsuarioFactory.php` | factory (pivot) | test-data | `database/factories/TrabalhadorFactory.php` | **role-match** |
| `tests/Feature/EquipeTest.php` + `EquipeUsuarioTest.php` | test (Pest) | feature | `tests/Feature/TipoEquipeTest.php` + `tests/Unit/CrudBasic.php` + `tests/Feature/TipoMovimentoTest.php` | **exact** (smoke) + **role-match** (rich) |

---

## Pattern Assignments

### 1. `database/migrations/..._create_equipes_table.php` (migration, schema-DDL)

**Analog:** `database/migrations/2025_06_06_152302_create.php` — bloco `tipo_equipe` (linhas 50–58).

**Why it matches:** Mesma forma estrutural: tabela de domínio com `idt_X`, FK para `tipo_movimento`, `timestamps` + `softDeletes`. A nova `equipes` é basicamente um `tipo_equipe` v2 com coluna extra `ind_ativa` + `des_slug`.

**What to copy — estrutura base (copiar verbatim):**
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipes', function (Blueprint $table) {
            $table->id('idt_equipe');
            $table->foreignId('idt_movimento')
                ->constrained('tipo_movimento', 'idt_movimento');
            // ... campos específicos ...
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipes');
    }
};
```

**What to adapt:**
- Nome da tabela: `equipes` (snake_case plural — DEVIATION do padrão singular do projeto; REQUIREMENTS exige `equipes`, documentar no ROADMAP).
- PK: `$table->id('idt_equipe')`.
- Campos específicos (ler REQUIREMENTS EQUIPE-01): `nom_equipe`, `des_slug` (unique por movimento), `ind_ativa` (default `true`), `des_descricao` (nullable).
- Índice composto: `$table->unique(['idt_movimento', 'des_slug'], 'equipes_movimento_slug_unique')` (mesmo padrão de `unique_presenca_por_participante` no `create.php` linhas 282).
- `down()` deve dropar apenas `equipes` (pivot em migration separada).

**Risco:** nome `equipes` (plural) quebra convenção do projeto (`tipo_equipe` singular). Manter — é o contrato do REQUIREMENTS.

---

### 2. `database/migrations/..._create_equipe_usuario_table.php` (migration pivot + audit)

**Analog composto:**
- Estrutura pivot: `database/migrations/2025_06_06_152302_create.php` bloco `voluntario` (se existir — se não, adaptar o bloco `ficha_analise` linhas 300–309 que tem 2 FKs + unique composto).
- Colunas de auditoria FK: `database/migrations/2026_04_18_133105_add_usuario_ficha_table.php` (linhas 12–16).

**Why it matches:** É uma tabela pivot com duas FKs + metadata (`papel`, audit cols). Nenhum pivot com softDeletes existe ainda, mas `ficha` tem a combinação "soft-deleted + FK audit" mais próxima.

**What to copy — estrutura pivot com audit cols:**
```php
Schema::create('equipe_usuario', function (Blueprint $table) {
    $table->id('idt_equipe_usuario');
    $table->foreignId('idt_equipe')
        ->constrained('equipes', 'idt_equipe')
        ->cascadeOnDelete();
    $table->foreignId('user_id')
        ->constrained('users');
    $table->string('papel', 30); // casteado no model para PapelEquipe enum
    // Audit FK pattern (clonar de add_usuario_ficha_table.php)
    $table->foreignId('usr_inclusao')->nullable()->constrained('users');
    $table->foreignId('usr_alteracao')->nullable()->constrained('users');
    $table->timestamp('dat_inclusao')->nullable();
    $table->timestamp('dat_alteracao')->nullable();
    $table->softDeletes();
    $table->unique(['idt_equipe', 'user_id'], 'equipe_usuario_unique');
});
```

**What to adapt:**
- **Deviation de naming:** REQUIREMENTS pede `usr_*` + `dat_*` (separados). `Ficha` usa `usu_*` (um só prefixo, sem `dat_*`). Esta fase **introduz o novo padrão `usr_*`+`dat_*`** — documentar no ROADMAP como precedent para futuras tabelas auditadas.
- Sem `$table->timestamps()` nativos — os `dat_inclusao`/`dat_alteracao` substituem `created_at`/`updated_at` (REQUIREMENTS). Confirmar na REQ antes de finalizar.
- `user_id` (padrão Laravel) em vez de `idt_usuario` — porque `users.id` é o PK (ver `Pessoa.php::idt_usuario` referencia `users.id`, mas REQUIREMENTS chama `user_id` explicitamente).
- Unique constraint + softDeletes no **SQLite**: o índice unique engatilha em rows soft-deleted também. Solução padrão: usar partial index só no MySQL **ou** confiar em validação aplicação-side. Ler ROADMAP — risco já listado.
- `cascadeOnDelete` no `idt_equipe` (deletar equipe dropa vínculos); `restrict` implícito no `user_id` (default) — NUNCA dropa usuários.

**Copy verbatim do analog de audit FK** (`2026_04_18_133105_add_usuario_ficha_table.php` linhas 12–16):
```php
$table->foreignId('usu_inclusao')->nullable()->constrained('users');
$table->foreignId('usu_alteracao')->nullable()->constrained('users');
```
→ adaptar nomes para `usr_inclusao`/`usr_alteracao`.

---

### 3. `app/Enums/PapelEquipe.php` (enum — **no analog**)

**NENHUM enum PHP 8.1+ existe no projeto.** Grep `enum\s+\w+.*string|BackedEnum` em `app/` retornou zero. O padrão atual de "enum" no projeto é **constante de classe** (ex.: `User::ROLE_USER`, `User::ROLE_ADMIN`, `User::ROLE_COORDENADOR`; `TipoMovimento::ECC`, `TipoMovimento::VEM`, `TipoMovimento::SegueMe`).

**Proposta de novo pattern (primeiro enum do projeto):**

**PHP 8.1+ backed string enum** com método `label()` para render pt_BR e método `labels()` estático para selects Livewire/Flux:

```php
<?php

namespace App\Enums;

enum PapelEquipe: string
{
    case CoordGeral    = 'coord_geral';
    case CoordEquipeH  = 'coord_equipe_h';
    case CoordEquipeM  = 'coord_equipe_m';
    case MembroEquipe  = 'membro_equipe';

    public function label(): string
    {
        return match ($this) {
            self::CoordGeral    => 'Coordenador Geral',
            self::CoordEquipeH  => 'Coordenador de Equipe (Homem)',
            self::CoordEquipeM  => 'Coordenador de Equipe (Mulher)',
            self::MembroEquipe  => 'Membro de Equipe',
        };
    }

    /** @return array<string,string> mapa value => label para <select> */
    public static function labels(): array
    {
        return array_reduce(
            self::cases(),
            fn ($acc, self $case) => $acc + [$case->value => $case->label()],
            []
        );
    }

    public function isCoordenador(): bool
    {
        return $this !== self::MembroEquipe;
    }
}
```

**Why this shape:**
- `: string` backed — permite `casts` direto no Eloquent (`'papel' => PapelEquipe::class`) e serialização natural em JSON/DB.
- `label()` + `labels()`: Livewire/Flux precisam do array `value => label` para `<flux:select>`. REQUIREMENTS menciona UI posterior mas a fundação já prepara.
- `isCoordenador()`: conveniência para scopes `coordenadores()` / `membros()` no model `Equipe` (ver #4).
- Case names em **PascalCase** (convenção PSR / PHP standard), valores em **snake_case** (convenção da tabela).

**What to adapt:** possivelmente nada na Fase 1 além dos 4 casos. Futuras fases poderão adicionar papéis (ex.: `SuportePastoral`).

**Referência para planner:** sem análogo, mas `app/Models/TipoMovimento.php` (linhas ~6–10 com `const ECC = 1;`) mostra o padrão ANTIGO de constantes que este enum **substitui conceitualmente** para papéis. Não confundir — constantes lá são **tipos** persistidos como `int`, aqui é enum de **string**.

---

### 4. `app/Models/Equipe.php` (model domain, CRUD + scopes + relations)

**Analog:** `app/Models/TipoEquipe.php` (arquivo completo, 39 linhas).

**Why it matches:** É literalmente a versão v2. Ambos: domain model com FK para `tipo_movimento`, SoftDeletes, HasFactory, PK `idt_equipe`. A evolução adiciona scopes e relação many-to-many para `User`.

**What to copy — shell completo (verbatim de `TipoEquipe.php`):**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipe extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'equipes';
    protected $primaryKey = 'idt_equipe';
    public $timestamps = true;

    protected $fillable = [
        'idt_movimento',
        'nom_equipe',
        'des_slug',
        'des_descricao',
        'ind_ativa',
    ];

    protected $casts = [
        'ind_ativa' => 'boolean',
    ];

    public function movimento()
    {
        return $this->belongsTo(TipoMovimento::class, 'idt_movimento');
    }
}
```

**What to adapt / ADICIONAR (específico de Fase 1):**

**Scopes** (analog: `app/Models/Trabalhador.php::scopeEvento`, linhas ~55–66):
```php
public function scopeAtivas(Builder $query): Builder
{
    return $query->where('ind_ativa', true);
}

public function scopeParaMovimento(Builder $query, ?int $idtMovimento): Builder
{
    return $idtMovimento
        ? $query->where('idt_movimento', $idtMovimento)
        : $query;
}
```

**Relations belongsToMany com pivot** (padrão **novo no projeto** — pivot com modelo próprio):
```php
public function usuarios()
{
    return $this->belongsToMany(
        User::class,
        'equipe_usuario',
        'idt_equipe',
        'user_id'
    )
    ->using(EquipeUsuario::class)
    ->withPivot(['papel', 'deleted_at', 'usr_inclusao', 'dat_inclusao'])
    ->withTimestamps(); // REAVALIAR: se a tabela usa dat_inclusao em vez de created_at, remover withTimestamps e gerenciar via booted() do pivot
}

public function coordenadores()
{
    return $this->usuarios()->wherePivotIn('papel', [
        PapelEquipe::CoordGeral->value,
        PapelEquipe::CoordEquipeH->value,
        PapelEquipe::CoordEquipeM->value,
    ]);
}

public function membros()
{
    return $this->usuarios()->wherePivot('papel', PapelEquipe::MembroEquipe->value);
}
```

**Mutator slug** (risco documentado no ROADMAP — acentos):
```php
public function setNomEquipeAttribute(string $value): void
{
    $this->attributes['nom_equipe'] = $value;
    if (empty($this->attributes['des_slug'])) {
        $this->attributes['des_slug'] = Str::slug($value); // Str::slug remove acentos OK
    }
}
```

**Imports adicionais esperados:**
```php
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use App\Enums\PapelEquipe;
```

---

### 5. `app/Models/EquipeUsuario.php` (model pivot, CRUD + audit booted)

**Analog composto:**
- **Estrutura pivot-model**: extends `Pivot` class (não `Model`) — nenhum exemplo exato no projeto; `app/Models/Voluntario.php` (84 linhas) é o mais próximo em termos de "modelo com 2 FKs".
- **Booted audit hook**: `app/Models/Ficha.php` linhas ~60–85 (hook `creating`/`updating` setando `usu_inclusao`/`usu_alteracao`).

**Why it matches:** O projeto não tem pivot com `using(...)` ainda — este será o primeiro. O **comportamento** (audit automático) é o que importa e tem análogo exato em `Ficha`.

**What to copy — booted audit (copiar verbatim de `Ficha.php`, linhas ~60–85):**
```php
protected static function booted()
{
    static::creating(function ($model) {
        if (auth()->check()) {
            $model->usu_inclusao = auth()->id();
            $model->usu_alteracao = auth()->id();
        }
    });

    static::updating(function ($model) {
        if (auth()->check()) {
            $model->usu_alteracao = auth()->id();
        }
    });
}
```

**What to adapt para `EquipeUsuario`:**

1. **Extends `Model`** (NÃO `Pivot`) — wirado como pivot via `->using()`:
```php
use Illuminate\Database\Eloquent\Model;

class EquipeUsuario extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'equipe_usuario';
    protected $primaryKey = 'idt_equipe_usuario';
    public $incrementing = true;
    public $timestamps = false; // usamos dat_inclusao/dat_alteracao manuais

    protected $fillable = ['idt_equipe', 'user_id', 'papel'];

    protected $casts = [
        'papel' => PapelEquipe::class,
        'dat_inclusao' => 'datetime',
        'dat_alteracao' => 'datetime',
    ];

    // ...
}
```

> **⚠️ Decisão de research (BLOCKING resolvida):** `Illuminate\Database\Eloquent\Relations\Pivot` é **incompatível** com `SoftDeletes` em Laravel 12 (faltam hooks do trait e auto-increment/PK comportam-se diferente). Solução: estender `Model` + wirar como pivot via `->using(EquipeUsuario::class)` na relação `belongsToMany`. Ver `Equipe::usuarios()` mais abaixo. Laravel 12 docs confirmam que qualquer `Model` pode servir como pivot class via `using()`.

2. **Audit hook adaptado para `usr_*` + `dat_*`** (extende o padrão do `Ficha` com o timestamp manual):
```php
protected static function booted()
{
    static::creating(function ($model) {
        $now = now();
        if (auth()->check()) {
            $model->usr_inclusao  ??= auth()->id();
            $model->usr_alteracao ??= auth()->id();
        }
        $model->dat_inclusao  ??= $now;
        $model->dat_alteracao ??= $now;
    });

    static::updating(function ($model) {
        if (auth()->check()) {
            $model->usr_alteracao = auth()->id();
        }
        $model->dat_alteracao = now();
    });
}
```

3. **Relations do pivot** (padrão `Pivot` do Laravel):
```php
public function equipe()
{
    return $this->belongsTo(Equipe::class, 'idt_equipe');
}

public function usuario()
{
    return $this->belongsTo(User::class, 'user_id');
}

public function papelEnum(): PapelEquipe
{
    return PapelEquipe::from($this->papel);
}
```

**Deviation explícito:** padrão `usu_*` do `Ficha` → `usr_*`+`dat_*` aqui. **Este é o primeiro uso do novo padrão** — gsd-planner deve incluir nota no PLAN para flaggear em code-review.

---

### 6. `database/seeders/EquipeVEMSeeder.php` (seeder, batch idempotent)

**Analog:** `database/seeders/DominiosSeeder.php` (26 linhas).

**Why it matches:** Mesmo papel (seed de domínio com guard idempotente) + mesma forma (chama `Factory::seedDefaults()`).

**What to copy — shell completo (verbatim de `DominiosSeeder.php`):**
```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Factories\EquipeFactory;
use App\Models\Equipe;

class EquipeVEMSeeder extends Seeder
{
    public function run(): void
    {
        // Guard idempotente — mesmo padrão de DominiosSeeder
        if (Equipe::count() > 0) {
            return;
        }

        EquipeFactory::seedDefaults();
    }
}
```

**What to adapt:**
- Nome da classe: `EquipeVEMSeeder`.
- Guard: `Equipe::count() > 0` (igual ao `TipoMovimento::count() > 0` do original, linha ~14 do `DominiosSeeder`).
- Delegação para `EquipeFactory::seedDefaults()` — a lista das 11 equipes mora lá (padrão estabelecido por `TipoEquipeFactory`).

**Registro em `DatabaseSeeder`:** adicionar `$this->call(EquipeVEMSeeder::class);` **APÓS** `DominiosSeeder::class` (porque precisa de `tipo_movimento` já populado).

**As 11 equipes VEM** (REQUIREMENTS EQUIPE-03) — ordem alfabética sugerida:
1. Alimentação
2. Bandinha
3. Emaús
4. Limpeza
5. Oração
6. Recepção
7. Reportagem
8. Sala
9. Secretaria
10. Troca de Ideias
11. Vendinha

---

### 7. `database/factories/EquipeFactory.php` (factory + seedDefaults idempotente)

**Analog:** `database/factories/TipoEquipeFactory.php` (64 linhas).

**Why it matches:** Mesmo shape EXATO — `definition()` para testes + `defaults()` para seed + `seedDefaults()` estático com `firstOrCreate`.

**What to copy — pattern completo (verbatim de `TipoEquipeFactory.php`):**
```php
<?php

namespace Database\Factories;

use App\Models\Equipe;
use App\Models\TipoMovimento;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class EquipeFactory extends Factory
{
    protected $model = Equipe::class;

    public function definition(): array
    {
        $nome = $this->faker->unique()->words(2, true);
        return [
            'idt_movimento' => TipoMovimento::factory(),
            'nom_equipe'    => ucfirst($nome),
            'des_slug'      => Str::slug($nome),
            'ind_ativa'     => true,
            'des_descricao' => $this->faker->optional()->sentence(),
        ];
    }

    /** @return array<int,array<string,mixed>> As 11 equipes VEM v1.1 */
    public function defaults(): array
    {
        $movimentos = TipoMovimento::all()->keyBy('des_sigla');
        $idtVEM = $movimentos['VEM']->idt_movimento;

        return collect([
            'Alimentação',
            'Bandinha',
            'Emaús',
            'Limpeza',
            'Oração',
            'Recepção',
            'Reportagem',
            'Sala',
            'Secretaria',
            'Troca de Ideias',
            'Vendinha',
        ])->map(fn (string $nome) => [
            'idt_movimento' => $idtVEM,
            'nom_equipe'    => $nome,
            'des_slug'      => Str::slug($nome),
            'ind_ativa'     => true,
        ])->all();
    }

    public static function seedDefaults(): void
    {
        foreach ((new self)->defaults() as $data) {
            Equipe::firstOrCreate(
                ['idt_movimento' => $data['idt_movimento'], 'des_slug' => $data['des_slug']],
                $data
            );
        }
    }
}
```

**What to adapt:**
- `$this->faker` (estilo projeto — ver `TrabalhadorFactory.php` linhas 12–30 e `TipoEquipeFactory.php`) — **NÃO use `fake()` helper**, mantenha consistência com o projeto.
- `firstOrCreate` usa chave composta `(idt_movimento, des_slug)` para casar com o unique index da migration.
- `Str::slug('Alimentação')` = `alimentacao` — teste este caso explicitamente nos testes (risco ROADMAP: mutator com acentos).

---

### 8. `database/factories/EquipeUsuarioFactory.php` (factory pivot)

**Analog:** `database/factories/TrabalhadorFactory.php` (31 linhas) — factory simples com 2 FKs.

**Why it matches:** Mesmo shape (2 FKs via outras factories + campo string). Não há pivot-factory no projeto; esta é adaptação simples.

**What to copy — estilo factory simples (verbatim padrão de `TrabalhadorFactory.php`):**
```php
<?php

namespace Database\Factories;

use App\Enums\PapelEquipe;
use App\Models\Equipe;
use App\Models\EquipeUsuario;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EquipeUsuarioFactory extends Factory
{
    protected $model = EquipeUsuario::class;

    public function definition(): array
    {
        return [
            'idt_equipe' => Equipe::factory(),
            'user_id'    => User::factory(),
            'papel'      => $this->faker->randomElement(PapelEquipe::cases())->value,
        ];
    }

    public function comoCoordenadorGeral(): static
    {
        return $this->state(fn () => ['papel' => PapelEquipe::CoordGeral->value]);
    }

    public function comoMembro(): static
    {
        return $this->state(fn () => ['papel' => PapelEquipe::MembroEquipe->value]);
    }
}
```

**What to adapt:**
- Campo `papel` usa `->value` porque a coluna é VARCHAR — o cast no model converte para enum.
- State methods `comoCoordenadorGeral()` / `comoMembro()` — analog: `UserFactory::unverified()` (linha ~36 de `UserFactory.php`, padrão state simples).
- Sem `seedDefaults()` — factory pivot é SÓ para testes, não para seed.

---

### 9. `tests/Feature/EquipeTest.php` + `EquipeUsuarioTest.php` (Pest feature tests)

**Analog composto:**
- **Smoke CRUD**: `tests/Feature/TipoEquipeTest.php` (23 linhas) + trait `tests/Unit/CrudBasic.php` (26 linhas).
- **Feature rica (relations + HTTP)**: `tests/Feature/TipoMovimentoTest.php` (79 linhas), `tests/Feature/ParticipanteTest.php` (164 linhas).
- **Bootstrap global**: `tests/Pest.php` (118 linhas) — contém `createMovimentos()` que já cria 11 VEM equipes em `tipo_equipe` (**ATENÇÃO: colisão de escopo**).

**Why it matches:** Estilo Pest do projeto é: `uses(RefreshDatabase::class, Trait::class)` no topo + `describe('Escopo::operacao', fn() => ...)` + `test('descrição em pt_BR', fn() => ...)`.

**What to copy — smoke test (verbatim de `tests/Feature/TipoEquipeTest.php`):**
```php
<?php

use App\Models\Equipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Unit\CrudBasic;

uses(RefreshDatabase::class, CrudBasic::class);

describe('Equipe::CRUD', function () {
    test('equipe respeita contrato basico', function () {
        createMovimentos(); // helper existente em tests/Pest.php
        $this->verificaOperacoes(Equipe::class, ['nom_equipe', 'des_slug', 'idt_movimento']);
    });
});
```

**What to copy — feature test rico** (analog: `TipoMovimentoTest.php`):
```php
describe('Equipe::relations', function () {
    beforeEach(function () {
        createMovimentos();
        $this->equipe = Equipe::factory()->create();
        $this->user = User::factory()->create();
    });

    test('atachar usuario via pivot cria registro com papel castado', function () {
        $this->equipe->usuarios()->attach($this->user->id, [
            'papel' => PapelEquipe::MembroEquipe->value,
        ]);

        $pivot = EquipeUsuario::first();
        expect($pivot->papel)->toBeInstanceOf(PapelEquipe::class);
        expect($pivot->papel)->toEqual(PapelEquipe::MembroEquipe);
    });

    test('scope coordenadores retorna apenas coordenadores', function () {
        $coord = User::factory()->create();
        $membro = User::factory()->create();

        $this->equipe->usuarios()->attach($coord->id, ['papel' => PapelEquipe::CoordGeral->value]);
        $this->equipe->usuarios()->attach($membro->id, ['papel' => PapelEquipe::MembroEquipe->value]);

        expect($this->equipe->coordenadores()->count())->toBe(1);
        expect($this->equipe->membros()->count())->toBe(1);
    });

    test('unique constraint bloqueia duplicata de user-equipe', function () {
        $this->equipe->usuarios()->attach($this->user->id, ['papel' => PapelEquipe::MembroEquipe->value]);
        expect(fn () => $this->equipe->usuarios()->attach($this->user->id, ['papel' => PapelEquipe::CoordGeral->value]))
            ->toThrow(Illuminate\Database\QueryException::class);
    });
});
```

**What to copy — seed test** (analog: padrão `assertDatabaseCount`):
```php
describe('EquipeVEMSeeder', function () {
    test('seed popula exatamente 11 equipes VEM e é idempotente', function () {
        createMovimentos();

        $this->seed(EquipeVEMSeeder::class);
        $this->assertDatabaseCount('equipes', 11);

        $this->seed(EquipeVEMSeeder::class); // segunda chamada — deve no-op
        $this->assertDatabaseCount('equipes', 11);
    });

    test('todas as equipes seed estão vinculadas ao movimento VEM', function () {
        createMovimentos();
        $this->seed(EquipeVEMSeeder::class);

        $vem = TipoMovimento::where('des_sigla', 'VEM')->first();
        expect(Equipe::where('idt_movimento', $vem->idt_movimento)->count())->toBe(11);
    });
});
```

**What to adapt — RISCO CRÍTICO:** `createMovimentos()` em `tests/Pest.php` **já cria 11 equipes em `tipo_equipe`** (tabela legada). A nova tabela `equipes` é **outra**. Os testes NÃO devem renomear `createMovimentos()` — apenas NÃO ASSUMIR que já popula `equipes`. Se necessário, criar novo helper `createEquipesVEM()` em `tests/Pest.php` OU chamar `$this->seed(EquipeVEMSeeder::class)` explicitamente.

**Migration up/down test** (REQUIREMENTS TEST-05):
```php
test('migration up e down rodam sem erros', function () {
    $this->artisan('migrate:fresh');
    expect(Schema::hasTable('equipes'))->toBeTrue();
    expect(Schema::hasTable('equipe_usuario'))->toBeTrue();

    $this->artisan('migrate:rollback', ['--step' => 2]);
    expect(Schema::hasTable('equipes'))->toBeFalse();
    expect(Schema::hasTable('equipe_usuario'))->toBeFalse();
});
```

---

## Shared Patterns (cross-cutting)

### SP-1. Audit booted() hook
**Source:** `app/Models/Ficha.php` linhas ~60–85.
**Apply to:** `app/Models/EquipeUsuario.php`.
**Adapt:** renomear `usu_*` → `usr_*` **e** adicionar set manual de `dat_inclusao`/`dat_alteracao` (não existe em `Ficha`).

```php
// Template (adapt prefix)
protected static function booted()
{
    static::creating(function ($m) {
        if (auth()->check()) {
            $m->usr_inclusao  ??= auth()->id();
            $m->usr_alteracao ??= auth()->id();
        }
        $m->dat_inclusao  ??= now();
        $m->dat_alteracao ??= now();
    });
    static::updating(function ($m) {
        if (auth()->check()) $m->usr_alteracao = auth()->id();
        $m->dat_alteracao = now();
    });
}
```

### SP-2. Factory `defaults()` + `seedDefaults()` idempotente
**Source:** `database/factories/TipoEquipeFactory.php` (arquivo inteiro).
**Apply to:** `database/factories/EquipeFactory.php`.
**Adapt:** lista de defaults (11 equipes VEM em vez da lista multi-movimento original). Manter `firstOrCreate` com chave composta que case com unique index da migration.

### SP-3. SoftDeletes + timestamps convention
**Source:** todas as migrations de domínio em `database/migrations/2025_06_06_152302_create.php`.
**Apply to:** `equipes` (sim), `equipe_usuario` (sim, mas **sem** `timestamps()` tradicional — usar `dat_*` manuais).
**Adapt:** pivot usa naming custom (`dat_*`), tabela principal usa `timestamps()` + `softDeletes()` padrão.

### SP-4. PK `idt_X` convention
**Source:** universal no projeto (`tipo_equipe`, `tipo_movimento`, `pessoa`, `ficha`, `evento`).
**Apply to:** `equipes` → `idt_equipe`, `equipe_usuario` → `idt_equipe_usuario`.
**Deviation:** `equipe_usuario.user_id` NÃO é `idt_usuario` — porque `users.id` é PK padrão Laravel. Documentado em REQUIREMENTS.

### SP-5. FK via `foreignId()->constrained('tabela', 'idt_X')`
**Source:** `database/migrations/2025_06_06_152302_create.php` linhas 52–53, 63–64, 83–84, etc.
**Apply to:** todas as FKs novas. Formato: `$table->foreignId('idt_movimento')->constrained('tipo_movimento', 'idt_movimento')`.

### SP-6. Seeder guard idempotente
**Source:** `database/seeders/DominiosSeeder.php` linhas ~13–15.
**Apply to:** `EquipeVEMSeeder::run()`.
**Template:** `if (Equipe::count() > 0) return;` antes de qualquer `firstOrCreate`.

### SP-7. Enum como novo pattern
**Source:** **NENHUM** — PapelEquipe é o primeiro. Estabelece o padrão para futuras fases.
**Apply to:** `app/Enums/PapelEquipe.php`, e **qualquer enum futuro no projeto**.
**Template:** backed string enum + `label()` + `labels(): array` + helpers semânticos (`isCoordenador()`).

### SP-8. Pest feature test structure
**Source:** `tests/Feature/TipoEquipeTest.php` + `tests/Feature/TipoMovimentoTest.php`.
**Apply to:** `EquipeTest.php`, `EquipeUsuarioTest.php`, `EquipeVEMSeederTest.php`.
**Template:**
```php
uses(RefreshDatabase::class, CrudBasic::class);
describe('Escopo::operacao', function () {
    beforeEach(fn () => createMovimentos());
    test('descrição em pt_BR', fn () => /* ... */);
});
```

---

## No Analog Found

| File | Role | Reason | Proposed Pattern |
|------|------|--------|------------------|
| `app/Enums/PapelEquipe.php` | enum | Nenhum enum PHP 8.1+ existe em `app/`. Padrão atual é constante de classe (ex.: `User::ROLE_USER`, `TipoMovimento::VEM`). | PHP 8.1 backed string enum + `label()` + `labels()` + helpers semânticos. Ver seção 3 deste documento. |

**Nota para gsd-planner:** esta é a oportunidade de **estabelecer a convenção** do projeto para enums. PLAN.md deve mencionar explicitamente que `PapelEquipe` é o **pattern template** para enums futuros.

---

## Metadata

**Analog search scope:**
- `app/Models/*.php` (11 modelos analisados)
- `database/migrations/*.php` (migrations de 2025 + 2026)
- `database/factories/*.php` (7 factories)
- `database/seeders/*.php` (5 seeders)
- `tests/Feature/*.php` + `tests/Unit/*.php` + `tests/Pest.php`
- `app/Enums/` (não existe)

**Files scanned (lidos parcialmente ou integralmente):**
- `app/Models/TipoEquipe.php`, `TipoMovimento.php`, `Trabalhador.php`, `Ficha.php`, `User.php`, `Pessoa.php`, `Voluntario.php`
- `database/migrations/2025_06_06_152302_create.php` (bloco `tipo_equipe` + `down()`)
- `database/migrations/2026_04_18_133105_add_usuario_ficha_table.php`
- `database/migrations/2025_07_16_123833_add_role_to_users_table.php`
- `database/factories/TipoEquipeFactory.php` (integral)
- `database/factories/TrabalhadorFactory.php` + `UserFactory.php`
- `database/seeders/DominiosSeeder.php` + `DatabaseSeeder.php`
- `tests/Pest.php`, `tests/TestCase.php`, `tests/Unit/CrudBasic.php`
- `tests/Feature/TipoEquipeTest.php`, `TipoMovimentoTest.php`, `ParticipanteTest.php`
- `.planning/ROADMAP.md`, `.planning/REQUIREMENTS.md`, `.planning/codebase/ARCHITECTURE.md`

**Pattern extraction date:** 2026-04-21.

**Cross-check grep evidence:**
- `declare(strict_types=1)` em `app/`: 0 matches → NÃO introduzir.
- `enum.*string|BackedEnum` em `app/`: 0 matches → primeiro enum.
- `usr_inclusao|dat_inclusao` em projeto: só em `.planning/` docs → novo naming.
- `usu_inclusao` em projeto: `app/Models/Ficha.php` + `database/migrations/2026_04_18_133105_add_usuario_ficha_table.php` → precedent alternativo.

---

## Ready for gsd-planner

Este documento mapeia **cada um dos 9 novos arquivos** da Fase 1 a um (ou mais) análogo(s) concreto(s) no codebase, com:
- Caminho absoluto/relativo do análogo.
- Por que faz match (1 linha).
- O que copiar (shell, hooks, casts, imports).
- O que adaptar (nomes de campos, papéis, 11 equipes, `usr_*`/`dat_*`).
- Shared patterns transversais (SP-1 a SP-8).
- "No analog" flag para `PapelEquipe` com proposta de pattern.

gsd-planner pode referenciar diretamente as seções numeradas (1–9) e SP-1..SP-8 em cada plano-de-arquivo do PLAN.md.
