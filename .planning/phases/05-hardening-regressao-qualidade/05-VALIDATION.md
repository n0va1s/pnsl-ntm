# Phase 5 Validation - Hardening, regressao e qualidade

**Criado:** 2026-04-25
**Status:** PASS com bloqueio ambiental em coverage

## Matriz de validacao

| Requisito | Verificacao | Evidencia esperada |
|-----------|-------------|--------------------|
| MIG-05 | Feature test de onboarding | Criar `User` cria/associa `Pessoa` sem loop; criar `Pessoa` cria `User`, vincula `idt_usuario`, usa senha `YYYYMMDD` e envia `BoasVindasMail` |
| MIG-06 | Feature test do observer | Criar `Gamificacao` incrementa `pessoa.qtd_pontos_total`; deletar decrementa; `AppServiceProvider::boot()` segue registrando o observer |
| MIG-07 | Suite completa | `C:/xampp/php/php.exe vendor/bin/pest` verde |
| TEST-01 | Coverage Pest | `vendor/bin/pest --coverage --min=80` executado ou bloqueio de driver documentado |
| TEST-08 | Pint | `C:/xampp/php/php.exe vendor/bin/pint --test` sem diff |

## Gates planejados

```bash
C:/xampp/php/php.exe vendor/bin/pint --test
C:/xampp/php/php.exe vendor/bin/pest tests/Feature/Onboarding/CascadeUserPessoaTest.php tests/Feature/Gamificacao/GamificacaoObserverRegressaoTest.php --stop-on-failure
C:/xampp/php/php.exe vendor/bin/pest tests/Feature/Equipes tests/Feature/Autorizacao tests/Unit/Policies --stop-on-failure
C:/xampp/php/php.exe vendor/bin/pest
C:/xampp/php/php.exe vendor/bin/pest --coverage --min=80
```

## Evidencia executada

- `C:/xampp/php/php.exe vendor/bin/pest tests/Feature/Onboarding/CascadeUserPessoaTest.php tests/Feature/Gamificacao/GamificacaoObserverRegressaoTest.php --stop-on-failure` PASS: 3 testes, 13 assertions
- `C:/xampp/php/php.exe vendor/bin/pest tests/Feature/Equipes tests/Feature/Autorizacao tests/Unit/Policies --stop-on-failure` PASS: 67 testes, 145 assertions
- `C:/xampp/php/php.exe vendor/bin/pint --test` falhou inicialmente em arquivos versionados com estilo antigo e no artefato local nao versionado `composer-setup.php`.
- Correcoes aplicadas:
  - Formatar arquivos versionados reportados pelo Pint.
  - Adicionar `pint.json` com `notPath: ["composer-setup.php"]`, pois `composer-setup.php` e artefato local nao versionado e nao deve participar do gate.
  - Corrigir `down()` de `2026_02_25_183007_create_gamificacao_table_and_add_pts_to_pessoa.php` para remover `pessoa.qtd_pontos_total`.
- `C:/xampp/php/php.exe vendor/bin/pint --test` PASS
- `C:/xampp/php/php.exe vendor/bin/pest tests/Feature/Onboarding/CascadeUserPessoaTest.php tests/Feature/Gamificacao/GamificacaoObserverRegressaoTest.php tests/Feature/Equipes tests/Feature/Autorizacao tests/Unit/Policies --stop-on-failure` PASS: 70 testes, 158 assertions
- `C:/xampp/php/php.exe vendor/bin/pest` PASS: 321 testes, 803 assertions
- `C:/xampp/php/php.exe vendor/bin/pest --coverage --min=80` BLOCKED: `No code coverage driver is available.`

## Resultado GSD/Nyquist

- MIG-05: PASS por `tests/Feature/Onboarding/CascadeUserPessoaTest.php`.
- MIG-06: PASS por `tests/Feature/Gamificacao/GamificacaoObserverRegressaoTest.php` e registro intacto em `App\Providers\AppServiceProvider::boot()`.
- MIG-07: PASS por suite completa Pest verde.
- TEST-08: PASS por Pint global verde.
- TEST-01: BLOCKED no ambiente local por ausencia de Xdebug/PCOV. O comando foi executado e falhou antes dos testes por falta de driver, nao por cobertura insuficiente. Para concluir numericamente, instalar/ativar Xdebug ou PCOV e rerodar `C:/xampp/php/php.exe vendor/bin/pest --coverage --min=80`.
