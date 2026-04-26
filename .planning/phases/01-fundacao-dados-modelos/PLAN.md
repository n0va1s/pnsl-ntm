---
phase: 01-fundacao-dados-modelos
plan: 01
type: execute
wave: 1
depends_on: []
files_modified:
  - app/Enums/PapelEquipe.php
  - app/Models/Equipe.php
  - app/Models/EquipeUsuario.php
  - app/Models/User.php
  - database/migrations/2026_04_21_000001_create_equipes_table.php
  - database/migrations/2026_04_21_000002_create_equipe_usuario_table.php
  - database/factories/EquipeFactory.php
  - database/factories/EquipeUsuarioFactory.php
  - database/seeders/EquipeVEMSeeder.php
  - database/seeders/DatabaseSeeder.php
  - tests/Unit/Enums/PapelEquipeTest.php
  - tests/Feature/Equipes/EquipeMigrationTest.php
  - tests/Feature/Equipes/EquipeUsuarioMigrationTest.php
  - tests/Unit/Models/EquipeTest.php
  - tests/Unit/Models/EquipeUsuarioTest.php
  - tests/Unit/Models/UserEquipesTest.php
  - tests/Feature/Equipes/EquipeVEMSeederTest.php
autonomous: true
requirements:
  - RBAC-01
  - RBAC-02
  - RBAC-03
  - RBAC-04
  - RBAC-05
  - RBAC-06
  - EQUIPE-01
  - EQUIPE-02
  - EQUIPE-03
  - MIG-04
  - TEST-05
  - TEST-06

must_haves:
  truths:
    - "php artisan migrate:fresh --seed cria 11 equipes VEM em SQLite"
    - "php artisan migrate:fresh --seed cria 11 equipes VEM em MySQL"
    - "Migrations são reversíveis (migrate:rollback derruba equipe_usuario antes de equipes)"
    - "User pode ter múltiplas equipes via pivot com papel (4 enums) e colunas de auditoria"
    - "Equipe::ativas() e Equipe::paraMovimento() filtram corretamente"
    - "EquipeUsuario preenche usr_inclusao/dat_inclusao ao criar e usr_alteracao/dat_alteracao ao atualizar"
    - "Unique (user_id, idt_equipe) no pivot impede duplicatas em ambos drivers"
    - "Seeder é idempotente (contagem não dobra se rodar duas vezes)"
  artifacts:
    - path: "app/Enums/PapelEquipe.php"
      provides: "Enum backed string com 4 papéis"
      contains: "enum PapelEquipe: string"
    - path: "app/Models/Equipe.php"
      provides: "Model Equipe com scopes e relações"
      contains: "class Equipe extends Model"
    - path: "app/Models/EquipeUsuario.php"
      provides: "Pivot model com SoftDeletes e audit"
      contains: "class EquipeUsuario extends Model"
    - path: "database/migrations/2026_04_21_000001_create_equipes_table.php"
      provides: "Tabela equipes"
    - path: "database/migrations/2026_04_21_000002_create_equipe_usuario_table.php"
      provides: "Tabela equipe_usuario (pivot)"
    - path: "database/seeders/EquipeVEMSeeder.php"
      provides: "Seeder das 11 equipes VEM"
      contains: "class EquipeVEMSeeder"
  key_links:
    - from: "app/Models/User.php"
      to: "app/Models/Equipe.php"
      via: "belongsToMany com using(EquipeUsuario::class)"
      pattern: "belongsToMany.*Equipe::class.*using.*EquipeUsuario::class"
    - from: "app/Models/Equipe.php"
      to: "app/Models/EquipeUsuario.php"
      via: "relação usuarios() com using() e withPivot('papel')"
      pattern: "using\\(EquipeUsuario::class\\)"
    - from: "database/seeders/DatabaseSeeder.php"
      to: "database/seeders/EquipeVEMSeeder.php"
      via: "$this->call(EquipeVEMSeeder::class)"
      pattern: "EquipeVEMSeeder::class"
---

# Phase 01 — Fundação de dados e modelos de equipe

<phase_header>
**Milestone:** v1.1 — Gestão de Equipes VEM (Fundação)
**Phase:** 1 de 5 — Fundação de dados e modelos de equipe
**Goal (verbatim, source: ROADMAP.md):**

> Persistir a estrutura de equipes e vínculos no banco, com models e seeder prontos para uso a jusante; `php artisan migrate:fresh --seed` produz 11 equipes VEM em SQLite e MySQL.

**REQ-IDs cobertos (12 de 12):** RBAC-01, RBAC-02, RBAC-03, RBAC-04, RBAC-05, RBAC-06, EQUIPE-01, EQUIPE-02, EQUIPE-03, MIG-04, TEST-05, TEST-06
</phase_header>

<execution_context>
@$HOME/.claude/get-shit-done/workflows/execute-plan.md
@$HOME/.claude/get-shit-done/templates/summary.md
</execution_context>

<context>
@.planning/PROJECT.md
@.planning/ROADMAP.md
@.planning/REQUIREMENTS.md
@.planning/STATE.md
@.planning/phases/01-fundacao-dados-modelos/RESEARCH.md
@.planning/phases/01-fundacao-dados-modelos/PATTERNS.md
@CLAUDE.md

<interfaces>
<!-- Contratos críticos extraídos da base e de REQUIREMENTS/RESEARCH. -->
<!-- Executor DEVE usar estas assinaturas; NÃO explorar base para descobrir. -->

Enum PapelEquipe (novo — primeiro enum do projeto):
```php
// app/Enums/PapelEquipe.php
enum PapelEquipe: string
{
    case CoordGeral   = 'coord_geral';
    case CoordEquipeH = 'coord_equipe_h';
    case CoordEquipeM = 'coord_equipe_m';
    case MembroEquipe = 'membro_equipe';

    public function label(): string;                 // pt_BR
    public static function opcoes(): array;          // value => label
    public function isCoordenador(): bool;           // true para todos exceto MembroEquipe
    public function requerSexo(): ?string;           // 'M' para CoordEquipeH, 'F' para CoordEquipeM, null caso contrário
}
```

Model Equipe (novo):
```php
// app/Models/Equipe.php
class Equipe extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'equipes';
    protected $primaryKey = 'idt_equipe';
    protected $fillable = ['idt_movimento', 'nom_equipe', 'des_slug', 'des_descricao', 'ind_ativa'];
    protected $casts = ['ind_ativa' => 'boolean'];

    public function scopeAtivas(Builder $q): Builder;
    public function scopeParaMovimento(Builder $q, int $idt): Builder;
    public function setNomEquipeAttribute(string $value): void; // seta des_slug via Str::slug

    public function movimento(): BelongsTo;       // TipoMovimento
    public function usuarios(): BelongsToMany;    // using(EquipeUsuario::class)->withPivot('papel', 'usr_inclusao', 'usr_alteracao', 'dat_inclusao', 'dat_alteracao')->withTrashed on pivot
    public function coordenadores(): BelongsToMany; // filtro pivot papel != 'membro_equipe'
    public function membros(): BelongsToMany;       // filtro pivot papel = 'membro_equipe'
}
```

Model EquipeUsuario (pivot custom, novo):
```php
// app/Models/EquipeUsuario.php
class EquipeUsuario extends Model  // *** NÃO Pivot *** — SoftDeletes exige Model
{
    use HasFactory, SoftDeletes;
    public $incrementing = true;
    public $timestamps = false;
    protected $table = 'equipe_usuario';
    protected $primaryKey = 'idt_equipe_usuario';
    protected $fillable = ['idt_equipe', 'user_id', 'papel', 'usr_inclusao', 'usr_alteracao', 'dat_inclusao', 'dat_alteracao'];
    protected $casts = [
        'papel' => PapelEquipe::class,
        'dat_inclusao' => 'datetime',
        'dat_alteracao' => 'datetime',
    ];

    protected static function booted(): void {
        static::creating(function ($m) { /* usr_inclusao + dat_inclusao */ });
        static::updating(function ($m) { /* usr_alteracao + dat_alteracao */ });
    }
}
```

