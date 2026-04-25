# Phase 5 Plan - Hardening, regressao e qualidade

**Criado:** 2026-04-25
**Status:** DONE
**GSD:** planejado manualmente porque `gsd-sdk` local falha com modulo ausente `@gsd-build/sdk/dist/cli.js`.

## Goal

Fechar o marco v1.1 com regressao explicita dos fluxos legados sensiveis, suite completa verde, Pint sem diff e evidencias de cobertura/limites locais.

## Requisitos

- MIG-05
- MIG-06
- MIG-07
- TEST-01
- TEST-08

## Decisoes

- Phase 5 fica empilhada sobre a Phase 4 porque valida o conjunto completo do marco v1.1.
- A suite completa ja esta verde apos Phase 4; Phase 5 deve preservar esse estado enquanto adiciona regressao explicita.
- Onboarding sera coberto por `tests/Feature/Onboarding/CascadeUserPessoaTest.php`, separando as garantias de cascata User/Pessoa dos testes amplos de `PessoaTest`.
- `GamificacaoObserver` sera coberto por `tests/Feature/Gamificacao/GamificacaoObserverRegressaoTest.php`, verificando incremento e decremento via eventos Eloquent reais.
- Corrigir rollback de `2026_02_25_183007_create_gamificacao_table_and_add_pts_to_pessoa.php`: o `down()` deve remover `pessoa.qtd_pontos_total` alem de dropar `gamificacao`.
- Coverage sera tentado com Pest, mas o ambiente local nao possui Xdebug/PCOV instalado; se o driver estiver indisponivel, documentar o bloqueio em `05-VALIDATION.md` sem mascarar a limitacao.

## Entregaveis

- `tests/Feature/Onboarding/CascadeUserPessoaTest.php`
- `tests/Feature/Gamificacao/GamificacaoObserverRegressaoTest.php`
- `database/migrations/2026_02_25_183007_create_gamificacao_table_and_add_pts_to_pessoa.php`
- `.planning/phases/05-hardening-regressao-qualidade/05-VALIDATION.md`

## Gates

- `C:/xampp/php/php.exe vendor/bin/pint --test`
- `C:/xampp/php/php.exe vendor/bin/pest tests/Feature/Onboarding/CascadeUserPessoaTest.php tests/Feature/Gamificacao/GamificacaoObserverRegressaoTest.php --stop-on-failure`
- `C:/xampp/php/php.exe vendor/bin/pest tests/Feature/Equipes tests/Feature/Autorizacao tests/Unit/Policies --stop-on-failure`
- `C:/xampp/php/php.exe vendor/bin/pest`
- `C:/xampp/php/php.exe vendor/bin/pest --coverage --min=80`
