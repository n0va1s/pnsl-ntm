# Phase 1: Fundação de dados e modelos de equipe — Research

**Researched:** 2026-04-21
**Domain:** Laravel 12 migrations, Eloquent pivot models, PHP 8.2 backed enums, Pest 3.8 testing
**Confidence:** HIGH (baseado em codebase real + documentação oficial Laravel 12 verificada)

---

## Summary

Esta fase cria a fundação de dados do sistema de equipes VEM: duas novas migrations (`equipes` e `equipe_usuario`), um enum PHP 8.2 de papéis escopados, dois models Eloquent (`Equipe` e `EquipeUsuario`), factory/seeder para as 11 equipes VEM, e testes de migration/seed em Pest 3.8.

O projeto já possui um padrão consolidado para todos esses artefatos, exceto enums — o PATTERNS.md (gerado antes desta pesquisa) mapeia análogos concretos para cada arquivo. Esta pesquisa valida e complementa esse mapeamento com achados críticos da documentação oficial e da análise do codebase.

**Descoberta crítica 1 (BLOQUEANTE DE DESIGN):** O Eloquent `Pivot` model **não suporta SoftDeletes**. A documentação oficial Laravel 12 afirma explicitamente: "Pivot models may not use the SoftDeletes trait. If you need to soft delete pivot records, convert your pivot model to an actual Eloquent model." O REQUIREMENTS.md (RBAC-02) exige soft deletes na pivot `equipe_usuario`. Portanto, `EquipeUsuario` DEVE estender `Model`, não `Pivot` — usando `using()` na relação mas com `public $incrementing = true` e sem extensão do `Pivot` class se SoftDeletes for necessário.

**Descoberta crítica 2 (NOMENCLATURA DE AUDITORIA):** O REQUIREMENTS.md especifica colunas `usr_inclusao`/`dat_inclusao`/`usr_alteracao`/`dat_alteracao` mas o código existente (`Ficha`) usa `usu_inclusao`/`usu_alteracao` (sem `dat_*`). O planner deve decidir qual padrão adotar e ser consistente.

**Descoberta crítica 3 (COLISÃO DE ESCOPO):** `tests/Pest.php::createMovimentos()` já insere 11 equipes em `tipo_equipe` (tabela legada). A nova tabela `equipes` é separada. Os testes de seed NÃO podem assumir que `createMovimentos()` popula `equipes` — precisam chamar explicitamente o `EquipeVEMSeeder`.

**Descoberta crítica 4 (DEPLOY):** O workflow de deploy (`deploy.yml`) NÃO executa `php artisan migrate` — só sincroniza arquivos via FTP. Migrations precisam ser executadas manualmente no Hostinger após o deploy.

**Primary recommendation:** Usar `EquipeUsuario` como `Model` (não `Pivot`) com `public $incrementing = true` para compatibilidade com `using()` na relação, garantindo suporte a SoftDeletes e o audit hook `booted()`.

---

<phase_requirements>
## Phase Requirements

| ID | Description | Research Support |
|----|-------------|------------------|
| RBAC-01 | Enum/constants de papéis (`coord-geral`, `coord-equipe-h`, `coord-equipe-m`, `membro-equipe`) em classe dedicada | PHP 8.2 backed enum `PapelEquipe: string` — VERIFIED: Laravel 12 enum casting docs |
| RBAC-02 | Migration `equipe_usuario` com FKs, `papel`, auditoria (`usr_inclusao`, `dat_inclusao`, `usr_alteracao`, `dat_alteracao`), soft deletes | Schema verificado; nota crítica: SoftDeletes exige `Model` não `Pivot` |
| RBAC-03 | Unique constraint `(user_id, equipe_id)` na pivot | `$table->unique(['user_id', 'idt_equipe'])` — padrão verificado nas migrations existentes |
| RBAC-04 | Model Pivot `EquipeUsuario` com cast do campo `papel` para o enum | `casts` array com `'papel' => PapelEquipe::class` — VERIFIED: docs oficiais |
| RBAC-05 | Relação `User::equipes()` belongsToMany via pivot com `withPivot('papel')` e `withTimestamps()` | Padrão belongsToMany verificado — nota: `withTimestamps()` conflita com dat_* manuais |
| RBAC-06 | Relação inversa `Equipe::usuarios()`, escopos `coordenadores()` e `membros()` | `wherePivotIn()` / `wherePivot()` — VERIFIED: Laravel 12 docs |
| EQUIPE-01 | Migration `equipes` (id, nome, slug, `idt_movimento` FK, descricao, ativo bool, timestamps, soft deletes) | Padrão de `tipo_equipe` migration — VERIFIED: codebase |
| EQUIPE-02 | Model `Equipe` com scopes `paraMovimento(idt)` e `ativas()`, mutator de `slug` | Analog: `TipoEquipe` model + scopes de `Trabalhador` — VERIFIED: codebase |
| EQUIPE-03 | Seeder com 11 equipes VEM | 10 equipes no `TipoEquipeFactory` existente + "Troca de Ideias" faltando — gap confirmado |
| MIG-04 | Migrations reversíveis — `down()` remove pivot e `equipes` em ordem correta | Ordem: primeiro drop `equipe_usuario` depois `equipes` — VERIFIED: FK dependency |
| TEST-05 | Teste de migration `up`/`down` reversível em SQLite | SQLite usa `foreign_key_constraints: true` por default no projeto — VERIFIED: config/database.php |
| TEST-06 | Teste do seed das 11 equipes (contagem exata + nomes + `idt_movimento = VEM`) | `assertDatabaseCount('equipes', 11)` + `where('idt_movimento', $vem->idt_movimento)` |
</phase_requirements>

---

## Architectural Responsibility Map