User::equipes() (modificação em model existente):
```php
// app/Models/User.php — adicionar método
public function equipes(): BelongsToMany
{
    return $this->belongsToMany(Equipe::class, 'equipe_usuario', 'user_id', 'idt_equipe')
        ->using(EquipeUsuario::class)
        ->withPivot(['papel', 'usr_inclusao', 'usr_alteracao', 'dat_inclusao', 'dat_alteracao'])
        ->whereNull('equipe_usuario.deleted_at');
}
```

Schemas das migrations (assinatura final — referência obrigatória):
```php
// 2026_04_21_000001_create_equipes_table.php
Schema::create('equipes', function (Blueprint $table) {
    $table->id('idt_equipe');
    $table->foreignId('idt_movimento')->constrained('tipo_movimento', 'idt_movimento');
    $table->string('nom_equipe', 100);
    $table->string('des_slug', 120)->index();
    $table->text('des_descricao')->nullable();
    $table->boolean('ind_ativa')->default(true);
    $table->timestamps();
    $table->softDeletes();
    $table->unique(['idt_movimento', 'des_slug'], 'equipes_movimento_slug_unique');
});

// 2026_04_21_000002_create_equipe_usuario_table.php
Schema::create('equipe_usuario', function (Blueprint $table) {
    $table->id('idt_equipe_usuario');
    $table->foreignId('idt_equipe')->constrained('equipes', 'idt_equipe')->cascadeOnDelete();
    $table->foreignId('user_id')->constrained('users');
    $table->string('papel', 30);
    $table->foreignId('usr_inclusao')->nullable()->constrained('users')->nullOnDelete();
    $table->foreignId('usr_alteracao')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamp('dat_inclusao')->nullable();
    $table->timestamp('dat_alteracao')->nullable();
    $table->softDeletes();
    $table->unique(['user_id', 'idt_equipe'], 'equipe_usuario_unique');
});

// down() em AMBAS: Schema::dropIfExists na ordem inversa (pivot ANTES de equipes)
```

Os 11 nomes VEM (fonte: REQUIREMENTS.md EQUIPE-03, ordem alfabética pt_BR):
| nom_equipe       | des_slug           |
|------------------|--------------------|
| Alimentação      | alimentacao        |
| Bandinha         | bandinha           |
| Emaús            | emaus              |
| Limpeza          | limpeza            |
| Oração           | oracao             |
| Recepção         | recepcao           |
| Reportagem       | reportagem         |
| Sala             | sala               |
| Secretaria       | secretaria         |
| Troca de Ideias  | troca-de-ideias    |
| Vendinha         | vendinha           |
</interfaces>
</context>

<scope_summary>

## Em escopo (nesta fase)

- Enum `PapelEquipe` (primeiro enum do projeto — estabelece padrão)
- Migrations `equipes` e `equipe_usuario` (reversíveis, compatíveis MySQL + SQLite)
- Models `Equipe` (com scopes e mutator de slug) e `EquipeUsuario` (pivot custom com SoftDeletes e audit)
- Extensão de `User` com `equipes()` belongsToMany
- Factories `EquipeFactory` (+ `seedDefaults()`) e `EquipeUsuarioFactory`
- Seeder `EquipeVEMSeeder` idempotente, registrado em `DatabaseSeeder`
- Suíte Pest de unidade e feature para todos os 12 REQ-IDs
- Validação dual-driver: `php artisan migrate:fresh --seed` deve passar em SQLite (default CI) e MySQL (prod)

## Diferido para fases posteriores (NÃO implementar aqui)

- Policies, Gates, middleware de autorização → Phase 2
- FormRequests e Volt SFCs (CRUD UI) → Phase 3
- Atribuição de membros/coordenadores via UI → Phase 4
- Hardening, coverage final, regressão da suite legada → Phase 5

## Fora do milestone v1.1 (não tocar)

- Espaços de equipe, Gamificação, Vendinha, IA Vendinha

</scope_summary>

<prerequisites>

## Estado esperado do repositório

- Branch atual: `main` (per `git status` atual)
- Migrations já presentes: tabelas `users`, `tipo_movimento`, `tipo_equipe` (legado — NÃO remover), `pessoas`, `ficha`, `voluntario` e demais tabelas do sistema
- Tabelas VEM que ESTE plano introduz: `equipes`, `equipe_usuario` — não podem existir previamente (migrations devem ser novas)
- `database/seeders/DatabaseSeeder.php` existe (precisará de um `$this->call(EquipeVEMSeeder::class)`)
- `app/Models/User.php` existe — receberá método `equipes()` (não alterar outros métodos)
- `app/Enums/` pode não existir como diretório — criar se necessário

## Stack e versões confirmadas (RESEARCH.md)

- PHP 8.2+ (suporte nativo a backed enums)
- Laravel 12.x
- Pest 3.8 com `RefreshDatabase` + trait `CrudBasic` já utilizada em testes existentes
- SQLite com `foreign_key_constraints` habilitado em `config/database.php`
- MySQL em produção (Hostinger)

## Dependências já instaladas (composer)

- `laravel/framework`, `livewire/livewire`, `livewire/flux`, `livewire/volt`, `pestphp/pest`, `pestphp/pest-plugin-laravel` — todos presentes em `composer.json`
- Nenhuma nova dependência será adicionada nesta fase

## Decisões de design pré-travadas (CONTEXT + RESEARCH)

- **D-01** `EquipeUsuario extends Model` (NÃO `Pivot`) — `SoftDeletes` não é suportado em `Pivot` no Laravel 12 (RESEARCH Critical Discovery 1)
- **D-02** Auditoria via `usr_*` + `dat_*` — novo padrão. `Ficha` mantém `usu_*` legado documentado
- **D-03** PK `idt_X` segue convenção do projeto; `user_id` permanece default por ser FK para `users.id` (Laravel PK)
- **D-04** Tabela `equipes` no plural (contrariando convenção singular do projeto) por exigência explícita de REQUIREMENTS EQUIPE-01
- **D-05** Enum values em snake_case (`coord_geral`), labels em pt_BR
- **D-06** Unique `(user_id, idt_equipe)` a nível de DB; restauração pós soft-delete é responsabilidade de camada de aplicação (Phase 4)
- **D-07** Helper `createMovimentos()` em `tests/Pest.php` NÃO será reusado (popula `tipo_equipe` legado, não `equipes`). Testes devem `$this->seed(EquipeVEMSeeder::class)` explicitamente
- **D-08** Deploy: `php artisan migrate` é manual no Hostinger após FTP. Fase documenta mas não automatiza
- **D-09** Migration pivot com ambos `timestamps()` omitidos em favor de `dat_inclusao`/`dat_alteracao` explícitos; `withTimestamps()` **não** usar na relação User↔Equipe

</prerequisites>

<tasks>

