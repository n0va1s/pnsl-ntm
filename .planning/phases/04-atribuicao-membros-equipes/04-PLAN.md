# Phase 4 Plan - Atribuicao de membros e coordenadores

**Criado:** 2026-04-25
**Status:** DONE
**GSD:** planejado manualmente porque `gsd-sdk` local falha com modulo ausente `@gsd-build/sdk/dist/cli.js`.

## Goal

Permitir que coordenacao geral atribua, altere papel e remova usuarios de equipes VEM por UI Volt, com bloqueio runtime de 2o coordenador H/M e exibicao das equipes no perfil.

## Requisitos

- EQUIPE-08
- ATRIB-01
- ATRIB-02
- ATRIB-03
- ATRIB-04
- ATRIB-05
- ATRIB-06
- ATRIB-07
- ATRIB-08
- TEST-04

## Decisoes

- A fase sera empilhada sobre a branch/PR da Phase 3, porque depende de `Equipe`, `EquipePolicy`, rotas `equipes.*` e Volt SFCs ja criados.
- A rota nova sera `GET /equipes/{equipe}/atribuir`, nome `equipes.atribuir`.
- Rejeitado: `->can('assignMembers', 'equipe')` direto na rota Volt, porque retornou 403 antes da montagem do componente neste projeto. O guard permanece via Gate/Policy no `mount()` e em todas as acoes do componente.
- O Volt SFC `resources/views/livewire/equipes/atribuir.blade.php` concentrara as acoes `atribuir`, `alterarPapel` e `remover`.
- A listagem elegivel usara usuarios com `pessoa` vinculada, excluindo usuarios ja vinculados ativamente a equipe. O escopo VEM sera derivado do movimento da equipe (`equipes.idt_movimento`); como `pessoa` nao possui `idt_movimento`, a elegibilidade usa pessoas com usuario existente para vinculacao operacional.
- Para papeis `coord_equipe_h` e `coord_equipe_m`, a UI e a validacao runtime filtram/validam `Pessoa::tip_genero` como `M`/`F`.
- O bloqueio H/M sera aplicado em `atribuir()` e `alterarPapel()` antes de criar/atualizar a pivot.
- Remocao usa soft-delete em `EquipeUsuario` para preservar historico.
- Auditoria da pivot fica no `booted()` existente de `EquipeUsuario`, preenchendo `usr_inclusao`/`dat_inclusao` em create e `usr_alteracao`/`dat_alteracao` em update/delete.

## Entregaveis

- `resources/views/livewire/equipes/atribuir.blade.php`
- `routes/web.php`
- `resources/views/livewire/settings/profile.blade.php`
- `tests/Feature/Equipes/AtribuirMembroTest.php`
- `tests/Feature/Equipes/BloqueioHMRuntimeTest.php`
- `tests/Feature/Equipes/AuditoriaPivotTest.php`
- `.planning/phases/04-atribuicao-membros-equipes/04-VALIDATION.md`

## Gates

- `C:/xampp/php/php.exe -l resources/views/livewire/equipes/atribuir.blade.php`
- `C:/xampp/php/php.exe vendor/bin/pest tests/Feature/Equipes/AtribuirMembroTest.php tests/Feature/Equipes/BloqueioHMRuntimeTest.php tests/Feature/Equipes/AuditoriaPivotTest.php`
- `C:/xampp/php/php.exe vendor/bin/pest tests/Feature/Equipes/`
- `C:/xampp/php/php.exe vendor/bin/pest`
