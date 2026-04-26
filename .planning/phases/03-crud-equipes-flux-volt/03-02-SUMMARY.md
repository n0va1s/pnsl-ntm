# 03-02 SUMMARY — Rotas e index de equipes

**Concluido:** 2026-04-24
**Status:** DONE

## Arquivos modificados/criados

- `routes/web.php`
- `resources/views/livewire/equipes/index.blade.php`
- `app/Policies/EquipePolicy.php`

## Decisoes

- Rotas `equipes.index`, `equipes.create` e `equipes.edit` foram registradas dentro do grupo `auth`.
- `equipes.create` usa `->can('create', Equipe::class)`; `equipes.edit` usa `->can('update', 'equipe')`.
- `EquipePolicy::create()` foi adicionado explicitamente para estabilizar Gate/Volt/FormRequest; `coord_geral` continua autorizado pelo `before()`, e demais usuários retornam `false`.
- Como `users` nao possui coluna `idt_movimento`, o movimento do usuario e derivado pelo vinculo ativo em `equipe_usuario` via join com `equipes`.
- O index usa `withTrashed()` para exibir equipes ativas, inativas e arquivadas e permitir restauracao pela propria UI.
- `restaurar()` autoriza por pivot direta de `coord_geral`, porque a policy comum usa `User::equipes()`, que exclui equipes soft-deleted.
- Componentes `flux:table/*` foram rejeitados porque o Flux instalado nao possui `flux::columns`; a tabela foi implementada com HTML/Tailwind.
- Icon props `archive`, `archive-restore` e `save` foram rejeitados porque os icones nao existem no pacote Flux local.

## Verificacao

- `C:/xampp/php/php.exe -l routes/web.php` passou.
- `C:/xampp/php/php.exe -l resources/views/livewire/equipes/index.blade.php` passou.
- `C:/xampp/php/php.exe artisan route:list --name=equipes` mostrou as 3 rotas.
- `C:/xampp/php/php.exe vendor/bin/pest tests/Feature/Equipes/` passou com 23 testes.