| Capability | Primary Tier | Secondary Tier | Rationale |
|------------|-------------|----------------|-----------|
| Schema de `equipes` e `equipe_usuario` | Database / Storage | — | Pura DDL — sem lógica de aplicação |
| Enum `PapelEquipe` | API / Backend (Domain) | — | Value object partilhado por models e futura UI |
| Model `Equipe` (scopes, relations) | API / Backend (Domain) | — | Eloquent encapsula regras de consulta |
| Model `EquipeUsuario` (audit hook) | API / Backend (Domain) | — | Booted hook é lógica de domínio (auditoria) |
| Seeder das 11 equipes | Database / Storage | — | Dados de referência |
| Factories | Database / Storage | — | Test data — sem lógica de apresentação |
| Testes Pest | — | — | Camada transversal de validação |

---

## Standard Stack

### Core (Phase 1)

| Library | Version | Purpose | Why Standard |
|---------|---------|---------|--------------|
| Laravel Migrations | 12.x | DDL schema | Framework padrão do projeto |
| Eloquent ORM | 12.x | Models + relations | Framework padrão do projeto |
| PHP Backed Enums | 8.2+ | `PapelEquipe` value object | PHP 8.2 nativo — zero dependência extra |
| Pest | 3.8 | Testes | Padrão do repo — 24 Feature + 3 Unit existentes |

### Supporting

| Library | Version | Purpose | When to Use |
|---------|---------|---------|-------------|
| `Illuminate\Support\Str` | 12.x | `Str::slug()` para mutator | Geração de slug no model `Equipe` |
| `Illuminate\Database\Eloquent\Relations\Pivot` | 12.x | Base class pivot | NÃO usar se precisar de SoftDeletes |
| `Illuminate\Database\Eloquent\SoftDeletes` | 12.x | Soft delete em pivot | Exige estender `Model`, não `Pivot` |

### Alternatives Considered

| Instead of | Could Use | Tradeoff |
|------------|-----------|----------|
| PHP backed enum | Constante de classe (padrão atual em `User::ROLE_*`) | Enums têm type-safety e cast nativo; constantes são mais simples mas sem cast automático |
| `Model` para pivot | `Pivot` puro | `Pivot` não suporta SoftDeletes; `Model` exige `$incrementing = true` e configuração manual |
| `Str::slug()` no mutator | `Str::transliterate()` + `Str::slug()` | `Str::slug` já remove acentos corretamente em PHP com `transliterator_transliterate` embutido |

---

## Architecture Patterns

### System Architecture Diagram

```
[DatabaseSeeder]
    │
    ├──calls──► [DominiosSeeder] ──popula──► tipo_movimento (VEM, ECC, SGM)
    │
    └──calls──► [EquipeVEMSeeder] ──lê VEM de tipo_movimento
                    │
                    └──chama──► [EquipeFactory::seedDefaults()]
                                    │
                                    └──firstOrCreate──► tabela: equipes (11 rows VEM)
                                                            │
                                                            └──FK──► tipo_movimento.idt_movimento


[HTTP Request / Seeder / Test]
    │
    └──cria/edita──► equipe_usuario
                        │   (idt_equipe FK → equipes)
                        │   (user_id FK → users)
                        │   (usr_inclusao FK → users, nullable)
                        │   (usr_alteracao FK → users, nullable)
                        │
                        └──booted()::creating──► preenche usr_* + dat_* automático
                        └──booted()::updating──► preenche usr_alteracao + dat_alteracao


[User model]
    └──equipes()──► belongsToMany(Equipe, 'equipe_usuario')
                        .using(EquipeUsuario)
                        .withPivot(['papel', 'deleted_at', 'usr_inclusao', 'dat_inclusao'])

[Equipe model]
    ├──usuarios()──► belongsToMany(User, 'equipe_usuario').using(EquipeUsuario)
    ├──coordenadores()──► usuarios().wherePivotIn('papel', [...])
    └──membros()──► usuarios().wherePivot('papel', MembroEquipe)
```

### Recommended Project Structure

```
app/
├── Enums/
│   └── PapelEquipe.php          # Primeiro enum do projeto
├── Models/
│   ├── Equipe.php               # Model domain (nova tabela equipes)
│   ├── EquipeUsuario.php        # Model pivot com SoftDeletes + audit
│   └── User.php                 # Adicionar relação equipes()
database/
├── factories/
│   ├── EquipeFactory.php        # definition() + defaults() + seedDefaults()
│   └── EquipeUsuarioFactory.php # definition() + state methods
├── migrations/
│   ├── YYYY_MM_DD_HHMMSS_create_equipes_table.php
│   └── YYYY_MM_DD_HHMMSS_create_equipe_usuario_table.php
└── seeders/
    ├── DatabaseSeeder.php       # Adicionar EquipeVEMSeeder::class
    └── EquipeVEMSeeder.php      # Idempotente — guard count > 0
tests/
├── Feature/
│   └── Equipes/
│       ├── EquipeMigrationTest.php   # TEST-05: up/down reversível
│       └── EquipeVEMSeederTest.php   # TEST-06: 11 equipes, idempotente
└── Unit/
    └── Models/
        └── EquipeTest.php            # scopes, relations, enum cast
```

---

## Pattern 1: Migration de tabela de domínio (equipes)

**What:** Tabela principal com PK `idt_X`, FK para `tipo_movimento`, campos de negócio, `timestamps()` e `softDeletes()`.

**When to use:** Toda entidade de domínio nova do projeto.

**Analog verificado:** `database/migrations/2025_06_06_152302_create.php` bloco `tipo_equipe` (linhas 50–58).

