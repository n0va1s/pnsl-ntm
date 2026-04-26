# Phase 4 Validation - Atribuicao de membros e coordenadores

**Criado:** 2026-04-25
**Status:** PASS

## Matriz de validacao

| Requisito | Verificacao | Evidencia esperada |
|-----------|-------------|--------------------|
| ATRIB-01 | Feature test de rota + guard no Volt | `coord-geral` recebe 200 em `equipes.atribuir`; usuario comum recebe 403 via `mount()` com Gate/Policy |
| ATRIB-02 | Feature test do componente | candidatos H/M respeitam `Pessoa::tip_genero`; usuarios ja vinculados nao aparecem como elegiveis |
| ATRIB-03 | Feature test Volt | `atribuir()` cria linha ativa em `equipe_usuario` com papel escolhido |
| ATRIB-04 | Feature test Volt | `remover()` aplica soft-delete no pivot e remove da listagem ativa |
| ATRIB-05 | Feature test Volt | `alterarPapel()` atualiza `papel` de um vinculo ativo |
| EQUIPE-08 / ATRIB-06 / TEST-04 | Feature test Volt | tentativa de 2o `coord_equipe_h` ou `coord_equipe_m` retorna erro de validacao em pt_BR e nao cria/atualiza |
| ATRIB-07 | Feature/Profile test | perfil exibe lista "Equipes" com nome e label do papel para vinculos ativos |
| ATRIB-08 | Feature test da pivot | create/update/delete preenchem auditoria esperada |

## Gates planejados

```bash
C:/xampp/php/php.exe vendor/bin/pest tests/Feature/Equipes/AtribuirMembroTest.php tests/Feature/Equipes/BloqueioHMRuntimeTest.php tests/Feature/Equipes/AuditoriaPivotTest.php
C:/xampp/php/php.exe vendor/bin/pest tests/Feature/Equipes/
C:/xampp/php/php.exe vendor/bin/pest
```

## Evidencia executada

- `C:/xampp/php/php.exe -l resources/views/livewire/equipes/atribuir.blade.php` PASS
- `C:/xampp/php/php.exe -l resources/views/livewire/settings/profile.blade.php` PASS
- `C:/xampp/php/php.exe -l app/Models/EquipeUsuario.php` PASS
- `C:/xampp/php/php.exe -l routes/web.php` PASS
- `C:/xampp/php/php.exe vendor/bin/pint app/Models/EquipeUsuario.php routes/web.php resources/views/livewire/equipes/atribuir.blade.php resources/views/livewire/settings/profile.blade.php tests/Feature/Equipes/AtribuirMembroTest.php tests/Feature/Equipes/BloqueioHMRuntimeTest.php tests/Feature/Equipes/AuditoriaPivotTest.php` PASS
- `C:/xampp/php/php.exe vendor/bin/pest tests/Feature/Equipes/AtribuirMembroTest.php tests/Feature/Equipes/BloqueioHMRuntimeTest.php tests/Feature/Equipes/AuditoriaPivotTest.php --stop-on-failure` PASS: 7 testes, 38 assertions
- `C:/xampp/php/php.exe vendor/bin/pest tests/Feature/Equipes --stop-on-failure` PASS: 30 testes, 99 assertions
- Primeira suite completa identificou 2 falhas; ambas corrigidas antes de concluir:
  - `tests/Unit/Policies/EquipePolicyTest.php`: expectativa antiga atualizada para o contrato Phase 4, onde `assignMembers()` direto tambem retorna true para `coord_geral`.
  - `tests/Feature/PessoaTest.php`: termo de busca tornado unico para evitar colisao com dados aleatorios da factory durante suite completa.
- `C:/xampp/php/php.exe vendor/bin/pint app/Policies/EquipePolicy.php tests/Unit/Policies/EquipePolicyTest.php tests/Feature/PessoaTest.php --test` PASS
- `C:/xampp/php/php.exe vendor/bin/pest tests/Unit/Policies/EquipePolicyTest.php tests/Feature/PessoaTest.php tests/Feature/Equipes --stop-on-failure` PASS: 65 testes, 182 assertions
- `C:/xampp/php/php.exe vendor/bin/pest` PASS: 318 testes, 790 assertions

## Resultado GSD/Nyquist

Todos os requisitos da matriz possuem teste automatizado ou gate executado. `04-VALIDATION.md` foi auditado em modo manual porque o `gsd-sdk` local falha por modulo ausente `@gsd-build/sdk/dist/cli.js`.
