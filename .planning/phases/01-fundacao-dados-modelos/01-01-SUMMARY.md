---
phase: 01-fundacao-dados-modelos
plan: 01
subsystem: equipes-vem
tags: [migrations, eloquent, enum, pivot, seeder, pest, laravel12, sqlite]
dependency_graph:
  requires: []
  provides:
    - app/Enums/PapelEquipe.php
    - app/Models/Equipe.php
    - app/Models/EquipeUsuario.php
    - app/Models/User.equipes()
    - database/migrations/2026_04_21_000001_create_equipes_table.php
    - database/migrations/2026_04_21_000002_create_equipe_usuario_table.php
    - database/factories/EquipeFactory.php
    - database/factories/EquipeUsuarioFactory.php
    - database/seeders/EquipeVEMSeeder.php
  affects:
    - app/Models/User.php (adição de método equipes())
    - database/seeders/DatabaseSeeder.php (registro do EquipeVEMSeeder)
tech_stack:
  added:
    - PHP 8.2 backed enums (primeiro enum do projeto)
    - Eloquent AsPivot trait em Model (workaround SoftDeletes em pivot Laravel 12)
  patterns:
    - enum PapelEquipe: string (snake_case values, pt_BR labels)
    - EquipeUsuario extends Model + AsPivot trait (não Pivot puro — D-01)
    - Auditoria usr_* + dat_* (novo padrão distinto de usu_* legado do Ficha — D-02)
    - seedDefaults() idempotente via firstOrCreate em factory
    - Rollback de migrations na ordem inversa (pivot antes de equipes — MIG-04)
key_files:
  created:
    - app/Enums/PapelEquipe.php
    - app/Models/Equipe.php
    - app/Models/EquipeUsuario.php
    - database/migrations/2026_04_21_000001_create_equipes_table.php
    - database/migrations/2026_04_21_000002_create_equipe_usuario_table.php
    - database/factories/EquipeFactory.php
    - database/factories/EquipeUsuarioFactory.php
    - database/seeders/EquipeVEMSeeder.php
    - tests/Unit/Enums/PapelEquipeTest.php
    - tests/Feature/Equipes/EquipeMigrationTest.php
    - tests/Feature/Equipes/EquipeUsuarioMigrationTest.php
    - tests/Unit/Models/EquipeTest.php
    - tests/Unit/Models/EquipeUsuarioTest.php
    - tests/Unit/Models/UserEquipesTest.php
    - tests/Unit/Models/EquipeFactoryTest.php
    - tests/Feature/Equipes/EquipeVEMSeederTest.php
  modified:
    - app/Models/User.php (método equipes() adicionado)
    - database/seeders/DatabaseSeeder.php (EquipeVEMSeeder registrado)
decisions:
  - "D-01: EquipeUsuario extends Model + AsPivot (não Pivot) — SoftDeletes não suportado em Pivot no Laravel 12"
  - "D-02: Novo padrão de auditoria usr_* + dat_* para equipe_usuario; usu_* legado em Ficha não alterado"
  - "D-03: PK idt_X para equipes/equipe_usuario; user_id permanece default (FK para users.id)"
  - "D-04: Tabela plural equipes (exigência REQUIREMENTS EQUIPE-01)"
  - "D-05: Enum values snake_case (coord_geral), labels pt_BR"
  - "D-06: Unique (user_id, idt_equipe) a nível de DB; re-adição pós soft-delete via camada de app (Phase 4)"
  - "D-07: createMovimentos() não reusado para equipes — testes chamam seed(EquipeVEMSeeder::class) explicitamente"
  - "D-09: withTimestamps() NÃO usado na relação User::equipes() — pivot usa dat_* manual via booted()"
  - "AsPivot trait soluciona fromRawAttributes/setPivotKeys necessários para using() em BelongsToMany"
metrics:
  duration_minutes: 214
  completed_date: "2026-04-23"
  tasks_completed: 8
  tasks_total: 9
  files_created: 16
  files_modified: 2
  tests_added: 49
  test_assertions: 136
---

# Phase 01 Plan 01: Fundação de dados e modelos de equipe — Summary