```php
// Source: database/migrations/2025_06_06_152302_create.php (padrão tipo_equipe)
Schema::create('equipes', function (Blueprint $table) {
    $table->id('idt_equipe');
    $table->foreignId('idt_movimento')
        ->constrained('tipo_movimento', 'idt_movimento');
    $table->string('nom_equipe', 100);
    $table->string('des_slug', 120)->index();
    $table->text('des_descricao')->nullable();
    $table->boolean('ind_ativa')->default(true);
    $table->timestamps();
    $table->softDeletes();

    // Unique por (movimento, slug) — mesmo padrão de unique_participante_per_evento
    $table->unique(['idt_movimento', 'des_slug'], 'equipes_movimento_slug_unique');
});
```

**`down()` correto:**
```php
public function down(): void
{
    Schema::dropIfExists('equipes');
}
```

---

## Pattern 2: Migration de pivot com auditoria (equipe_usuario)

**What:** Tabela pivot com 2 FKs, coluna de papel (string), colunas de auditoria, soft deletes.

**When to use:** Toda pivot com rastreabilidade de quem criou/alterou.

**Analog composto verificado:** `2025_06_06_152302_create.php` (bloco `voluntario`) + `2026_04_18_133105_add_usuario_ficha_table.php`.

**DECISAO CRITICA — nomenclatura de auditoria:**
- REQUIREMENTS.md pede: `usr_inclusao` / `dat_inclusao` / `usr_alteracao` / `dat_alteracao`
- Codebase existente (`Ficha`) usa: `usu_inclusao` / `usu_alteracao` (sem `dat_*`)
- **O planner DEVE escolher uma convenção e ser explícito.** Recomendação: adotar o padrão do REQUIREMENTS.md (`usr_*` + `dat_*`) como novo padrão, documentando a divergência do `Ficha` legado.

```php
// Source: padrão composto - codebase verificado
Schema::create('equipe_usuario', function (Blueprint $table) {
    $table->id('idt_equipe_usuario');  // PK auto-increment — obrigatório para SoftDeletes

    $table->foreignId('idt_equipe')
        ->constrained('equipes', 'idt_equipe')
        ->cascadeOnDelete();  // Deletar equipe cascata os vínculos

    $table->foreignId('user_id')
        ->constrained('users');
        // NÃO cascadeOnDelete — nunca deletar users por cascata

    $table->string('papel', 30);  // Casteado para PapelEquipe enum no model

    // Auditoria — NOVO padrão usr_* + dat_*
    $table->foreignId('usr_inclusao')->nullable()->constrained('users')->nullOnDelete();
    $table->foreignId('usr_alteracao')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamp('dat_inclusao')->nullable();
    $table->timestamp('dat_alteracao')->nullable();

    $table->softDeletes();  // deleted_at — exige Model (não Pivot) no Eloquent

    // Unique: uma pessoa só pode ter um vínculo ativo por equipe
    $table->unique(['user_id', 'idt_equipe'], 'equipe_usuario_unique');
});
```

**`down()` correto (ordem importa — FK de equipe_usuario para equipes):**
```php
public function down(): void
{
    Schema::dropIfExists('equipe_usuario');  // PRIMEIRO — tem FK para equipes
    // equipes é dropada na própria migration de equipes
}
```

---

## Pattern 3: PHP 8.2 Backed Enum — PapelEquipe

**What:** Enum de string backed com métodos de label e helpers semânticos.

**When to use:** Qualquer conjunto de valores de domínio que precisam de type-safety e cast Eloquent.

**IMPORTANTE:** Este é o PRIMEIRO enum do projeto. O projeto atualmente usa constantes de classe (`User::ROLE_USER`, `TipoMovimento::VEM`). O enum introduz um novo pattern.

**Verificado:** PHP 8.1+ backed enums são suportados nativamente pelo Laravel 12 como cast Eloquent. A documentação oficial confirma: `'papel' => PapelEquipe::class` no array `casts()`.

```php
// Source: documentação oficial Laravel 12 enum casting + PHP 8.2 spec
<?php

namespace App\Enums;

enum PapelEquipe: string
{
    case CoordGeral   = 'coord_geral';
    case CoordEquipeH = 'coord_equipe_h';
    case CoordEquipeM = 'coord_equipe_m';
    case MembroEquipe = 'membro_equipe';

    public function label(): string
    {
        return match ($this) {
            self::CoordGeral   => 'Coordenador(a) Geral',
            self::CoordEquipeH => 'Coordenador de Equipe (Homem)',
            self::CoordEquipeM => 'Coordenadora de Equipe (Mulher)',
            self::MembroEquipe => 'Membro de Equipe',
        };
    }

    /** @return array<string,string> Para uso em <flux:select> e afins */
    public static function opcoes(): array
    {
        return array_column(
            array_map(fn ($case) => ['value' => $case->value, 'label' => $case->label()], self::cases()),
            'label',
            'value'
        );
    }

    public function isCoordenador(): bool
    {
        return $this !== self::MembroEquipe;
    }

    public function requerSexo(): ?string
    {
        return match ($this) {
            self::CoordEquipeH => 'M',
            self::CoordEquipeM => 'F',
            default            => null,
        };
    }
}
```

**Valores de banco (snake_case):** `coord_geral`, `coord_equipe_h`, `coord_equipe_m`, `membro_equipe`.

**IMPORTANTE:** Os valores do enum (ex.: `coord_geral`) diferem dos rótulos usados em REQUIREMENTS.md (ex.: `coord-geral` com hífen). A coluna DB usa underscore (`string(30)`). O planner deve documentar essa decisão de naming.

---

## Pattern 4: Model Equipe (domain model com scopes e belongsToMany)

**What:** Model Eloquent para a tabela `equipes` com scopes locais e relação many-to-many para `User`.

**Analog verificado:** `app/Models/TipoEquipe.php` (modelo de domínio existente).

