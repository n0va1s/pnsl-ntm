---
phase: 02-autorizacao-escopada
plan: 01
subsystem: auth
tags: [laravel-policy, gate, rbac, pivot, eloquent, livewire]

# Dependency graph
requires:
  - phase: 01-fundacao-dados-modelos
    provides: "equipe_usuario pivot table, Equipe/EquipeUsuario models, PapelEquipe enum with snake_case values, User::equipes() BelongsToMany with soft-delete filter"
provides:
  - "EquipePolicy com before() + 4 habilidades (viewAny, view, update, assignMembers)"
  - "AuthServiceProvider com mapa explicito Equipe::class => EquipePolicy::class"
  - "User::isCoordenadorGeral(), isCoordenadorDe(Equipe), isMembroDe(Equipe) helpers booleanos"
affects:
  - 03-crud-equipes
  - 04-atribuicao-membros
  - 05-hardening-qualidade

# Tech tracking
tech-stack:
  added: []
  patterns:
    - "Laravel Policy com before() retornando ?bool (null para pass-through, true para super-admin)"
    - "AuthServiceProvider estendes Illuminate\\Foundation\\Support\\Providers\\AuthServiceProvider com $policies explicito"
    - "Helpers booleanos em User usam ->exists() (O(1)) com wherePivot('.value') nunca enum object"

key-files:
  created:
    - app/Policies/EquipePolicy.php
    - app/Providers/AuthServiceProvider.php
  modified:
    - bootstrap/providers.php
    - app/Models/User.php

key-decisions:
  - "before() retorna null (nao false) para nao-coord-geral — false bloquearia admin flat também"
  - "update() retorna Response (nao bool) para mensagem pt_BR no 403"
  - "assignMembers() retorna false — before() intercepta coord-geral antes de chegar aqui"
  - "Equipe sem import explicito em User.php: mesmo namespace App\\Models, pint remove como redundante"

patterns-established:
  - "Policy before(): retornar null para pass-through, true para super-admin global"
  - "Helpers de policy: sempre ->exists() + PapelEquipe::X->value, nunca ->get() ou enum object"
  - "AuthServiceProvider separado do AppServiceProvider — GamificacaoObserver permanece isolado"

requirements-completed: [RBAC-07, RBAC-08, RBAC-09, RBAC-10, MIG-01, MIG-02, MIG-03]

# Metrics
duration: 16min
completed: 2026-04-24
---

# Phase 2 Plan 01: Autorizacao Escopada — Camada de Policy Summary

**EquipePolicy com 4 habilidades + before() global para coord-geral, AuthServiceProvider com mapa explicito, e 3 helpers booleanos O(1) em User consultando pivot equipe_usuario**

## Performance

- **Duration:** 16 min
- **Started:** 2026-04-24T03:23:55Z
- **Completed:** 2026-04-24T03:39:55Z
- **Tasks:** 3
- **Files modified:** 4

## Accomplishments

- EquipePolicy com before() retornando ?bool: true para coord-geral, null para todos os outros (nunca false)
- AuthServiceProvider com $policies = [Equipe::class => EquipePolicy::class] — registro explicito com precedencia sobre auto-discovery
- User::isCoordenadorGeral(), isCoordenadorDe(Equipe), isMembroDe(Equipe) com ->exists() e PapelEquipe::X->value
- 27 testes da Phase 1 (Equipe model suite) continuam verdes apos mudancas
- OnlyManagerMiddleware e users.role intactos; AppServiceProvider nao tocado

## Task Commits

Cada task foi commitada atomicamente:

1. **Task 1: Criar EquipePolicy com before() e 4 habilidades** - `ab57e5b` (feat)
2. **Task 2: Criar AuthServiceProvider e registrar em bootstrap/providers.php** - `92330c1` (feat)
3. **Task 3: Adicionar helpers isCoordenadorGeral/isCoordenadorDe/isMembroDe no User** - `495c6c1` (feat)

## Files Created/Modified

- `app/Policies/EquipePolicy.php` - Policy com 5 metodos: before(?bool), viewAny(bool), view(bool), update(Response), assignMembers(bool)
- `app/Providers/AuthServiceProvider.php` - Provider com $policies mapeando Equipe => EquipePolicy
- `bootstrap/providers.php` - Adicao de AuthServiceProvider::class como terceiro provider
- `app/Models/User.php` - Adicao dos 3 helpers booleanos e import PapelEquipe; Equipe removida por pint (mesmo namespace)

## Decisions Made

- **before() retorna null, nao false:** Retornar false bloquearia coord-geral de acessar tudo — viola RBAC-10. Null faz pass-through para o metodo especifico.
- **update() retorna Response, nao bool:** Permite mensagem de erro em pt_BR ("Apenas coordenadores da equipe podem edita-la.") no HTTP 403.
- **assignMembers() retorna false explicito:** Documentacao de intencao + testabilidade unitaria. before() garante que coord-geral nunca chega aqui.
- **Sem import de Equipe em User.php:** PHP resolve via namespace App\Models sem import quando User esta no mesmo namespace. Pint remove automaticamente como no_unused_imports.

## Deviations from Plan

### Auto-fixed Issues

**1. [Rule 1 - Bug] Pint corrigiu formatacao PSR-12 em User.php apos insercao manual**
- **Found during:** Task 3 (User helpers)
- **Issue:** Insercao manual dos helpers gerou violacoes PSR-12: class_attributes_separation, function_declaration, unary_operator_spaces, no_unused_imports, not_operator_with_successor_space
- **Fix:** Executado `./vendor/bin/pint app/Models/User.php` para autocorrecao; uso de App\Models\Equipe removido como redundante (mesmo namespace)
- **Files modified:** app/Models/User.php
- **Verification:** pint --test retornou pass; 5 UserEquipes tests continuam verdes; php -l sem erros de sintaxe
- **Committed in:** 495c6c1 (Task 3 commit)

---

**Total deviations:** 1 auto-fixed (1 formatacao/linting)
**Impact on plan:** Auto-fix necessario para conformidade PSR-12. Sem mudanca de comportamento ou escopo.

## Issues Encountered

- Testes TrabalhadorTest (4 falhas pre-existentes): FOREIGN KEY constraint em tabela `evento` — nao relacionado a este plano. Bloqueador pre-existente documentado em STATE.md.
- SQLite "database is locked" em execucao paralela de testes — artefato de concorrencia, resolvido executando suites em isolamento.

## Next Phase Readiness

- Contrato de autorizacao pronto: authorize() e can() podem ser usados em Volt SFCs da Phase 3
- Gate::getPolicyFor(Equipe::class) retorna EquipePolicy (AuthServiceProvider registrado)
- Todos os helpers de conveniencia disponiveis para uso imediato nas policies e controllers
- Phase 3 (CRUD de equipes) pode comecar sem blockers deste plano

## Self-Check

- [x] app/Policies/EquipePolicy.php existe com 5 metodos corretos
- [x] app/Providers/AuthServiceProvider.php existe com $policies
- [x] bootstrap/providers.php contem AuthServiceProvider::class
- [x] app/Models/User.php contem os 3 helpers com ->exists() e .value
- [x] Commits ab57e5b, 92330c1, 495c6c1 verificados em git log
- [x] 27 testes Phase 1 (Equipe model suite) verdes
- [x] pint --test passa em todos os 3 arquivos novos/modificados
- [x] Nenhuma migration criada; users.role intacto; OnlyManagerMiddleware nao tocado

## Self-Check: PASSED

---
*Phase: 02-autorizacao-escopada*
*Completed: 2026-04-24*