<task type="auto" tdd="true">
  <name>Task 1 — Criar enum PapelEquipe (RBAC-01)</name>
  <files>
    - app/Enums/PapelEquipe.php (criar)
    - tests/Unit/Enums/PapelEquipeTest.php (criar)
  </files>
  <behavior>
    - PapelEquipe::cases() retorna exatamente 4 casos na ordem CoordGeral, CoordEquipeH, CoordEquipeM, MembroEquipe
    - Valores de string: 'coord_geral', 'coord_equipe_h', 'coord_equipe_m', 'membro_equipe'
    - label() retorna pt_BR: "Coordenador Geral", "Coordenador de Equipe H", "Coordenador de Equipe M", "Membro de Equipe"
    - opcoes() retorna array associativo value => label com os 4 pares
    - isCoordenador(): true para CoordGeral, CoordEquipeH, CoordEquipeM; false para MembroEquipe
    - requerSexo(): 'M' para CoordEquipeH, 'F' para CoordEquipeM, null para CoordGeral e MembroEquipe
    - from('coord_geral') retorna PapelEquipe::CoordGeral (teste do contrato backed enum)
    - tryFrom('invalido') retorna null (teste de robustez)
  </behavior>
  <action>
    Criar `app/Enums/PapelEquipe.php` como enum backed string. Estabelece o padrão de enums do projeto (por D-05 — snake_case nos valores, labels pt_BR). NÃO usar `declare(strict_types=1);` por regra do projeto (PATTERNS MR-1).

    Seguir TDD: escrever `tests/Unit/Enums/PapelEquipeTest.php` PRIMEIRO com `describe('PapelEquipe enum')` e pelo menos os testes: "tem exatamente 4 papéis", "valores em snake_case", "label retorna pt_BR", "opcoes() devolve value=>label", "isCoordenador distingue membro dos demais", "requerSexo retorna M/F/null conforme papel", "from() e tryFrom() funcionam". Rodar `./vendor/bin/pest tests/Unit/Enums/PapelEquipeTest.php` → DEVE FALHAR (RED). Então implementar o enum mínimo que passa nos testes (GREEN). REFACTOR se necessário para clareza (ex.: extrair mapa de labels em match).

    Commit sugerido: `feat(phase-01/enum): introduzir enum PapelEquipe com 4 papeis (RBAC-01)`
  </action>
  <verify>
    <automated>./vendor/bin/pest tests/Unit/Enums/PapelEquipeTest.php --compact</automated>
  </verify>
  <done>
    - Arquivo `app/Enums/PapelEquipe.php` existe e declara `enum PapelEquipe: string`
    - Todos os testes unitários do enum passam
    - Enum é importável via `use App\Enums\PapelEquipe;` sem erros de lint
  </done>
</task>

<task type="auto" tdd="true">
  <name>Task 2 — Migration equipes (EQUIPE-01, MIG-04 parcial, TEST-05 parcial)</name>
  <files>
    - database/migrations/2026_04_21_000001_create_equipes_table.php (criar)
    - tests/Feature/Equipes/EquipeMigrationTest.php (criar)
  </files>
  <behavior>
    - `migrate:fresh` cria tabela `equipes` com colunas: idt_equipe (PK bigint), idt_movimento (FK tipo_movimento), nom_equipe (string 100), des_slug (string 120 indexada), des_descricao (text nullable), ind_ativa (boolean default true), created_at, updated_at, deleted_at
    - Unique composto `(idt_movimento, des_slug)` com nome `equipes_movimento_slug_unique`
    - `migrate:rollback --step=1` remove a tabela sem erro
    - FK `idt_movimento` aponta para `tipo_movimento.idt_movimento` (confirmar com Schema::getColumnType e assert indireto via insert)
    - Funciona em SQLite (usado pelo test runner)
  </behavior>
  <action>
    TDD. Criar primeiro `tests/Feature/Equipes/EquipeMigrationTest.php`:
    - `uses(RefreshDatabase::class)`
    - `test('tabela equipes é criada com todas as colunas esperadas')` → `Schema::hasTable('equipes')` + `Schema::hasColumns('equipes', [...])`
    - `test('equipes tem PK idt_equipe bigint autoincrement')`
    - `test('equipes tem unique composto idt_movimento+des_slug')` → inserir duas linhas com mesmo par deve lançar `QueryException`
    - `test('equipes aceita soft delete')` → criar via DB raw, `update deleted_at`, `whereNull` exclui o registro
    - `test('migration de equipes é reversível')` → `Artisan::call('migrate:rollback', ['--step'=>1])` + `Schema::hasTable('equipes')` = false, depois `Artisan::call('migrate')` de volta para não quebrar outros testes

    Rodar → RED. Criar migration `2026_04_21_000001_create_equipes_table.php` com schema do `<interfaces>` acima. `up()` cria, `down()` executa `Schema::dropIfExists('equipes')`. Atenção: FK para `tipo_movimento` — tabela já existe na base (confirmar via `grep -rn "create('tipo_movimento'" database/migrations` se necessário); senão, documentar como pré-requisito no corpo da migration e via `Schema::hasTable('tipo_movimento')` guard em `up()` com `throw`.

    Rodar → GREEN. Ajustar nomes de índices se colidirem (nome `equipes_movimento_slug_unique` é explicitamente curto para não estourar limite do MySQL).

    Commit sugerido: `feat(phase-01/db): migration da tabela equipes com FK e soft delete (EQUIPE-01, MIG-04)`
  </action>
  <verify>
    <automated>./vendor/bin/pest tests/Feature/Equipes/EquipeMigrationTest.php --compact</automated>
  </verify>
  <done>
    - Arquivo de migration existe com prefixo `2026_04_21_000001_`
    - `php artisan migrate:fresh` cria a tabela sem erro em SQLite
    - Todos os testes de migration passam
    - `php artisan migrate:rollback --step=1` remove `equipes` sem erro
  </done>
</task>

<task type="auto" tdd="true">
  <name>Task 3 — Migration equipe_usuario (RBAC-02, RBAC-03, MIG-04, TEST-05 parcial)</name>
  <files>
    - database/migrations/2026_04_21_000002_create_equipe_usuario_table.php (criar)
    - tests/Feature/Equipes/EquipeUsuarioMigrationTest.php (criar)
  </files>
  <behavior>
    - Tabela `equipe_usuario` criada com colunas: idt_equipe_usuario (PK), idt_equipe (FK cascade), user_id (FK users), papel (string 30), usr_inclusao (nullable FK users nullOnDelete), usr_alteracao (nullable FK users nullOnDelete), dat_inclusao (timestamp nullable), dat_alteracao (timestamp nullable), deleted_at
    - Unique `(user_id, idt_equipe)` com nome `equipe_usuario_unique`
    - FK `idt_equipe` com cascade on delete (remover equipe derruba vínculos)
    - FK `user_id` sem cascade (preserva histórico)
    - `down()` executa drop na ordem correta respeitando FKs (pivot primeiro, conforme MIG-04)
    - Rollback completo (ambas migrations): pivot cai primeiro, depois equipes
  </behavior>
  <action>
    TDD. Criar `tests/Feature/Equipes/EquipeUsuarioMigrationTest.php`:
    - `test('tabela equipe_usuario existe com colunas esperadas')`
    - `test('pivot tem unique user_id+idt_equipe')` → criar 2 `User` via `UserFactory`, 1 `Equipe` via DB raw, inserir (u1, e1), tentar inserir (u1, e1) novamente → `QueryException`
    - `test('apagar equipe cascata vínculos')` → inserir registros, `DB::delete('delete from equipes where idt_equipe = ?')` → count em `equipe_usuario` deve ser 0
    - `test('apagar user nao cascata vínculo — apenas anula usr_inclusao/usr_alteracao')` → verificar que FK com `nullOnDelete` em `usr_inclusao` zera o campo mas mantém a linha
    - `test('migrations são reversíveis na ordem correta')` → `migrate:rollback --step=2` derruba pivot depois equipes; `Schema::hasTable` retorna false para ambas; depois `migrate` restaura

    Rodar → RED. Criar migration `2026_04_21_000002_create_equipe_usuario_table.php` com schema do `<interfaces>`. Certificar que:
    - `id('idt_equipe_usuario')` (custom PK name exige migration com id nomeado)
    - FK `idt_equipe` usa `constrained('equipes', 'idt_equipe')->cascadeOnDelete()`
    - FK `user_id` usa `constrained('users')` sem cascade
    - `usr_inclusao` e `usr_alteracao`: `foreignId(...)->nullable()->constrained('users')->nullOnDelete()`
    - `down()` faz apenas `Schema::dropIfExists('equipe_usuario')` (a outra migration cuida de equipes)

    Rodar → GREEN.

    Commit sugerido: `feat(phase-01/db): migration do pivot equipe_usuario com audit e soft delete (RBAC-02, RBAC-03, MIG-04)`
  </action>
  <verify>
    <automated>./vendor/bin/pest tests/Feature/Equipes/EquipeUsuarioMigrationTest.php --compact</automated>
  </verify>
  <done>
    - Migration existe com prefixo `2026_04_21_000002_`
    - `php artisan migrate:fresh` cria ambas as tabelas em ordem correta
    - `migrate:rollback --step=2` derruba ambas sem erros de FK
    - Todos os testes da migration do pivot passam
  </done>