```php
// Source: app/Models/TipoEquipe.php (analog verificado) + Laravel 12 docs
<?php

namespace App\Models;

use App\Enums\PapelEquipe;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

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

    // Scopes (analog: Trabalhador::scopeEvento)
    public function scopeAtivas(Builder $query): Builder
    {
        return $query->where('ind_ativa', true);
    }

    public function scopeParaMovimento(Builder $query, ?int $idtMovimento): Builder
    {
        return $idtMovimento ? $query->where('idt_movimento', $idtMovimento) : $query;
    }

    // Relations
    public function movimento(): BelongsTo
    {
        return $this->belongsTo(TipoMovimento::class, 'idt_movimento');
    }

    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'equipe_usuario', 'idt_equipe', 'user_id')
            ->using(EquipeUsuario::class)
            ->withPivot(['papel', 'deleted_at', 'usr_inclusao', 'dat_inclusao', 'usr_alteracao', 'dat_alteracao'])
            ->withTimestamps();
        // NOTA: withTimestamps() gerencia created_at/updated_at; se a tabela não tiver esses
        // campos (usa dat_* manuais), remover withTimestamps() e gerenciar via booted() do model.
    }

    public function coordenadores(): BelongsToMany
    {
        return $this->usuarios()->wherePivotIn('papel', [
            PapelEquipe::CoordGeral->value,
            PapelEquipe::CoordEquipeH->value,
            PapelEquipe::CoordEquipeM->value,
        ]);
    }

    public function membros(): BelongsToMany
    {
        return $this->usuarios()->wherePivot('papel', PapelEquipe::MembroEquipe->value);
    }

    // Mutator: gera slug automaticamente se não fornecido
    // Analog: Str::slug já transliterates acentos (oração → oracao, emaús → emaus)
    protected function setNomEquipeAttribute(string $value): void
    {
        $this->attributes['nom_equipe'] = $value;
        if (empty($this->attributes['des_slug'])) {
            $this->attributes['des_slug'] = Str::slug($value);
        }
    }
}
```

---

## Pattern 5: Model EquipeUsuario (pivot com SoftDeletes e audit)

**What:** Model que representa a linha da pivot `equipe_usuario`. Estende `Model` (NÃO `Pivot`) para suportar SoftDeletes.

**CRITICO — verificado na documentação oficial Laravel 12:**
> "Pivot models may not use the SoftDeletes trait. If you need to soft delete pivot records, convert your pivot model to an actual Eloquent model."

**Analog composto verificado:** `app/Models/Ficha.php` (audit booted hook) + `Illuminate\Database\Eloquent\Relations\Pivot` (referência de como configurar com `using()`).

```php
// Source: app/Models/Ficha.php (booted audit pattern) + Laravel 12 docs (pivot usando using())
<?php

namespace App\Models;

use App\Enums\PapelEquipe;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EquipeUsuario extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'equipe_usuario';
    protected $primaryKey = 'idt_equipe_usuario';
    public $incrementing = true;    // Obrigatório ao usar Model com using() em belongsToMany
    public $timestamps = false;     // Usar dat_inclusao/dat_alteracao manuais em vez de created_at/updated_at

    protected $fillable = [
        'idt_equipe',
        'user_id',
        'papel',
        'usr_inclusao',
        'usr_alteracao',
        'dat_inclusao',
        'dat_alteracao',
    ];

    protected $casts = [
        'papel'         => PapelEquipe::class,
        'dat_inclusao'  => 'datetime',
        'dat_alteracao' => 'datetime',
    ];

    // Audit hook — NOVO padrão usr_* + dat_* (diferente do usu_* de Ficha)
    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            $now = now();
            if (auth()->check()) {
                $model->usr_inclusao  ??= auth()->id();
                $model->usr_alteracao ??= auth()->id();
            }
            $model->dat_inclusao  ??= $now;
            $model->dat_alteracao ??= $now;
        });

        static::updating(function (self $model): void {
            if (auth()->check()) {
                $model->usr_alteracao = auth()->id();
            }
            $model->dat_alteracao = now();
        });
    }

    // Relations
    public function equipe(): BelongsTo
    {
        return $this->belongsTo(Equipe::class, 'idt_equipe', 'idt_equipe');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
```

**Atualização necessária em User.php:**
```php
// Source: app/Models/User.php (adicionar ao modelo existente)
public function equipes(): BelongsToMany
{
    return $this->belongsToMany(Equipe::class, 'equipe_usuario', 'user_id', 'idt_equipe')
        ->using(EquipeUsuario::class)
        ->withPivot(['papel', 'deleted_at', 'usr_inclusao', 'dat_inclusao', 'usr_alteracao', 'dat_alteracao'])
        ->withTimestamps(); // ver nota em Equipe::usuarios()
}
```

---

## Pattern 6: Seeder idempotente (EquipeVEMSeeder)

**What:** Seeder com guard count > 0 que delega para `EquipeFactory::seedDefaults()`.

**Analog verificado:** `database/seeders/DominiosSeeder.php` (padrão exato).

**COLISÃO DE ESCOPO IDENTIFICADA:** `tests/Pest.php::createMovimentos()` popula `tipo_equipe` (tabela legada) com 11 equipes VEM, mas NÃO a nova tabela `equipes`. Os testes de seed NÃO podem depender de `createMovimentos()` para popular `equipes` — devem chamar `$this->seed(EquipeVEMSeeder::class)` explicitamente.

**Lista das 11 equipes VEM para o seeder:**
O `TipoEquipeFactory::defaults()` existente lista 11 equipes mas inclui apenas 10 VEM (falta "Troca de Ideias"). O novo seeder deve ter as 11 corretas:
1. Alimentação → `alimentacao`
2. Bandinha → `bandinha`
3. Emaús → `emaus`
4. Limpeza → `limpeza`
5. Oração → `oracao`
6. Recepção → `recepcao`
7. Reportagem → `reportagem`
8. Sala → `sala`
9. Secretaria → `secretaria`
10. Troca de Ideias → `troca-de-ideias`
11. Vendinha → `vendinha`

