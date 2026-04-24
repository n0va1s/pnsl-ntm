# 03-04 SUMMARY — Testes Feature de equipes

**Concluido:** 2026-04-24
**Status:** DONE

## Arquivos criados

- `tests/Feature/Equipes/EquipeCrudTest.php`
- `tests/Feature/Equipes/EquipeArquivamentoTest.php`

## Cobertura entregue

- `EquipeCrudTest.php`: 7 testes cobrindo index filtrado por movimento, 403 em create/edit, criação via Volt, slug duplicado, edição e toggle `ind_ativa`.
- `EquipeArquivamentoTest.php`: 2 testes cobrindo soft-delete com pivot preservada e restauracao de equipe arquivada.
- `withoutVite()` foi aplicado nos `beforeEach` para evitar falha por manifest ausente em ambiente de teste.

## Verificacao

- `C:/xampp/php/php.exe -l tests/Feature/Equipes/EquipeCrudTest.php` passou.
- `C:/xampp/php/php.exe -l tests/Feature/Equipes/EquipeArquivamentoTest.php` passou.
- `C:/xampp/php/php.exe vendor/bin/pest tests/Feature/Equipes/EquipeCrudTest.php tests/Feature/Equipes/EquipeArquivamentoTest.php` passou com 9 testes.
- `C:/xampp/php/php.exe vendor/bin/pest tests/Feature/Equipes/` passou com 23 testes.