</task>

<task type="auto" tdd="true">
  <name>Task 4 — Model Equipe com scopes, mutator e relações (EQUIPE-02, RBAC-06)</name>
  <files>
    - app/Models/Equipe.php (criar)
    - tests/Unit/Models/EquipeTest.php (criar)
  </files>
  <behavior>
    - `Equipe::ativas()->get()` retorna apenas `ind_ativa = true`
    - `Equipe::paraMovimento($idt)->get()` retorna apenas da movimento
    - `$equipe->nom_equipe = 'Troca de Ideias'` automaticamente seta `des_slug = 'troca-de-ideias'`
    - `Str::slug('Emaús')` resolve para `'emaus'` (sem acento, lowercase)
    - `Str::slug('Oração')` → `'oracao'`
    - `$equipe->movimento` é `BelongsTo` para `TipoMovimento`
    - `$equipe->usuarios` é `BelongsToMany` usando pivot custom e trazendo `papel` + audit
    - `$equipe->coordenadores` retorna somente vínculos com papel ≠ `membro_equipe`
    - `$equipe->membros` retorna somente vínculos com papel = `membro_equipe`
    - SoftDeletes funciona: `$equipe->delete()` não remove do DB
  </behavior>
  <action>
    Depende de: Task 1 (enum), Task 2 (migration equipes). Não depende de Task 3 para compilação, mas sim para os testes que usam a relação `usuarios()`.

    TDD. Criar `tests/Unit/Models/EquipeTest.php`:
    - `uses(RefreshDatabase::class)`
    - helper local ou direto: inserir um `tipo_movimento` (pode reusar `createMovimentos()` de `tests/Pest.php` se houver um que popula `tipo_movimento` — confira com grep)
    - `describe('Equipe model')`:
      - 'escopo ativas filtra ind_ativa = true'
      - 'escopo paraMovimento filtra por idt_movimento'
      - 'mutator de nom_equipe gera des_slug via Str::slug'
      - 'slug trata acentos pt_BR (Emaús, Oração, Troca de Ideias)'
      - 'relacao movimento retorna TipoMovimento'
      - 'relacao usuarios traz papel e colunas de auditoria no pivot'
      - 'coordenadores retorna somente papeis diferentes de membro_equipe'
      - 'membros retorna somente membro_equipe'
      - 'SoftDeletes preserva o registro'

    Rodar → RED. Criar `app/Models/Equipe.php` com:
    - `use HasFactory, SoftDeletes`
    - `$table = 'equipes'`, `$primaryKey = 'idt_equipe'`
    - `$fillable`, `$casts` conforme `<interfaces>`
    - `scopeAtivas`, `scopeParaMovimento(Builder $q, int $idt)`
    - `setNomEquipeAttribute($value)` → seta `attributes['nom_equipe']` e `attributes['des_slug'] = Str::slug($value)`
    - Relação `movimento(): BelongsTo` → `TipoMovimento::class, 'idt_movimento', 'idt_movimento'`
    - Relação `usuarios(): BelongsToMany`:
      ```php
      return $this->belongsToMany(User::class, 'equipe_usuario', 'idt_equipe', 'user_id')
          ->using(EquipeUsuario::class)
          ->withPivot(['papel', 'usr_inclusao', 'usr_alteracao', 'dat_inclusao', 'dat_alteracao'])
          ->whereNull('equipe_usuario.deleted_at');
      ```
    - `coordenadores()`: clone de `usuarios()` com `->wherePivot('papel', '!=', PapelEquipe::MembroEquipe->value)` (usar valor string, não o enum direto, para evitar cast mismatch no where)
    - `membros()`: com `->wherePivot('papel', PapelEquipe::MembroEquipe->value)`

    Rodar → GREEN. REFACTOR: extrair `whereNull('equipe_usuario.deleted_at')` para um método privado `applyPivotSoftDelete()` se padrão se repetir.

    Commit sugerido: `feat(phase-01/model): Equipe model com scopes, slug mutator e relacoes (EQUIPE-02, RBAC-06)`
  </action>
  <verify>
    <automated>./vendor/bin/pest tests/Unit/Models/EquipeTest.php --compact</automated>
  </verify>
  <done>
    - `app/Models/Equipe.php` existe com todas as assinaturas do `<interfaces>`
    - Todos os testes unitários do model passam
    - `Str::slug` trata acentos dos 11 nomes VEM corretamente (verificado via dataset Pest)
  </done>
</task>

<task type="auto" tdd="true">
  <name>Task 5 — Model EquipeUsuario (pivot) com audit booted e cast de enum (RBAC-04)</name>
  <files>
    - app/Models/EquipeUsuario.php (criar)
    - tests/Unit/Models/EquipeUsuarioTest.php (criar)
  </files>
  <behavior>
    - `EquipeUsuario::create([...])` com usuário autenticado preenche `usr_inclusao = auth()->id()` e `dat_inclusao = now()`
    - `$ev->update(['papel' => PapelEquipe::CoordGeral])` preenche `usr_alteracao` e `dat_alteracao`
    - Sem auth()->check(), campos `usr_*` ficam null (permitindo operações de seeder/factory)
    - Cast `papel`: `$ev->papel` retorna instância de `PapelEquipe` (não string)
    - SoftDeletes: `$ev->delete()` marca `deleted_at` e não remove
    - `$ev->getIncrementing()` retorna true (pivot com id custom)
    - `$ev->timestamps` é false (não usa created_at/updated_at; usa dat_*)
  </behavior>
  <action>
    Depende de: Task 1, Task 3 (migration pivot).

    TDD. Criar `tests/Unit/Models/EquipeUsuarioTest.php`:
    - `uses(RefreshDatabase::class)`
    - Setup comum: criar User autor via `UserFactory`, TipoMovimento, Equipe, User alvo
    - `describe('EquipeUsuario pivot model')`:
      - 'cast do papel resolve para enum PapelEquipe'
      - 'creating sem auth mantém usr_inclusao nulo'
      - 'creating com auth preenche usr_inclusao e dat_inclusao'
      - 'updating com auth preenche usr_alteracao e dat_alteracao (mantém usr_inclusao intacto)'
      - 'SoftDeletes preserva o registro com deleted_at'
      - 'incrementing é true e primary key é idt_equipe_usuario'
      - 'timestamps public prop é false'

    Rodar → RED. Criar `app/Models/EquipeUsuario.php`:
    - `extends Model` (NÃO `Pivot` — D-01)
    - `use HasFactory, SoftDeletes`
    - `public $incrementing = true;` `public $timestamps = false;`
    - `$table = 'equipe_usuario'`, `$primaryKey = 'idt_equipe_usuario'`
    - `$fillable` = todos os campos exceto `deleted_at`
    - `$casts = ['papel' => PapelEquipe::class, 'dat_inclusao' => 'datetime', 'dat_alteracao' => 'datetime']`
    - `protected static function booted(): void`:
      ```php
      static::creating(function (self $m): void {
          if (auth()->check()) {
              $m->usr_inclusao = $m->usr_inclusao ?? auth()->id();
          }
          $m->dat_inclusao = $m->dat_inclusao ?? now();
      });
      static::updating(function (self $m): void {
          if (auth()->check() && $m->isDirty() && !$m->isDirty('usr_alteracao')) {
              $m->usr_alteracao = auth()->id();
              $m->dat_alteracao = now();
          }
      });
      ```

    Rodar → GREEN. REFACTOR se necessário para extrair `setAuditOnCreate` / `setAuditOnUpdate`.

    Commit sugerido: `feat(phase-01/model): EquipeUsuario pivot com SoftDeletes cast enum e audit (RBAC-04)`
  </action>
  <verify>
    <automated>./vendor/bin/pest tests/Unit/Models/EquipeUsuarioTest.php --compact</automated>
  </verify>
  <done>
    - `app/Models/EquipeUsuario.php` existe e `extends Model`
    - Booted hook preenche audit em ambos creating e updating
    - Cast de `papel` retorna enum
    - Todos os testes passam
  </done>
