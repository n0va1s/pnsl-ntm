# 03-03 SUMMARY — Create/edit Volt SFCs

**Concluido:** 2026-04-24
**Status:** DONE

## Arquivos criados

- `resources/views/livewire/equipes/create.blade.php`
- `resources/views/livewire/equipes/edit.blade.php`

## Decisoes

- `create` valida inline as mesmas regras de `EquipeStoreRequest`.
- `create` remove `des_slug` do payload quando vazio para permitir que o mutator de `Equipe::setNomEquipeAttribute()` gere o slug.
- `create` persiste `idt_movimento` derivado do vinculo ativo do usuario em `equipe_usuario`, porque `users.idt_movimento` nao existe.
- `edit` expõe `des_slug` para correcao manual.
- `edit` usa `Rule::unique('equipes', 'des_slug')->where('idt_movimento', $this->equipe->idt_movimento)->ignore($this->equipe->idt_equipe, 'idt_equipe')`.
- `edit` persiste o toggle `ind_ativa`.

## Verificacao

- `C:/xampp/php/php.exe -l resources/views/livewire/equipes/create.blade.php` passou.
- `C:/xampp/php/php.exe -l resources/views/livewire/equipes/edit.blade.php` passou.
- `C:/xampp/php/php.exe vendor/bin/pest tests/Feature/Equipes/` passou com 23 testes.
