---
phase: 02-autorizacao-escopada
plan: 02
subsystem: testing
tags: [pest, laravel-policy, gate, rbac, pivot, livewire, withoutVite]

# Dependency graph
requires:
  - phase: 02-autorizacao-escopada
    plan: 01
    provides: "EquipePolicy com before()+4 habilidades, AuthServiceProvider, User helpers isCoordenadorGeral/isCoordenadorDe/isMembroDe"

provides:
  - "37 testes automatizados cobrindo EquipePolicy (Unit + Feature via Gate)"
  - "Smoke test de regressão OnlyManagerMiddleware (TEST-07)"
  - "Contrato verificável TEST-02, TEST-03, TEST-07 para Phase 3"

affects:
  - 03-crud-equipes
  - 05-hardening-qualidade

# Tech tracking
tech-stack:
  added: []
  patterns:
    - "Unit tests de Policy: instância direta new EquipePolicy() para métodos bool/Response, Gate ($user->can()) para before()"
    - "Feature smoke tests: withoutVite() nos casos 200 quando Vite manifest ausente em test env"
    - "Pest describe() hierárquico por habilidade: describe('ability') > it('papel → resultado')"
    - "Testes ortogonalidade RBAC: mesmo usuário testado via policy Gate E via middleware HTTP"

key-files:
  created:
    - tests/Unit/Policies/EquipePolicyTest.php
    - tests/Feature/Autorizacao/ConfiguracoesLegacyGuardTest.php
    - tests/Feature/Autorizacao/EquipePolicyHttpTest.php
  modified: []

key-decisions:
  - "withoutVite() adicionado nos testes 200 do ConfiguracoesLegacyGuardTest — Vite manifest ausente é condição pré-existente do ambiente de testes local, não um bug deste plano"
  - "Pint converteu FQCNs inline para use statements no topo dos Feature tests (fully_qualified_strict_types + ordered_imports)"
  - "Unit tests chamam policy diretamente para bool/Response; Gate ($user->can()) para validar before() — separação clara entre teste de método e teste de integração Gate"

patterns-established:
  - "Policy test: before() testado via $user->can() (passa por Gate), métodos específicos testados via $policy->method() (direto)"
  - "Vite em testes: $this->withoutVite() antes de actingAs em testes de view que retornam 200"
  - "Ortogonalidade RBAC: mesmo usuário (coord-geral com role=user) testado como autorizado na policy e bloqueado no middleware legacy"

requirements-completed: [TEST-02, TEST-03, TEST-07]

# Metrics
duration: 15min
completed: 2026-04-24
---

# Phase 2 Plan 02: Autorização Escopada — Testes de Policy e Guard Summary

**37 testes Pest cobrindo EquipePolicy (Unit direta + Feature via Gate) e smoke de regressão do OnlyManagerMiddleware, validando TEST-02, TEST-03, TEST-07**

## Performance

- **Duration:** 15 min
- **Started:** 2026-04-24T03:44:52Z
- **Completed:** 2026-04-24T03:59:52Z
- **Tasks:** 2
- **Files modified:** 3 criados

## Accomplishments

- 17 Unit tests diretos da EquipePolicy cobrindo before(), viewAny(), view(), update(), assignMembers() × todos os papéis relevantes (coord-geral, coord-equipe-h/m, membro-equipe, sem vínculo)
- 4 Feature smoke tests do OnlyManagerMiddleware: role=user→403, admin→200, coord→200, coord-geral+role=user→403 (RBAC-10)
- 16 Feature tests da policy via Gate (actingAs + $user->can()) cobrindo todas as 4 habilidades + RBAC-09 (Gate::getPolicyFor) + RBAC-10 (ortogonalidade)
- Nenhum arquivo de produção modificado neste plano
- Suite de Phase 1 (52 testes Unit relevantes) verde sem regressão

## Task Commits

Cada task foi commitada atomicamente:

1. **Task 4: Unit tests EquipePolicy** - `9f5e689` (test)
2. **Task 5: Feature tests Guard + Policy Http** - `155b11f` (test)

**Plan metadata:** (este commit — docs)

## Files Created/Modified

- `tests/Unit/Policies/EquipePolicyTest.php` - 17 Unit tests: describe por habilidade, instância direta + Gate para before()
- `tests/Feature/Autorizacao/ConfiguracoesLegacyGuardTest.php` - 4 smoke tests regressão OnlyManagerMiddleware, withoutVite() nos 200
- `tests/Feature/Autorizacao/EquipePolicyHttpTest.php` - 16 Feature tests: viewAny/view/update/assignMembers via actingAs+can(), RBAC-09, RBAC-10

## Decisions Made