</task>

<task type="auto" tdd="true">
  <name>Task 6 — User::equipes() belongsToMany (RBAC-05)</name>
  <files>
    - app/Models/User.php (modificar — adicionar método; NÃO remover/alterar existentes)
    - tests/Unit/Models/UserEquipesTest.php (criar)
  </files>
  <behavior>
    - `$user->equipes` é `BelongsToMany` para `Equipe`
    - Pivot traz `papel` + colunas de auditoria
    - Vínculos soft-deleted NÃO aparecem em `$user->equipes`
    - `$user->equipes()->attach($equipeId, ['papel' => 'coord_geral'])` funciona (respeita audit se auth)
    - `$user->equipes()->where('equipes.ind_ativa', true)` filtra corretamente via join
  </behavior>
  <action>
    Depende de: Task 4 (Equipe model), Task 5 (pivot model).

    **ANTES DE EDITAR**: `wc -l app/Models/User.php`. Se >300 linhas, ler em janelas ou grep por métodos já existentes (`grep -n "function " app/Models/User.php`). Identificar se já existe método `equipes()` ou relação conflitante — a suite legada tem `GamificacaoObserver` e cascata User↔Pessoa; não tocar nessas áreas.

    TDD. Criar `tests/Unit/Models/UserEquipesTest.php`:
    - `test('user pode ter multiplas equipes via pivot')`
    - `test('pivot traz papel como enum PapelEquipe')` — `$user->equipes->first()->pivot->papel instanceof PapelEquipe`
    - `test('vínculos soft-deleted nao aparecem em user->equipes')`
    - `test('attach cria registro no pivot com papel')`
    - `test('relacao nao quebra cascata User <-> Pessoa existente')` — criar user, pessoa; attach equipe; deletar user; confirmar que pessoa segue regra legada

    Rodar → RED. Editar `app/Models/User.php` adicionando:
    ```php
    public function equipes(): BelongsToMany
    {
        return $this->belongsToMany(Equipe::class, 'equipe_usuario', 'user_id', 'idt_equipe')
            ->using(EquipeUsuario::class)
            ->withPivot(['papel', 'usr_inclusao', 'usr_alteracao', 'dat_inclusao', 'dat_alteracao'])
            ->whereNull('equipe_usuario.deleted_at');
    }
    ```
    Adicionar imports `use App\Models\Equipe;`, `use App\Models\EquipeUsuario;`, `use Illuminate\Database\Eloquent\Relations\BelongsToMany;` se ainda não presentes.

    Rodar → GREEN.

    Commit sugerido: `feat(phase-01/model): User::equipes belongsToMany com pivot custom (RBAC-05)`
  </action>
  <verify>
    <automated>./vendor/bin/pest tests/Unit/Models/UserEquipesTest.php --compact</automated>
  </verify>
  <done>
    - Método `equipes()` existe em `User.php` com assinatura do `<interfaces>`
    - Testes novos passam
    - Suite legada (testes existentes em `tests/Feature/`) continua verde: `./vendor/bin/pest --exclude-group=slow`
  </done>
</task>

