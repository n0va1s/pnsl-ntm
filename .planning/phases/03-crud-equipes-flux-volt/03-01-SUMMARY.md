# 03-01 SUMMARY — FormRequests de Equipe

**Concluido:** 2026-04-24
**Status:** DONE

## Arquivos criados

- `app/Http/Requests/EquipeStoreRequest.php`
- `app/Http/Requests/EquipeUpdateRequest.php`

## Decisoes

- `EquipeStoreRequest::authorize()` delega para `can('create', Equipe::class)`.
- `EquipeUpdateRequest::authorize()` delega para `can('update', $this->route('equipe'))`.
- `des_slug` permanece opcional; quando enviado, precisa ser lowercase slug e unico dentro do mesmo `idt_movimento`.
- `EquipeUpdateRequest` usa `->ignore($equipe->idt_equipe, 'idt_equipe')` porque a PK de `Equipe` nao e `id`.
- EQUIPE-08 nao foi implementado em FormRequest; foi movido para Phase 4, junto de ATRIB-06 + TEST-04.

## Verificacao

- `C:/xampp/php/php.exe -l app/Http/Requests/EquipeStoreRequest.php` passou.
- `C:/xampp/php/php.exe -l app/Http/Requests/EquipeUpdateRequest.php` passou.
- `C:/xampp/php/php.exe vendor/bin/pint app/Http/Requests/EquipeStoreRequest.php app/Http/Requests/EquipeUpdateRequest.php` passou; Pint ajustou imports/docblock no update request.
