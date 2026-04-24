# STATE.md — pnsl-ntm

> Estado atual do projeto GSD. Atualizado automaticamente pelos comandos `/gsd-*`.

## Current Milestone

**v1.1 — Gestão de Equipes VEM (Fundação)**
Iniciado em: 2026-04-21

## Scope

- RBAC escopado por equipe (`equipe_usuario` pivot + Gate/Policy nativos)
- Estrutura de 11 equipes VEM (CRUD + seed)
- Atribuição de membros/coordenadores às equipes

## Out of Milestone Scope (deferido para v1.2+)

- Espaços de equipe (comunicados, eventos internos) — v1.2
- Gamificação Score 0-100 (rubrica thinkworklab) — v1.3
- Módulo Vendinha — v1.4
- Análise de IA para Vendinha — v1.5

## Phases

1. **Phase 1 — Fundação de dados e modelos de equipe**: migrations `equipes` + `equipe_usuario`, models `Equipe`/`EquipeUsuario`, enum de papéis e seeder das 11 equipes VEM
2. **Phase 2 — Autorização escopada (Gate/Policy nativos)**: `EquipePolicy`, helpers no `User`, wiring em `AuthServiceProvider`, coexistência com middleware `manager`
3. **Phase 3 — CRUD de equipes (Flux/Volt)**: FormRequests, Volt SFCs (index/create/edit), rotas, arquivamento via soft-delete
4. **Phase 4 — Atribuição de membros e coordenadores**: Volt `equipes.atribuir` com filtros VEM+sexo, regras H+M, auditoria, listagem no perfil
5. **Phase 5 — Hardening, regressão e qualidade**: coverage Pest ≥80%, regressão da suite legada, preservação de cascata `User↔Pessoa` e `GamificacaoObserver`, `pint --test` verde

## Current Phase

**Phase 2 — Autorização escopada (Gate/Policy nativos): COMPLETA**

3/3 tasks completas. EquipePolicy, AuthServiceProvider, User helpers — todos implementados e testados.

Próximo: Phase 3 — CRUD de equipes (Flux/Volt)

## Completed Phases

- **Phase 1 — Fundação de dados e modelos de equipe**: Tasks 1-8 completas (T9 checkpoint:human-verify pendente MySQL); summary em `01-01-SUMMARY.md`
- **Phase 2 — Autorização escopada**: 3/3 tasks completas; summary em `02-01-SUMMARY.md`

## Key Artifacts

- `.planning/PROJECT.md` — visão geral + requisitos ativos
- `.planning/REQUIREMENTS.md` — 43 REQ-IDs do milestone v1.1 com traceability completa
- `.planning/ROADMAP.md` — 5 fases, 43/43 requisitos mapeados
- `.planning/codebase/` — snapshots de arquitetura/stack/integrações
- `.planning/phases/01-fundacao-dados-modelos/01-01-SUMMARY.md` — summary das tasks 1-8
- `.planning/phases/02-autorizacao-escopada/02-01-SUMMARY.md` — summary Phase 2

## Decisions Made

- D-01: `EquipeUsuario extends Model + AsPivot` (não `Pivot`) — SoftDeletes exige Model no Laravel 12
- D-02: Novo padrão de auditoria `usr_*/dat_*` para equipe_usuario; usu_* legado (Ficha) não alterado
- D-03: PK `idt_X` para novas tabelas; `user_id` permanece default (FK para users.id)
- D-04: Tabela plural `equipes` (exigência REQUIREMENTS EQUIPE-01)
- D-05: Enum values snake_case (`coord_geral`), labels pt_BR
- D-06: Unique `(user_id, idt_equipe)` a nível de DB; restauração pós soft-delete via app (Phase 4)
- D-07: `createMovimentos()` não reusado — testes chamam `seed(EquipeVEMSeeder::class)` explicitamente
- D-09: `withTimestamps()` NÃO usado em User::equipes() — pivot usa `dat_*` manual via booted()
- AsPivot trait resolve `fromRawAttributes`/`setPivotKeys` para `using()` em BelongsToMany
- D-10: `before()` retorna null (não false) para não-coord-geral — false bloquearia admin flat também (RBAC-10)
- D-11: `update()` retorna Response (não bool) — mensagem pt_BR no HTTP 403
- D-12: `assignMembers()` retorna false explícito — documentação de intenção + testabilidade unitária
- D-13: Import de `Equipe` removido de User.php por pint — mesmo namespace App\Models, redundante

## Blockers

- Task 9 Phase 1 (checkpoint:human-verify): Verificação dual-driver MySQL pendente
- TrabalhadorTest (4 falhas pré-existentes): `FOREIGN KEY constraint failed` em tabela `evento` — não relacionado ao milestone atual
- GD extension não instalada no XAMPP dev: impede `migrate:fresh --seed` completo (EventoSeeder usa GD)

## Progress

Phase: 2 of 5 (Autorização escopada — COMPLETA)
Plan: 1 of 1 (3/3 tasks complete)
Status: Phase 2 complete — pronto para Phase 3 CRUD
Progress: [██░░░░░░░░] 35% (Phase 1 tasks 1-8 + Phase 2 completas)

---
*Last updated: 2026-04-24 — Phase 2 completa: EquipePolicy, AuthServiceProvider, User helpers implementados; 27 testes Equipe verdes*