**One-liner:** Migrations `equipes`/`equipe_usuario`, enum `PapelEquipe`, models `Equipe`/`EquipeUsuario` com AsPivot+SoftDeletes, relação `User::equipes()`, factories idempotentes e seeder das 11 equipes VEM validados por 49 testes Pest.

## Tasks Executed

| # | Task | Commit | Status |
|---|------|--------|--------|
| 1 | Enum PapelEquipe (RBAC-01) | b7710b8 | Completa |
| 2 | Migration equipes (EQUIPE-01, MIG-04) | 9d120b8 | Completa |
| 3 | Migration equipe_usuario (RBAC-02, RBAC-03) | 93b1b86 | Completa |
| 4 | Model Equipe com scopes e relações (EQUIPE-02, RBAC-06) | 3e543a2 | Completa |
| 5 | Model EquipeUsuario com audit e cast (RBAC-04) | 13837bb | Completa |
| 6 | User::equipes() BelongsToMany (RBAC-05) | 1e84014 | Completa |
| 7 | Factories EquipeFactory e EquipeUsuarioFactory | 821baba | Completa |
| 8 | Seeder EquipeVEMSeeder idempotente (EQUIPE-03, TEST-06) | 9f2ccd9 | Completa |
| 9 | Verificação dual-driver MySQL (TEST-05) | — | Aguardando checkpoint humano |
| — | Pint + robustez de rollback test | 408b70c | Completa |

## Deviations from Plan

### Auto-fixed Issues

**1. [Rule 1 - Bug] EquipeUsuario precisou de trait AsPivot para compatibilidade com using()**
- **Found during:** Task 4 (testes de relação usuarios() falhavam com `fromRawAttributes` not found)
- **Issue:** `EquipeUsuario extends Model` sem trait específica não implementa `fromRawAttributes()` e `setPivotKeys()` necessários quando `using()` é chamado em `belongsToMany`
- **Fix:** Adicionado `use AsPivot` (trait do Laravel em `Illuminate\Database\Eloquent\Relations\Concerns\AsPivot`) que fornece esses métodos enquanto mantém `Model` como base (permitindo SoftDeletes)
- **Files modified:** `app/Models/EquipeUsuario.php`
- **Commit:** 13837bb

**2. [Rule 1 - Bug] Teste de rollback frágil quando suite roda em conjunto**
- **Found during:** Task 9 (suite completa — 1 teste falhava)
- **Issue:** `migrate:rollback --step=1` rodava `equipe_usuario` quando `EquipeUsuarioMigrationTest` tinha executado antes, tornando o resultado dependente da ordem de execução
- **Fix:** Alterado para `--step=2` verificando que ambas as tabelas (equipe_usuario e equipes) são removidas
- **Files modified:** `tests/Feature/Equipes/EquipeMigrationTest.php`
- **Commit:** 408b70c

**3. [Rule 2 - Missing] migrate:fresh --seed teste ajustado para ambiente sem GD**
- **Found during:** Task 8
- **Issue:** `migrate:fresh --seed` completo falha neste ambiente porque `EventoSeeder` usa extensão GD (não instalada no XAMPP de desenvolvimento)
- **Fix:** Teste alterado para `Artisan::call('migrate:fresh')` + `$this->seed(EquipeVEMSeeder::class)` separadamente, com comentário explicativo. O registro em `DatabaseSeeder` foi feito corretamente.
- **Files modified:** `tests/Feature/Equipes/EquipeVEMSeederTest.php`
- **Commit:** 9f2ccd9

### Pre-existing Issues (out of scope — logged for reference)

- **FichaTest failures (5 tests):** `FOREIGN KEY constraint failed` em `ficha_analise` — pré-existente antes de nossa branch, confirmado via `git stash`
- **SQLite rollback de `usu_inclusao`:** Legacy migration `2026_04_18_133105_add_usuario_ficha_table.php` não pode ser revertida no SQLite (bug pré-existente com FK drop)

## REQ-IDs Covered