**Registro em DatabaseSeeder — ordem obrigatória:**
```php
$this->call([
    DominiosSeeder::class,    // PRIMEIRO — popula tipo_movimento
    EquipeVEMSeeder::class,   // SEGUNDO — precisa de tipo_movimento.VEM
    EventoSeeder::class,
    // ...
]);
```

---

## Don't Hand-Roll

| Problem | Don't Build | Use Instead | Why |
|---------|-------------|-------------|-----|
| Slug de nomes com acentos | Lógica própria de remoção | `Str::slug($value)` | Já trata NFC, transliteração de acentos do pt_BR |
| Soft delete em pivot | Coluna `ativo` booleana manual | `SoftDeletes` trait em Model (não Pivot) | Laravel integra com `->onlyTrashed()`, `restore()`, Eloquent queries |
| Audit de criador/alterador | Colunas preenchidas no controller | `booted()::creating/updating` no Model | Atomic, não pode ser esquecido em nenhum caminho de escrita |
| Pivot com comportamento extra | Lógica nos controllers | `using(EquipeUsuario::class)` na relação | Encapsula comportamento no lugar certo |
| Seed idempotente | Verificar cada registro manualmente | `firstOrCreate` com chave composta | Padrão consolidado no projeto |

---

## Common Pitfalls

### Pitfall 1: Pivot com SoftDeletes — usar Pivot em vez de Model
**What goes wrong:** Se `EquipeUsuario extends Pivot`, o trait `SoftDeletes` não funciona. Queries de soft delete falham silenciosamente ou lançam erro.
**Why it happens:** Laravel proíbe SoftDeletes em `Pivot` — é uma limitação documentada.
**How to avoid:** `EquipeUsuario extends Model` com `public $incrementing = true` e `$table = 'equipe_usuario'`.
**Warning signs:** `SoftDeletes` não tem efeito; `deleted_at` não é preenchido automaticamente.

### Pitfall 2: `withTimestamps()` vs `dat_inclusao`/`dat_alteracao` manuais
**What goes wrong:** `withTimestamps()` na relação `belongsToMany` espera colunas `created_at` e `updated_at`. Se a tabela usa `dat_inclusao`/`dat_alteracao` manuais (sem timestamps padrão), `withTimestamps()` vai tentar preencher colunas que não existem.
**Why it happens:** `$timestamps = false` no model desabilita o behavior automático, mas `withTimestamps()` na relação opera independentemente.
**How to avoid:** OU adicionar `created_at`/`updated_at` na tabela pivot E usar `withTimestamps()` OU usar apenas `dat_*` manuais no `booted()` sem `withTimestamps()`.
**Warning signs:** `Column not found: created_at` em SQLite.

### Pitfall 3: Unique constraint em SQLite com soft deletes
**What goes wrong:** A constraint `unique(['user_id', 'idt_equipe'])` na tabela conflita com registros soft-deleted — se um usuário é removido (soft-delete) de uma equipe e tenta ser re-adicionado, o `INSERT` falha por violação do unique mesmo que o registro antigo esteja soft-deleted.
**Why it happens:** SQLite e MySQL não suportam partial indexes via Laravel Schema Builder de forma transparente.
**How to avoid:** Duas opções: (A) Incluir `deleted_at` na constraint (não resolve no MySQL sem partial index nativo). (B) **Preferível:** Usar validação application-side que primeiro hard-deletes ou restaura o registro soft-deleted antes de inserir. Cobrir com teste explícito.
**Warning signs:** `UNIQUE constraint failed` ao tentar re-adicionar usuário previamente removido.

### Pitfall 4: `createMovimentos()` não popula `equipes`
**What goes wrong:** Testes que chamam `createMovimentos()` assumem que as equipes VEM estão disponíveis, mas `createMovimentos()` só popula `tipo_equipe` (tabela legada).
**Why it happens:** Ambas as tabelas têm "equipes VEM" mas são entidades separadas.
**How to avoid:** Testes de Fase 1 devem chamar `$this->seed(EquipeVEMSeeder::class)` ou `createMovimentos()` + seeder explícito.
**Warning signs:** `assertDatabaseCount('equipes', 11)` falha com 0 ao usar apenas `createMovimentos()`.

### Pitfall 5: Nomenclatura inconsistente de auditoria
**What goes wrong:** Usar `usu_*` (padrão de `Ficha`) em vez de `usr_*` (padrão do REQUIREMENTS) ou vice-versa, gerando código inconsistente entre tabelas.
**Why it happens:** REQUIREMENTS.md define `usr_*` mas o codebase existente tem `usu_*` em `Ficha`.
**How to avoid:** Decisão explícita no PLAN: adotar `usr_*`+`dat_*` como novo padrão para `equipe_usuario`, documentar divergência de `Ficha` como legado.
**Warning signs:** Code review flagea nomes inconsistentes entre models.

### Pitfall 6: Ordem de drop em `down()` das migrations
**What goes wrong:** Se a migration de `equipes` tenta dropar a tabela antes de `equipe_usuario`, o MySQL rejeita por violação de FK.
**Why it happens:** `equipe_usuario.idt_equipe` tem FK para `equipes`.
**How to avoid:** `down()` da migration de `equipe_usuario` dropa `equipe_usuario` primeiro; `down()` da migration de `equipes` dropa apenas `equipes`. A ordem de `migrate:rollback` é invertida à ordem de criação.
**Warning signs:** `Cannot drop table equipes: foreign key constraint fails` ao fazer rollback.

### Pitfall 7: Deploy sem migrate
**What goes wrong:** Após push para main, o deploy FTP sincroniza as migrations mas não as executa. As novas tabelas não existem em produção.
**Why it happens:** O `deploy.yml` não tem step de `php artisan migrate`.
**How to avoid:** Documentar o procedimento manual: após deploy FTP, acessar Hostinger hPanel terminal e executar `php artisan migrate --force`.
**Warning signs:** Erro 500 em produção por tabela inexistente após deploy.

