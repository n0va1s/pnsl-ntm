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

**Phase 1 — Fundação de dados e modelos de equipe (not started)**

Próximo passo: rodar `/gsd-plan-phase 1` para decompor a fase em planos executáveis.

## Completed Phases

_(nenhuma)_

## Key Artifacts

- `.planning/PROJECT.md` — visão geral + requisitos ativos
- `.planning/REQUIREMENTS.md` — 43 REQ-IDs do milestone v1.1 com traceability completa
- `.planning/ROADMAP.md` — 5 fases, 43/43 requisitos mapeados
- `.planning/codebase/` — snapshots de arquitetura/stack/integrações

## Progress

Phase: 1 of 5 (Fundação de dados e modelos de equipe)
Status: Ready to plan
Progress: [░░░░░░░░░░] 0%

---
*Last updated: 2026-04-21 após geração do roadmap (5 fases, coverage 43/43)*