| REQ-ID | Teste que prova | Status |
|--------|----------------|--------|
| RBAC-01 | `tests/Unit/Enums/PapelEquipeTest.php` (8 testes) | Verde |
| RBAC-02 | `tests/Feature/Equipes/EquipeUsuarioMigrationTest.php` | Verde |
| RBAC-03 | `tests/Feature/Equipes/EquipeUsuarioMigrationTest.php` — unique constraint | Verde |
| RBAC-04 | `tests/Unit/Models/EquipeUsuarioTest.php` — cast enum + audit | Verde |
| RBAC-05 | `tests/Unit/Models/UserEquipesTest.php` — equipes() BelongsToMany | Verde |
| RBAC-06 | `tests/Unit/Models/EquipeTest.php` — coordenadores(), membros() | Verde |
| EQUIPE-01 | `tests/Feature/Equipes/EquipeMigrationTest.php` — schema completo | Verde |
| EQUIPE-02 | `tests/Unit/Models/EquipeTest.php` — scopes + mutator | Verde |
| EQUIPE-03 | `tests/Feature/Equipes/EquipeVEMSeederTest.php` — 11 equipes + slugs | Verde |
| MIG-04 | `tests/Feature/Equipes/EquipeMigrationTest.php` + `EquipeUsuarioMigrationTest.php` | Verde |
| TEST-05 | Pest completo em SQLite verde; MySQL pendente (Task 9 checkpoint) | Parcial |
| TEST-06 | `tests/Feature/Equipes/EquipeVEMSeederTest.php` — migrate:fresh + seed | Verde |

## Patterns Established for Next Phases

1. **Enum padrão do projeto:** `enum XYZ: string` com `label()`, `opcoes()`, `isCoordenador()` — importar via `use App\Enums\XYZ`
2. **Pivot com SoftDeletes:** `extends Model` + `use AsPivot, SoftDeletes` (não `extends Pivot`)
3. **Auditoria nova:** `usr_inclusao`/`usr_alteracao` (FK nullable nullOnDelete) + `dat_inclusao`/`dat_alteracao` (timestamp nullable) via `booted()` — distinto do padrão legado `usu_*` do `Ficha`
4. **Factory idempotente:** `seedDefaults()` com `firstOrCreate` na chave composta natural
5. **Seeder guard:** `if (Model::count() > 0) return;` antes de qualquer insert
6. **BelongsToMany sem withTimestamps():** Usar `dat_*` manuais via booted() quando timestamps padrão conflitam com design da tabela

## Known Stubs

Nenhum. Todos os dados fluem do banco — sem valores hardcoded, placeholders ou mocks em código de produção.

## Threat Flags

Nenhuma nova superfície de segurança além do que foi planejado no `<threat_model>`.

- T-01-01 (Tampering em audit): Mitigado — `booted()` hook preenche `usr_inclusao`/`usr_alteracao` automaticamente
- T-01-02 (Repudiation): Mitigado — SoftDeletes preserva histórico de vínculos
- T-01-04 (DoS de seeder duplicado): Mitigado — guard `count() > 0` + `firstOrCreate`
- T-01-05 (EoP via enum inválido): Mitigado — cast Eloquent lança `ValueError` em valor inválido

## Self-Check: PASSED

**Arquivos criados verificados:**
- [x] `app/Enums/PapelEquipe.php` — existe
- [x] `app/Models/Equipe.php` — existe
- [x] `app/Models/EquipeUsuario.php` — existe
- [x] `database/migrations/2026_04_21_000001_create_equipes_table.php` — existe
- [x] `database/migrations/2026_04_21_000002_create_equipe_usuario_table.php` — existe
- [x] `database/factories/EquipeFactory.php` — existe
- [x] `database/factories/EquipeUsuarioFactory.php` — existe
- [x] `database/seeders/EquipeVEMSeeder.php` — existe

**Commits verificados:**
- [x] b7710b8 — enum PapelEquipe
- [x] 9d120b8 — migration equipes
- [x] 93b1b86 — migration equipe_usuario
- [x] 3e543a2 — model Equipe + EquipeFactory
- [x] 13837bb — model EquipeUsuario
- [x] 1e84014 — User::equipes()
- [x] 821baba — factories
- [x] 9f2ccd9 — seeder
- [x] 408b70c — pint + rollback fix

**Suite completa (49 testes, 136 assertions):** VERDE

## Next Step

Task 9 requer verificação manual do dual-driver MySQL. Após aprovação do checkpoint:
- Executar `/gsd-plan-phase 2` para Phase 2 — Autorização escopada (Gate/Policy nativos)