---

## Code Examples

### Enum cast em Eloquent
```php
// Source: documentação oficial Laravel 12 - https://laravel.com/docs/12.x/eloquent-mutators#enum-casting
protected $casts = [
    'papel' => PapelEquipe::class,
];

// Uso:
$pivotRow->papel; // retorna instância de PapelEquipe
$pivotRow->papel === PapelEquipe::CoordGeral; // true/false
$pivotRow->papel->label(); // 'Coordenador(a) Geral'
```

### belongsToMany com custom pivot usando Model (não Pivot)
```php
// Source: documentação oficial Laravel 12 - https://laravel.com/docs/12.x/eloquent-relationships#defining-custom-intermediate-table-models
// NOTA: quando o pivot precisa de SoftDeletes, usar Model com incrementing = true
public function usuarios(): BelongsToMany
{
    return $this->belongsToMany(User::class, 'equipe_usuario', 'idt_equipe', 'user_id')
        ->using(EquipeUsuario::class)
        ->withPivot(['papel', 'deleted_at', 'usr_inclusao', 'dat_inclusao']);
}
```

### Scope com `wherePivotIn` para filtrar por papel
```php
// Source: documentação oficial Laravel 12 - https://laravel.com/docs/12.x/eloquent-relationships#filtering-queries-via-intermediate-table-columns
public function coordenadores(): BelongsToMany
{
    return $this->usuarios()->wherePivotIn('papel', [
        PapelEquipe::CoordGeral->value,
        PapelEquipe::CoordEquipeH->value,
        PapelEquipe::CoordEquipeM->value,
    ]);
}
```

### `Str::slug` com pt_BR (acentos)
```php
// Source: app/Models/Equipe.php (mutator proposto)
// Str::slug lida com acentos corretamente via transliteracao
Str::slug('Emaús');        // → 'emaus'
Str::slug('Oração');       // → 'oracao'
Str::slug('Troca de Ideias'); // → 'troca-de-ideias'
```

### Teste de seed com `assertDatabaseCount`
```php
// Source: padrão Pest do projeto (tests/Feature/*.php)
test('seed popula exatamente 11 equipes VEM', function () {
    createMovimentos(); // popula tipo_movimento (tipo_equipe legado)
    $this->seed(\Database\Seeders\EquipeVEMSeeder::class);
    $this->assertDatabaseCount('equipes', 11);

    $vem = \App\Models\TipoMovimento::where('des_sigla', 'VEM')->first();
    expect(\App\Models\Equipe::where('idt_movimento', $vem->idt_movimento)->count())->toBe(11);
});
```

### Teste de unique constraint na pivot
```php
// Source: padrão de teste existente em tests/Feature/ParticipanteTest.php
test('unique constraint bloqueia duplo vinculo user-equipe', function () {
    $equipe = \App\Models\Equipe::factory()->create();
    $user   = \App\Models\User::factory()->create();

    $equipe->usuarios()->attach($user->id, ['papel' => PapelEquipe::MembroEquipe->value]);

    expect(fn () => $equipe->usuarios()->attach($user->id, ['papel' => PapelEquipe::CoordGeral->value]))
        ->toThrow(\Illuminate\Database\QueryException::class);
});
```

---

## State of the Art

| Old Approach | Current Approach | When Changed | Impact |
|--------------|------------------|--------------|--------|
| Constantes de classe (`User::ROLE_*`) | PHP 8.2 backed enums | PHP 8.1+ | Type-safety, cast nativo Eloquent, serialização automática |
| Pivot sem SoftDeletes | Model (não Pivot) com SoftDeletes | Laravel 9+ (restriction continua) | Histórico de vínculos preservado |
| Enum MySQL nativo | String com check constraint (ou só string) | — | Compatibilidade SQLite/MySQL mantida |

**Deprecated/outdated:**
- Usar `Pivot` com `SoftDeletes`: não funciona em nenhuma versão do Laravel.
- `enum()` do MySQL na migration: não compatível com SQLite (dev/CI usa SQLite).

---

## Open Questions

1. **Nomenclatura de auditoria: `usr_*` + `dat_*` OU manter `usu_*` do Ficha?**
   - O que sabemos: REQUIREMENTS.md pede `usr_*`+`dat_*`; código existente (`Ficha`) usa `usu_*` sem `dat_*`.
   - O que está unclear: qual padrão adotar para `equipe_usuario`.
   - Recomendação: adotar `usr_*`+`dat_*` como novo padrão, documentar `usu_*` do Ficha como legado. Acertar Ficha futuramente.

2. **`withTimestamps()` na relação OU timestamps manuais?**
   - O que sabemos: `$timestamps = false` no model desabilita criação automática de `created_at`/`updated_at`; `withTimestamps()` na relação opera independentemente.
   - O que está unclear: Se a tabela não tiver `created_at`/`updated_at`, `withTimestamps()` vai falhar.
   - Recomendação: decidir na migration. Opção A: incluir `timestamps()` padrão + `dat_inclusao`/`dat_alteracao` ADICIONAIS (redundância intencional para auditoria de negócio). Opção B: apenas `dat_*` manuais sem `withTimestamps()` na relação.

3. **Unique constraint na pivot inclui `deleted_at` para permitir re-adição?**
   - O que sabemos: soft-delete + unique constraint sem partial index bloqueia re-adição.
   - O que está unclear: Se o projeto vai precisar re-adicionar usuários às equipes (provável — Fase 4).
   - Recomendação: validação application-side (verificar + restaurar ou criar novo registro) em vez de partial index. MySQL 8+ suporta partial index mas Laravel Schema Builder não tem suporte nativo.