<task type="auto" tdd="true">
  <name>Task 7 — Factories EquipeFactory e EquipeUsuarioFactory com seedDefaults</name>
  <files>
    - database/factories/EquipeFactory.php (criar)
    - database/factories/EquipeUsuarioFactory.php (criar)
    - tests/Unit/Models/EquipeFactoryTest.php (criar — smoke tests)
  </files>
  <behavior>
    - `Equipe::factory()->create()` produz registro válido com slug determinístico
    - `EquipeFactory::defaults()` retorna array com 11 entradas (uma por equipe VEM) com `idt_movimento` resolvido para VEM, `des_slug` correto para cada acento
    - `EquipeFactory::seedDefaults()` insere as 11 equipes via `firstOrCreate` (idempotente — segunda chamada não duplica)
    - `EquipeUsuario::factory()->create()` produz vínculo válido com user e equipe criados via `for()`
    - Nenhuma factory usa timestamps `created_at`/`updated_at` no pivot (por design `$timestamps=false`)
  </behavior>
  <action>
    Depende de: Task 4, Task 5.

    Criar `EquipeFactory`:
    ```php
    class EquipeFactory extends Factory
    {
        protected $model = Equipe::class;

        public function definition(): array
        {
            $nome = $this->faker->unique()->words(2, true);
            return [
                'idt_movimento' => TipoMovimento::where('sig_movimento', 'VEM')->value('idt_movimento')
                    ?? TipoMovimento::factory()->create(['sig_movimento' => 'VEM'])->idt_movimento,
                'nom_equipe'    => ucfirst($nome),
                'des_slug'      => Str::slug($nome),
                'des_descricao' => $this->faker->sentence,
                'ind_ativa'     => true,
            ];
        }

        public function defaults(): array
        {
            $idtVEM = TipoMovimento::where('sig_movimento', 'VEM')->value('idt_movimento');
            $nomes = ['Alimentação','Bandinha','Emaús','Limpeza','Oração','Recepção','Reportagem','Sala','Secretaria','Troca de Ideias','Vendinha'];
            return array_map(fn($n) => [
                'idt_movimento' => $idtVEM,
                'nom_equipe'    => $n,
                'des_slug'      => Str::slug($n),
                'des_descricao' => null,
                'ind_ativa'     => true,
            ], $nomes);
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

    Criar `EquipeUsuarioFactory`:
    ```php
    class EquipeUsuarioFactory extends Factory
    {
        protected $model = EquipeUsuario::class;

        public function definition(): array
        {
            return [
                'idt_equipe' => Equipe::factory(),
                'user_id'    => User::factory(),
                'papel'      => PapelEquipe::MembroEquipe,
                // audit preenchido via booted hook
            ];
        }

        public function comoPapel(PapelEquipe $papel): self
        {
            return $this->state(['papel' => $papel]);
        }
    }
    ```

    TDD smoke: `tests/Unit/Models/EquipeFactoryTest.php`:
    - 'factory cria equipe válida'
    - 'defaults() retorna 11 entradas com slugs corretos para acentos' (dataset dos 11 pares nome→slug)
    - 'seedDefaults é idempotente' (rodar 2× → Equipe::count() permanece 11)
    - 'EquipeUsuarioFactory cria vínculo válido'

    Rodar → GREEN.

    Commit sugerido: `feat(phase-01/factory): EquipeFactory com defaults e EquipeUsuarioFactory (EQUIPE-03 parcial)`
  </action>
  <verify>
    <automated>./vendor/bin/pest tests/Unit/Models/EquipeFactoryTest.php --compact</automated>
  </verify>
  <done>
    - Ambas as factories existem e produzem registros válidos
    - `defaults()` contém exatamente 11 entradas na ordem alfabética
    - `seedDefaults()` é idempotente (verificado no teste)
    - Slugs dos 11 nomes conferem com tabela do `<interfaces>`
  </done>
</task>

<task type="auto" tdd="true">
  <name>Task 8 — Seeder EquipeVEMSeeder idempotente e registro em DatabaseSeeder (EQUIPE-03, TEST-06)</name>
  <files>
    - database/seeders/EquipeVEMSeeder.php (criar)
    - database/seeders/DatabaseSeeder.php (modificar — adicionar call)
    - tests/Feature/Equipes/EquipeVEMSeederTest.php (criar)
  </files>
  <behavior>
    - `php artisan db:seed --class=EquipeVEMSeeder` cria 11 equipes VEM
    - Rodar o seeder novamente NÃO duplica registros (guard por count)
    - Todas as 11 equipes têm `idt_movimento` = VEM
    - Nomes e slugs conferem exatamente com a tabela oficial de REQUIREMENTS.md EQUIPE-03
    - Seeder é chamado automaticamente em `php artisan migrate:fresh --seed` via `DatabaseSeeder`
    - Funciona em SQLite E MySQL (TEST-05 componente)
  </behavior>
  <action>
    Depende de: Task 7 (factory com seedDefaults).

    Criar `database/seeders/EquipeVEMSeeder.php`:
    ```php
    class EquipeVEMSeeder extends Seeder
    {
        public function run(): void
        {
            if (Equipe::count() > 0) {
                return;
            }
            // Garantir que TipoMovimento VEM existe; reusar helper se disponível
            if (!TipoMovimento::where('sig_movimento', 'VEM')->exists()) {
                TipoMovimento::firstOrCreate(
                    ['sig_movimento' => 'VEM'],
                    ['nom_movimento' => 'VEM', 'ind_ativa' => true]
                );
            }
            EquipeFactory::seedDefaults();
        }
    }
    ```

    **ANTES DE EDITAR `DatabaseSeeder`**: `wc -l database/seeders/DatabaseSeeder.php` e ler. Adicionar `$this->call(EquipeVEMSeeder::class);` APÓS qualquer seeder que crie TipoMovimento VEM (se houver). Se não houver, a criação está garantida dentro do próprio seeder.

    TDD. Criar `tests/Feature/Equipes/EquipeVEMSeederTest.php`:
    - `uses(RefreshDatabase::class)`
    - `test('seeder cria exatamente 11 equipes VEM')`:
      ```php
      $this->seed(EquipeVEMSeeder::class);
      expect(Equipe::count())->toBe(11);
      expect(Equipe::all()->every(fn($e) => $e->movimento->sig_movimento === 'VEM'))->toBeTrue();
      ```
    - `test('seeder é idempotente')`:
      ```php
      $this->seed(EquipeVEMSeeder::class);
      $this->seed(EquipeVEMSeeder::class);
      expect(Equipe::count())->toBe(11);
      ```
    - `test('11 nomes e slugs esperados estão presentes')` — dataset com os 11 pares nome→slug; cada um `Equipe::where('des_slug', $slug)->exists()`
    - `test('migrate:fresh --seed produz 11 equipes')`:
      ```php
      Artisan::call('migrate:fresh', ['--seed' => true]);
      expect(Equipe::count())->toBe(11);
      ```

    Commit sugerido: `feat(phase-01/seeder): EquipeVEMSeeder com 11 equipes idempotente (EQUIPE-03, TEST-06)`
  </action>
  <verify>
    <automated>./vendor/bin/pest tests/Feature/Equipes/EquipeVEMSeederTest.php --compact</automated>
  </verify>
  <done>
    - Seeder existe, é idempotente e registrado em `DatabaseSeeder`
    - `php artisan migrate:fresh --seed` produz 11 equipes VEM em SQLite (executado pelo runner de teste)
    - Todos os 11 nomes/slugs conferem com tabela oficial
    - Testes passam
  </done>
</task>

<task type="checkpoint:human-verify" gate="blocking">
  <name>Task 9 — Verificação dual-driver MySQL↔SQLite (TEST-05 completo)</name>
  <what-built>
    Migrations + seeder validados em SQLite via Pest (Tasks 2, 3, 8). Resta verificação manual em MySQL para garantir compatibilidade dual-driver por TEST-05 e pela goal da fase ("produz 11 equipes VEM em SQLite **e MySQL**").
  </what-built>
  <how-to-verify>
    1. Configurar `.env.testing.mysql` apontando para banco MySQL local (pode ser Docker ou XAMPP). Alternativa: definir variáveis inline:
       ```bash
       DB_CONNECTION=mysql DB_HOST=127.0.0.1 DB_PORT=3306 DB_DATABASE=pnsl_ntm_test \
         DB_USERNAME=root DB_PASSWORD= php artisan migrate:fresh --seed
       ```
    2. Executar:
       ```bash
       DB_CONNECTION=mysql DB_HOST=127.0.0.1 DB_PORT=3306 DB_DATABASE=pnsl_ntm_test \
         DB_USERNAME=root DB_PASSWORD= php artisan tinker --execute="echo App\Models\Equipe::count()"
       ```
       Esperado: `11`
    3. Confirmar rollback ida-e-volta:
       ```bash
       DB_CONNECTION=mysql ... php artisan migrate:rollback --step=2
       DB_CONNECTION=mysql ... php artisan migrate
       DB_CONNECTION=mysql ... php artisan db:seed --class=EquipeVEMSeeder
       ```
       Count final: `11`.
    4. Rodar a suíte Pest completa com MySQL:
       ```bash
       DB_CONNECTION=mysql DB_HOST=127.0.0.1 DB_PORT=3306 DB_DATABASE=pnsl_ntm_test \
         DB_USERNAME=root DB_PASSWORD= ./vendor/bin/pest tests/Feature/Equipes tests/Unit/Models/EquipeTest.php tests/Unit/Models/EquipeUsuarioTest.php tests/Unit/Models/UserEquipesTest.php
       ```
       Esperado: verde.
    5. Rodar a suíte completa em SQLite (driver default):
       ```bash
       ./vendor/bin/pest --compact
       ```
       Esperado: verde (inclusive suíte legada).

    Se qualquer passo falhar, documentar o erro exato e retornar para correção (provável causa: nomes de índices > 64 caracteres no MySQL, casts de enum incompatíveis, ou tamanho de string varchar).
  </how-to-verify>
  <resume-signal>
    Responder com "approved" se todos os 5 passos passaram. Caso contrário, colar o erro exato.
  </resume-signal>
</task>

</tasks>

<dependency_graph>

## Grafo de dependências

```
        ┌─────────────────┐
        │ T1 PapelEquipe  │
        └────────┬────────┘
                 │
    ┌────────────┼────────────┐
    │            │            │
    ▼            ▼            ▼
┌────────┐  ┌────────┐   ┌─────────────┐
│ T2 mig │  │ T4 mod │   │ T5 mod piv  │
│equipes │  │ Equipe │   │EquipeUsuario│
└───┬────┘  └───▲────┘   └─────▲───────┘
    │           │              │
    ▼           │              │
