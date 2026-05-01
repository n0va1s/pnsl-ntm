| Route (URI) | Tested URL | HTTP Status | Return Message |
|-------------|------------|-------------|----------------|
| / | / | 200 | OK |
| vem | /vem | 200 | OK |
| ecc | /ecc | 500 | Undefined variable $situacoes (View: /var/www/html/resources/views/ficha/formECC.blade.php) |
| sgm | /sgm | 500 | Undefined variable $situacoes (View: /var/www/html/resources/views/ficha/formSGM.blade.php) |
| settings | /settings | 302 | OK |
| timeline | /timeline | 200 | OK |
| dashboard | /dashboard | 200 | OK |
| contatos | /contatos | 200 | OK |
| participantes | /participantes | 200 | OK |
| eventos | /eventos | 200 | OK |
| eventos/create | /eventos/create | 200 | OK |
| eventos/{evento} | /eventos/1 | 200 | OK |
| eventos/{evento}/edit | /eventos/1/edit | 200 | OK |
| pessoas | /pessoas | 200 | OK |
| pessoas/create | /pessoas/create | 200 | OK |
| pessoas/{pessoa} | /pessoas/1 | 500 | Call to undefined method App\Http\Controllers\PessoaController::show() |
| pessoas/{pessoa}/edit | /pessoas/1/edit | 200 | OK |
| fichas/vem | /fichas/vem | 200 | OK |
| fichas/vem/create | /fichas/vem/create | 200 | OK |
| fichas/vem/{vem} | /fichas/vem/1 | 200 | OK |
| fichas/vem/{vem}/edit | /fichas/vem/1/edit | 200 | OK |
| fichas/ecc | /fichas/ecc | 200 | OK |
| fichas/ecc/create | /fichas/ecc/create | 500 | Undefined variable $situacoes (View: /var/www/html/resources/views/ficha/formECC.blade.php) |
| fichas/ecc/{ecc} | /fichas/ecc/1 | 500 | Call to undefined method App\Models\Ficha::analises() |
| fichas/ecc/{ecc}/edit | /fichas/ecc/1/edit | 500 | Call to undefined method App\Models\Ficha::analises() |
| fichas/sgm | /fichas/sgm | 200 | OK |
| fichas/sgm/create | /fichas/sgm/create | 500 | Undefined variable $situacoes (View: /var/www/html/resources/views/ficha/formSGM.blade.php) |
| fichas/sgm/{sgm} | /fichas/sgm/1 | 500 | Undefined variable $situacoes (View: /var/www/html/resources/views/ficha/formSGM.blade.php) |
| fichas/sgm/{sgm}/edit | /fichas/sgm/1/edit | 500 | Undefined variable $situacoes (View: /var/www/html/resources/views/ficha/formSGM.blade.php) |
| fichas/vem/approve/{id} | /fichas/vem/approve/1 | 500 | Call to undefined method App\Http\Controllers\FichaVemController::approve() |
| fichas/ecc/approve/{id} | /fichas/ecc/approve/1 | 500 | Call to undefined method App\Http\Controllers\FichaEccController::approve() |
| fichas/sgm/approve/{id} | /fichas/sgm/approve/1 | 500 | Call to undefined method App\Http\Controllers\FichaSGMController::approve() |
| trabalhadores | /trabalhadores | 200 | OK |
| trabalhadores/create | /trabalhadores/create | 500 | Attempt to read property "idt_evento" on null (View: /var/www/html/resources/views/trabalhador/form. |
| trabalhadores/review | /trabalhadores/review | 200 | OK |
| avaliacao | /avaliacao | 200 | OK |
| montagem | /montagem | 200 | OK |
| quadrante | /quadrante | 200 | OK |
| aniversario | /aniversario | 200 | OK |
| configuracoes | /configuracoes | 200 | OK |
| configuracoes/role | /configuracoes/role | 200 | OK |
| configuracoes/equipe | /configuracoes/equipe | 200 | OK |
| configuracoes/equipe/create | /configuracoes/equipe/create | 200 | OK |
| configuracoes/equipe/{equipe} | /configuracoes/equipe/1 | 200 | OK |
| configuracoes/equipe/{equipe}/edit | /configuracoes/equipe/1/edit | 200 | OK |
| configuracoes/movimento | /configuracoes/movimento | 200 | OK |
| configuracoes/movimento/create | /configuracoes/movimento/create | 200 | OK |
| configuracoes/movimento/{movimento} | /configuracoes/movimento/1 | 500 | View [configuracoes.TipoMovimentoShow] not found. |
| configuracoes/movimento/{movimento}/edit | /configuracoes/movimento/1/edit | 200 | OK |
| configuracoes/responsavel | /configuracoes/responsavel | 200 | OK |
| configuracoes/responsavel/create | /configuracoes/responsavel/create | 200 | OK |
| configuracoes/responsavel/{responsavel} | /configuracoes/responsavel/1 | 200 | OK |
| configuracoes/responsavel/{responsavel}/edit | /configuracoes/responsavel/1/edit | 200 | OK |
| configuracoes/restricao | /configuracoes/restricao | 200 | OK |
| configuracoes/restricao/create | /configuracoes/restricao/create | 200 | OK |
| configuracoes/restricao/{restricao} | /configuracoes/restricao/1 | 200 | OK |
| configuracoes/restricao/{restricao}/edit | /configuracoes/restricao/1/edit | 200 | OK |
| eventos/{evento}/gerenciamento | /eventos/1/gerenciamento | 200 | OK |
| settings/profile | /settings/profile | 200 | OK |
| settings/password | /settings/password | 200 | OK |
| settings/appearance | /settings/appearance | 200 | OK |
| login | /login | 302 | OK |
| register | /register | 302 | OK |
| forgot-password | /forgot-password | 302 | OK |
| reset-password/{token} | /reset-password/1 | 302 | OK |
| verify-email | /verify-email | 200 | OK |
| verify-email/{id}/{hash} | /verify-email/1/1 | 403 | Invalid signature. |
| confirm-password | /confirm-password | 200 | OK |