- **withoutVite() nos testes 200:** A Vite manifest está ausente no ambiente de testes local (pre-existing). Adicionar `$this->withoutVite()` é a solução correta do Laravel para isso — não modifica comportamento de autorização, apenas suprime asset loading.
- **Unit tests chamam policy diretamente:** Para métodos que retornam bool/Response, chamada direta é mais simples e legível. Para before(), usa-se $user->can() pois o Gate precisa ser ativado.
- **Pint aplicado após escrita:** FQCNs inline (ex: `\App\Policies\EquipePolicy::class`) convertidos para `use` statements no topo pelo fixer `fully_qualified_strict_types`. Testes continuaram verdes.

## Deviations from Plan

### Auto-fixed Issues

**1. [Rule 3 - Blocking] withoutVite() adicionado nos testes assertStatus(200) do ConfiguracoesLegacyGuardTest**
- **Found during:** Task 5 (ConfiguracoesLegacyGuardTest.php)
- **Issue:** Testes de role=admin→200 e role=coord→200 falhavam com 500 (ViteManifestNotFoundException) pois `public/build/manifest.json` não existe no ambiente de testes local. O mesmo problema existia no analog `tests/Feature/ConfiguracoesTest.php` (teste pré-existente que também falha).
- **Fix:** Adicionado `$this->withoutVite()` antes de `actingAs()` nos dois testes 200. Testes 403 não precisam de Vite pois o middleware aborta antes de renderizar qualquer view.
- **Files modified:** tests/Feature/Autorizacao/ConfiguracoesLegacyGuardTest.php
- **Verification:** `./vendor/bin/pest tests/Feature/Autorizacao/ConfiguracoesLegacyGuardTest.php` → 4 passed
- **Committed in:** 155b11f (Task 5 commit)

**2. [Rule 1 - Formatting] Pint corrigiu FQCNs e imports nos Feature tests**
- **Found during:** Task 5 (após escrita dos Feature tests)
- **Issue:** FQCNs inline (`\App\Policies\EquipePolicy::class`, `\Illuminate\Support\Facades\Gate::getPolicyFor`) e imports desordernados violavam `fully_qualified_strict_types` e `ordered_imports`
- **Fix:** Executado `./vendor/bin/pint` nos 2 Feature test files; FQCNs convertidos para `use` statements no topo
- **Files modified:** tests/Feature/Autorizacao/ConfiguracoesLegacyGuardTest.php, tests/Feature/Autorizacao/EquipePolicyHttpTest.php
- **Verification:** `pint --test` retornou pass; testes continuaram 20 passed
- **Committed in:** 155b11f (Task 5 commit)

---

**Total deviations:** 2 auto-fixed (1 blocking - Vite manifest ausente, 1 formatação/linting)
**Impact on plan:** Auto-fixes necessários para corretude do ambiente de testes. Sem mudança de escopo ou comportamento.

## Issues Encountered

- Vite manifest ausente em ambiente de testes local é problema pré-existente (afeta `tests/Feature/ConfiguracoesTest.php` também). Resolvido com `withoutVite()` nos 2 casos 200. Os testes 403 não são afetados pois o middleware aborta antes de renderizar views.
- 8 testes pré-existentes falham (`EventoServiceTest`, `VoluntarioServiceTest`) por FOREIGN KEY constraint em tabela `evento` — não relacionado a este plano, documentado em STATE.md como bloqueador pré-existente.

## User Setup Required

None - no external service configuration required.

## Next Phase Readiness

- Contrato verificável de autorização: 37 testes provam que EquipePolicy + Gate funcionam corretamente
- Phase 3 (CRUD de equipes via Volt SFCs) pode usar `authorize()` e `can()` com confiança
- Ortogonalidade RBAC-10 documentada e protegida por testes (coord-geral VEM vs users.role flat)
- Gate::getPolicyFor(Equipe::class) verificado em RBAC-09 — sem risco de policy não registrada

## Self-Check

- [x] tests/Unit/Policies/EquipePolicyTest.php existe com 17 testes verdes
- [x] tests/Feature/Autorizacao/ConfiguracoesLegacyGuardTest.php existe com 4 testes verdes
- [x] tests/Feature/Autorizacao/EquipePolicyHttpTest.php existe com 16 testes verdes
- [x] Commits 9f5e689, 155b11f verificados em git log
- [x] 52 testes Unit Phase 1 continuam verdes
- [x] pint --test passa nos 3 arquivos novos
- [x] Nenhum arquivo de produção modificado
- [x] Ameaças T-02-06, T-02-07, T-02-08 cobertas por testes específicos

## Self-Check: PASSED

---
*Phase: 02-autorizacao-escopada*
*Completed: 2026-04-24*