┌────────┐      │              │
│ T3 mig │──────┴──────────────┘
│pivot   │
└───┬────┘
    │
    ▼
┌────────┐
│ T6 User│──────┐
│::equipes│     │
└────────┘      │
                ▼
            ┌────────┐
            │ T7 fac │
            │defaults│
            └───┬────┘
                │
                ▼
            ┌────────┐
            │ T8 see │
            │ VEM    │
            └───┬────┘
                │
                ▼
            ┌────────┐
            │ T9 HUM │
            │dual db │
            └────────┘
```

## Ordem de execução obrigatória (caminho crítico)

1. **T1** (enum) — sem dependências
2. **T2** (migration equipes) — pode rodar em paralelo com T1, mas testes referenciam enum só em Task 5+
3. **T3** (migration pivot) — depende de T2 (FK `idt_equipe`)
4. **T4** (Equipe model) — depende de T1, T2
5. **T5** (EquipeUsuario model) — depende de T1, T3
6. **T6** (User::equipes) — depende de T4, T5
7. **T7** (factories) — depende de T4, T5 (instanciam modelos)
8. **T8** (seeder) — depende de T7
9. **T9** (dual-driver) — depende de todas as anteriores

## Paralelismo possível

- T1 + T2 podem ser trabalhadas em paralelo (não há import cruzado)
- T4 + T5 podem ser trabalhadas em paralelo após T1+T2+T3 prontos
- T6 e T7 podem ser trabalhadas em paralelo após T4+T5

Como este é um único plano executado por Claude, mantenha sequencial na ordem T1→T9.

</dependency_graph>

<risks>

## Riscos e mitigações

| ID | Risco | Probabilidade | Impacto | Mitigação |
|----|-------|--------------|---------|-----------|
| R-01 | Eloquent `Pivot` não suporta `SoftDeletes` (quebraria RBAC-02 em runtime) | Alta se ignorado | Bloqueante | **Decisão travada D-01**: `EquipeUsuario extends Model`. Documentado em `<interfaces>` e em `RESEARCH.md Critical Discovery 1`. Task 5 verifica via teste de soft-delete. |
| R-02 | MySQL↔SQLite divergência em unique + soft-delete | Média | Alto | Unique só em `(user_id, idt_equipe)` a nível de DB (D-06). Restauração pós-soft-delete é responsabilidade de aplicação (Phase 4). Task 9 valida em MySQL real. |
| R-03 | Nomes de índices > 64 chars no MySQL | Baixa | Alto | Nomes explícitos curtos: `equipes_movimento_slug_unique`, `equipe_usuario_unique`. |
| R-04 | `Str::slug()` não transliterar acento pt_BR | Baixa | Alto | Task 7 tem dataset Pest cobrindo Emaús→emaus, Oração→oracao, Troca de Ideias→troca-de-ideias. |
| R-05 | Helper `createMovimentos()` em `tests/Pest.php` popular `tipo_equipe` legado e confundir testes | Alta sem documentação | Médio | **Decisão D-07**: testes desta fase chamam `$this->seed(EquipeVEMSeeder::class)` explicitamente. NÃO reusar `createMovimentos()` para equipes. |
| R-06 | Divergência de auditoria (`usu_*` legado vs `usr_*` novo) causar confusão | Média | Baixo | **Decisão D-02**: novo padrão `usr_*` + `dat_*` documentado em CONTEXT/RESEARCH. `Ficha` mantém `usu_*` legado (não tocar). |
| R-07 | Cast de enum em SQLite lançar erro em where-pivot | Baixa | Médio | Scopes `coordenadores()` e `membros()` usam valor string (`PapelEquipe::MembroEquipe->value`), não o enum direto. Testado em Task 4. |
| R-08 | Migration em produção Hostinger não executa automaticamente | Alta | Médio | **Decisão D-08**: documentado em PATTERNS e neste plano. Deploy manual `php artisan migrate` obrigatório. Checkpoint T9 é local; produção é passo separado (fora desta fase). |
| R-09 | Cascata User↔Pessoa legado quebrar ao adicionar pivot | Baixa | Alto | Task 6 inclui teste de regressão explícito. FK `user_id` em pivot SEM cascade; `usr_inclusao` com `nullOnDelete`. |
| R-10 | `GamificacaoObserver` interferir em `EquipeUsuario::create` | Baixa | Médio | Observer é em outro model. Caso haja listener global, Task 5 isola via setup sem dados de ficha. |
| R-11 | Tabela `tipo_movimento` não ter registro VEM no ambiente de teste | Média | Médio | Seeder garante criação via `firstOrCreate`. Factory resolve `idt_movimento` dinamicamente. |
| R-12 | Tamanho de `papel` varchar(30) ficar apertado | Baixa | Baixo | Valor máximo atual: `coord_equipe_h` (14 chars). Folga de 16 chars para futuro. |

</risks>

<verification_matrix>

## Matriz de verificação REQ-ID × Task × Teste

| REQ-ID | Descrição (resumida) | Task(s) | Teste(s) que provam |
|--------|---------------------|---------|---------------------|
| RBAC-01 | 4 papéis como enum | T1 | `tests/Unit/Enums/PapelEquipeTest.php` — casos, valores, label, isCoordenador, requerSexo |
| RBAC-02 | Migration `equipe_usuario` com audit + soft delete | T3 | `tests/Feature/Equipes/EquipeUsuarioMigrationTest.php` — colunas, soft delete |
| RBAC-03 | Unique `(user_id, equipe_id)` | T3 | `tests/Feature/Equipes/EquipeUsuarioMigrationTest.php` — "pivot tem unique user_id+idt_equipe" |
| RBAC-04 | Pivot model casta `papel` para enum | T5 | `tests/Unit/Models/EquipeUsuarioTest.php` — "cast do papel resolve para enum" + booted hooks |
| RBAC-05 | `User::equipes()` belongsToMany com pivot + timestamps | T6 | `tests/Unit/Models/UserEquipesTest.php` — multiplas equipes, pivot traz papel, respeita soft-delete |
| RBAC-06 | `Equipe::usuarios()` + scopes coordenadores/membros | T4 | `tests/Unit/Models/EquipeTest.php` — "coordenadores retorna..." + "membros retorna..." |
| EQUIPE-01 | Migration `equipes` com campos obrigatórios | T2 | `tests/Feature/Equipes/EquipeMigrationTest.php` — colunas, índices, tipos |
| EQUIPE-02 | Model `Equipe` com scopes + mutator de slug | T4 | `tests/Unit/Models/EquipeTest.php` — ativas, paraMovimento, mutator (3 acentos) |
| EQUIPE-03 | Seeder das 11 equipes VEM | T7 + T8 | `tests/Feature/Equipes/EquipeVEMSeederTest.php` — count=11, todas VEM, 11 nomes/slugs |
| MIG-04 | Migrations reversíveis (down() correto) | T2 + T3 | `tests/Feature/Equipes/*MigrationTest.php` — "migrations são reversíveis na ordem correta" |
| TEST-05 | Migrations funcionam em SQLite E MySQL | T2 + T3 + T9 | Pest completo em SQLite (automático) + checkpoint manual T9 em MySQL |
| TEST-06 | `migrate:fresh --seed` produz 11 equipes | T8 | `tests/Feature/Equipes/EquipeVEMSeederTest.php` — "migrate:fresh --seed produz 11 equipes" |

**Cobertura:** 12 de 12 REQ-IDs cobertos. Cada um tem pelo menos um teste automatizado; TEST-05 adicionalmente tem checkpoint humano para validação MySQL.

</verification_matrix>

<threat_model>

## Trust Boundaries

| Boundary | Descrição |
|----------|-----------|
| DB driver ↔ SQL | Migrations geram DDL que deve ser válida em ambos drivers |
| Aplicação ↔ DB | Boot hooks de audit usam `auth()->id()`; dados não confiáveis se auth() não estiver configurado |
| Seeder ↔ DB | Seeder pode rodar em produção; guard por count previne duplicatas |

## STRIDE Threat Register

| ID | Categoria | Componente | Disposição | Mitigação |
|----|-----------|-----------|-----------|-----------|
| T-01-01 | Tampering | `EquipeUsuario` audit columns | mitigate | Booted hook preenche `usr_inclusao`/`usr_alteracao` automaticamente; `fillable` inclui campos para seeding, mas app layer não deve permitir input direto (será reforçado em Phase 3 via FormRequest) |
| T-01-02 | Repudiation | Alterações em vínculo user-equipe | mitigate | Colunas `usr_alteracao` + `dat_alteracao` auditam quem modificou. SoftDeletes preserva histórico |
| T-01-03 | Information Disclosure | FK `usr_inclusao` com nullOnDelete | accept | Ao deletar user autor, campo fica null (perde rastro do autor, mas vínculo em si permanece). Trade-off: privacidade > auditoria forense nesta camada |
| T-01-04 | Denial of Service | Seeder duplicado | mitigate | Guard `if (Equipe::count() > 0) return;` previne crescimento explosivo; `firstOrCreate` adicional garante idempotência no nível do registro |
| T-01-05 | Elevation of Privilege | Cast de enum permite apenas valores válidos | mitigate | Cast do enum em `$casts` faz `PapelEquipe::from()`, lançando `ValueError` em string inválida. Teste em Task 5 |

</threat_model>

<done_criteria>

## Checklist de conclusão da Phase 1

### Arquivos criados
- [ ] `app/Enums/PapelEquipe.php`
- [ ] `app/Models/Equipe.php`
- [ ] `app/Models/EquipeUsuario.php`
- [ ] `database/migrations/2026_04_21_000001_create_equipes_table.php`
- [ ] `database/migrations/2026_04_21_000002_create_equipe_usuario_table.php`
- [ ] `database/factories/EquipeFactory.php`
- [ ] `database/factories/EquipeUsuarioFactory.php`
- [ ] `database/seeders/EquipeVEMSeeder.php`

### Arquivos modificados (mínimo necessário)
- [ ] `app/Models/User.php` — apenas adição do método `equipes()` e imports
- [ ] `database/seeders/DatabaseSeeder.php` — apenas `$this->call(EquipeVEMSeeder::class);`

### Testes criados
- [ ] `tests/Unit/Enums/PapelEquipeTest.php`
- [ ] `tests/Feature/Equipes/EquipeMigrationTest.php`
- [ ] `tests/Feature/Equipes/EquipeUsuarioMigrationTest.php`
- [ ] `tests/Unit/Models/EquipeTest.php`
- [ ] `tests/Unit/Models/EquipeUsuarioTest.php`
- [ ] `tests/Unit/Models/UserEquipesTest.php`
- [ ] `tests/Unit/Models/EquipeFactoryTest.php`
- [ ] `tests/Feature/Equipes/EquipeVEMSeederTest.php`

### Comportamentos verificados
- [ ] `php artisan migrate:fresh --seed` cria 11 equipes VEM em SQLite (verificado via Pest)
- [ ] `php artisan migrate:fresh --seed` cria 11 equipes VEM em MySQL (verificado via checkpoint T9)
- [ ] `php artisan migrate:rollback --step=2` derruba pivot antes de equipes, sem erros de FK
- [ ] `./vendor/bin/pest --compact` passa 100% (incluindo suíte legada — regressão)
- [ ] Cobertura Pest da fase ≥ 80% (medir com `./vendor/bin/pest --coverage`)
- [ ] `./vendor/bin/pint --test` verde (código segue style do projeto)

### Decisões documentadas
- [ ] Divergência `usr_*` vs `usu_*` registrada em PATTERNS (já feito) e referenciada aqui
- [ ] Escolha `extends Model` (não `Pivot`) em `EquipeUsuario` tem comentário no código explicando motivo
- [ ] Helper `createMovimentos()` NÃO reusado para equipes — documentado em comentário nos testes afetados

### REQ-IDs validados
- [ ] Todos os 12 REQ-IDs da matriz têm ao menos um teste verde correspondente

</done_criteria>

<next_phase_handoff>

## O que estará pronto ao fim desta fase

- Modelo de dados completo para VEM: enum `PapelEquipe`, models `Equipe` + `EquipeUsuario`, relação `User::equipes()`
- 11 equipes VEM persistidas e disponíveis em qualquer ambiente após `migrate:fresh --seed`
- Suíte de testes cobrindo migrations, models, factory e seeder

## O que a Phase 2 consumirá (Autorização escopada)

- `PapelEquipe` enum — base para todas as abilities no `EquipePolicy`
- `User::equipes()` — fornece a lista de equipes do usuário para `Gate` checks
- `Equipe::usuarios()` com scopes `coordenadores()`/`membros()` — usados por `EquipePolicy::view`, `update`, `manage`
- Método futuro `User::isCoordenadorDe(Equipe $e): bool` será implementado em Phase 2 usando `equipes()`+`wherePivot('papel', ...)`

## Artefatos que a Phase 3 (CRUD Flux/Volt) consumirá

- `Equipe::scopeAtivas()`, `Equipe::scopeParaMovimento()` — para listagem filtrada
- `Equipe::factory()` — para preview data em testes de Volt SFCs
- Mutator de slug — garante que `FormRequest` não precisa calcular slug manualmente

## Artefatos que a Phase 4 (atribuição) consumirá

- `EquipeUsuario` com audit automático — Volt de atribuição só precisa setar `idt_equipe`, `user_id`, `papel`
- `PapelEquipe::requerSexo()` — usado para filtro "coordenador homem/mulher" no Volt
- Unique constraint — backend enforce anti-duplicata; UI reforça com validação pré-submit

## Bloqueadores explicitados

- Phase 2 NÃO pode começar até Task 9 (dual-driver) estar aprovada, pois policies dependem da forma final do pivot
- Phase 5 (hardening) depende de toda esta fase + suíte legada continuar verde

</next_phase_handoff>

<success_criteria>

- 8 arquivos criados + 2 modificados conforme `files_modified`
- 8 arquivos de teste criados com TDD (RED → GREEN → REFACTOR) — um commit `test(...)` + um `feat(...)` por task quando aplicável; tarefas pequenas podem combinar teste+impl em commit único com a convenção do projeto
- Todos os 12 REQ-IDs da verification matrix validados por testes automatizados
- Checkpoint T9 (MySQL) aprovado pelo usuário
- Suíte Pest completa verde (`./vendor/bin/pest`)
- `php artisan migrate:fresh --seed` produz 11 equipes VEM em SQLite e MySQL (meta da fase)
- Nenhum arquivo fora de `files_modified` é alterado
- Nenhum teste existente é quebrado (regressão zero)
- `./vendor/bin/pint --test` verde

</success_criteria>

<output>
Ao concluir, criar `.planning/phases/01-fundacao-dados-modelos/01-01-SUMMARY.md` seguindo o template de summary com:
- Lista exata de arquivos criados/modificados
- Decisões travadas (D-01 a D-09) e sua manifestação no código
- REQ-IDs cobertos + referência aos testes
- Patterns estabelecidos para fases seguintes (primeiro enum, pivot extends Model, audit usr_*/dat_*)
- Cost real vs estimado (para afinar agente nas próximas fases)
- Riscos materializados e como foram resolvidos
- Próximo passo: `/gsd-plan-phase 2`
</output>