4. **Nome exato do enum: `PapelEquipe` ou `PapelEquipeEnum`?**
   - Recomendação: `PapelEquipe` (sem sufixo `Enum`) — PSR/PHP community convention.

5. **Coexistência com `tipo_equipe` legado:**
   - O projeto tem `tipo_equipe` (usada em `trabalhador`, `voluntario`) e vai ter `equipes` (nova, para RBAC). São entidades separadas. Os testes precisam distinguir claramente qual tabela estão usando.

---

## Runtime State Inventory

> Fase 1 é greenfield (apenas adiciona tabelas/models). Não há rename/refactor de estado existente.

| Category | Items Found | Action Required |
|----------|-------------|------------------|
| Stored data | Nenhum dado em `equipes` ou `equipe_usuario` (tabelas ainda não existem) | Nenhuma — tabelas novas |
| Live service config | Nenhuma config externa menciona as novas tabelas | Nenhuma |
| OS-registered state | Nenhum | Nenhuma |
| Secrets/env vars | Nenhum | Nenhuma |
| Build artifacts | Nenhum | Nenhuma |

**Atenção:** `tipo_equipe` (tabela legada) **continua existindo** e sendo usada por `trabalhador` e `voluntario`. Não é renomeada. Zero impacto em dados existentes.

---

## Environment Availability

| Dependency | Required By | Available | Version | Fallback |
|------------|------------|-----------|---------|----------|
| PHP 8.2+ | Backed enums, typed properties | Confirmado (`composer.json`) | 8.2+ (CI: 8.4) | — |
| SQLite | Dev/CI database | Confirmado (`config/database.php`) | — | — |
| MySQL | Produção | Confirmado (Hostinger) | — | — |
| Pest 3.8 | Testes | Confirmado (`composer.json`) | 3.8 | — |
| `foreign_key_constraints` SQLite | FK validation em testes | **Habilitado** (`config/database.php` linha 39: `'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true)`) | — | — |

**Missing dependencies:** Nenhuma dependência bloqueante. Todas as ferramentas necessárias já estão disponíveis.

---

## Validation Architecture

### Test Framework

| Property | Value |
|----------|-------|
| Framework | Pest 3.8 |
| Config file | `phpunit.xml` |
| Quick run command | `./vendor/bin/pest tests/Feature/Equipes/ tests/Unit/Models/EquipeTest.php` |
| Full suite command | `./vendor/bin/pest` |

### Phase Requirements → Test Map

| Req ID | Behavior | Test Type | Automated Command | File Exists? |
|--------|----------|-----------|-------------------|-------------|
| RBAC-01 | Enum `PapelEquipe` tem 4 cases, `label()` retorna string pt_BR | unit | `./vendor/bin/pest tests/Unit/Models/` | Wave 0 |
| RBAC-02 | Tabela `equipe_usuario` existe com schema correto | integration | `./vendor/bin/pest tests/Feature/Equipes/EquipeMigrationTest.php` | Wave 0 |
| RBAC-03 | Unique `(user_id, idt_equipe)` bloqueia duplicata | integration | `./vendor/bin/pest tests/Feature/Equipes/` | Wave 0 |
| RBAC-04 | Cast `papel` retorna instância de `PapelEquipe` | unit | `./vendor/bin/pest tests/Unit/Models/` | Wave 0 |
| RBAC-05 | `$user->equipes` retorna `BelongsToMany` com `withPivot` | unit | `./vendor/bin/pest tests/Unit/Models/` | Wave 0 |
| RBAC-06 | `$equipe->coordenadores()` e `$equipe->membros()` filtram corretamente | integration | `./vendor/bin/pest tests/Feature/Equipes/` | Wave 0 |
| EQUIPE-01 | Tabela `equipes` existe com colunas corretas | integration | `./vendor/bin/pest tests/Feature/Equipes/EquipeMigrationTest.php` | Wave 0 |
| EQUIPE-02 | Scopes `ativas()` e `paraMovimento()` funcionam | unit | `./vendor/bin/pest tests/Unit/Models/` | Wave 0 |
| EQUIPE-03 | Seeder cria 11 equipes VEM idempotentemente | integration | `./vendor/bin/pest tests/Feature/Equipes/EquipeVEMSeederTest.php` | Wave 0 |
| MIG-04 | `migrate:rollback` remove pivot + equipes sem error FK | integration | `./vendor/bin/pest tests/Feature/Equipes/EquipeMigrationTest.php` | Wave 0 |
| TEST-05 | Migration up/down em SQLite | integration | `./vendor/bin/pest tests/Feature/Equipes/EquipeMigrationTest.php` | Wave 0 |
| TEST-06 | 11 equipes VEM com nomes + `idt_movimento = VEM` | integration | `./vendor/bin/pest tests/Feature/Equipes/EquipeVEMSeederTest.php` | Wave 0 |

### Sampling Rate

- **Per task commit:** `./vendor/bin/pest tests/Feature/Equipes/ tests/Unit/Models/ --stop-on-failure`
- **Per wave merge:** `./vendor/bin/pest`
- **Phase gate:** `./vendor/bin/pest && vendor/bin/pint --test`

### Wave 0 Gaps

- [ ] `tests/Feature/Equipes/EquipeMigrationTest.php` — cobre RBAC-02, EQUIPE-01, MIG-04, TEST-05
- [ ] `tests/Feature/Equipes/EquipeVEMSeederTest.php` — cobre EQUIPE-03, TEST-06
- [ ] `tests/Unit/Models/EquipeTest.php` — cobre RBAC-05, RBAC-06, EQUIPE-02
- [ ] `tests/Unit/Models/PapelEquipeTest.php` — cobre RBAC-01, RBAC-04
- [ ] `app/Enums/` — diretório não existe, Wave 0 deve criar

---

## Security Domain

### Applicable ASVS Categories

| ASVS Category | Applies | Standard Control |
|---------------|---------|-----------------|
| V2 Authentication | Não | Fase 1 é pura DDL/models — sem auth nova |
| V3 Session Management | Não | Sem mudança de session |
| V4 Access Control | Parcial | O enum define os papéis — verificação de acesso fica na Phase 2 |
| V5 Input Validation | Sim | `fillable` no model limita mass assignment; cast de enum rejeita valores inválidos |
| V6 Cryptography | Não | Sem dados criptografados na Fase 1 |

### Known Threat Patterns for Laravel Migrations + Eloquent

| Pattern | STRIDE | Standard Mitigation |
|---------|--------|---------------------|
| Mass assignment via `fill()` sem `$fillable` | Tampering | `protected $fillable` definido explicitamente em todos os models novos |
| Enum com valor inválido no banco | Tampering | Cast do Eloquent lança `ValueError` ao tentar hidratar valor inválido |
| Seed em produção acidental | Information Disclosure | Guard `if (Equipe::count() > 0) return;` no seeder; seed só via `DatabaseSeeder` explícito |
| FKs sem `nullOnDelete` em `usr_inclusao` | Integrity | `nullOnDelete()` nas FKs de auditoria — usuário deletado não bloqueia deleção de pivot |

---

## Assumptions Log

| # | Claim | Section | Risk if Wrong |
|---|-------|---------|---------------|
| A1 | `Str::slug('Emaús')` retorna `'emaus'` corretamente | Pattern 4 (mutator) | Slugs com acentos incorretos causariam falha no unique constraint ao re-seed |
| A2 | SQLite aceita `withTimestamps()` em relação quando a tabela não tem `created_at` | Pitfall 2 | Erro runtime em SQLite ao usar a relação com pivot |
| A3 | O deploy Hostinger requer migrate manual após FTP sync | Common Pitfalls | Se havia migrate automático, o aviso seria desnecessário |

---

## Sources

### Primary (HIGH confidence)
- Documentação oficial Laravel 12 — https://laravel.com/docs/12.x/eloquent-relationships#defining-custom-intermediate-table-models — Pivot + SoftDeletes limitation VERIFIED
- Documentação oficial Laravel 12 — https://laravel.com/docs/12.x/eloquent-mutators#enum-casting — Enum cast VERIFIED
- Documentação oficial Laravel 12 — https://laravel.com/docs/12.x/migrations — Schema, unique, FKs VERIFIED
- Documentação oficial Laravel 12 — https://laravel.com/docs/12.x/eloquent-relationships#many-to-many — `wherePivotIn`, `withPivot` VERIFIED
- Codebase verificado via Read/Bash/Grep: `app/Models/User.php`, `Pessoa.php`, `TipoEquipe.php`, `TipoMovimento.php`, `Ficha.php`
- Codebase verificado: `database/migrations/2025_06_06_152302_create.php`, `2026_04_18_133105_add_usuario_ficha_table.php`
- Codebase verificado: `database/factories/TipoEquipeFactory.php`, `database/seeders/DominiosSeeder.php`
- Codebase verificado: `tests/Pest.php`, `app/Http/Middleware/OnlyManagerMiddleware.php`, `bootstrap/app.php`
- Codebase verificado: `.github/workflows/deploy.yml` (sem migrate automático)
- Codebase verificado: `config/database.php` (`foreign_key_constraints: true`)
- Codebase verificado: `phpunit.xml` (`DB_DATABASE=database/testing.sqlite`)
- PATTERNS.md pre-existente em `.planning/phases/01-fundacao-dados-modelos/PATTERNS.md`

### Secondary (MEDIUM confidence)
- `TipoEquipeFactory::defaults()` verificado — lista 10 VEM (falta "Troca de Ideias") vs. 11 exigidas pelo REQUIREMENTS.md

### Tertiary (LOW confidence)
- Nenhum item LOW confidence nesta pesquisa.

---

## Metadata

**Confidence breakdown:**
- Standard stack: HIGH — stack 100% determinada pelo projeto existente
- Architecture: HIGH — patterns verificados no codebase real
- Pitfalls: HIGH — critical pitfalls documentados pela docs oficiais (Pivot + SoftDeletes) e análise de código
- Nomenclatura de auditoria: MEDIUM — divergência real entre REQUIREMENTS e codebase, requer decisão explícita do planner

**Research date:** 2026-04-21
**Valid until:** 2026-05-21 (stack estável, mas verificar se Laravel 12 receber patch sobre Pivot/SoftDeletes)

---

## Project Constraints (from CLAUDE.md)

Diretivas extraídas do `CLAUDE.md` do projeto para que o planner verifique compliance:

| Diretiva | Relevância para Fase 1 |
|----------|----------------------|
| PSR-12, `declare(strict_types=1)` | **ATENÇÃO:** grep confirma que `declare(strict_types=1)` NÃO é usado em `app/` — não introduzir na Fase 1 para manter consistência |
| DTOs readonly imutáveis | Não aplicável na Fase 1 (sem DTOs nesta fase) |
| `FormRequest` para validação | Não aplicável (sem HTTP endpoints na Fase 1) |
| Services fat para regra de negócio | Não aplicável (Fase 1 é pura fundação de dados) |
| Pest ≥80% coverage em features novas | **CRITICO** — todos os arquivos novos em `app/` precisam de cobertura |
| `wc -l` antes de abrir arquivo >300 linhas | Diretiva de desenvolvimento — planner deve incluir em instruções de tarefa |
| Contar linhas antes de abrir arquivo | Diretiva operacional para execução |
| SQLite dev/test, MySQL produção | Migrations devem ser compatíveis com ambos — verificado |
| `vendor/bin/pint --test` no CI | Fase 1 deve passar Pint antes do merge |
