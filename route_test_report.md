| Route (URI) | Tested URL | HTTP Status | Return Message |
|-------------|------------|-------------|----------------|
| configuracoes | /configuracoes | 200 | OK |
| configuracoes/role | /configuracoes/role | 200 | OK |
| configuracoes/equipe | /configuracoes/equipe | 200 | OK |
| configuracoes/equipe/create | /configuracoes/equipe/create | 200 | OK |
| configuracoes/equipe/{equipe} | /configuracoes/equipe/1 | 200 | OK |
| configuracoes/equipe/{equipe}/edit | /configuracoes/equipe/1/edit | 200 | OK |
| configuracoes/movimento | /configuracoes/movimento | 200 | OK |
| configuracoes/movimento/create | /configuracoes/movimento/create | 200 | OK |
| configuracoes/movimento/{movimento} | /configuracoes/movimento/1 | 500 | Call to undefined method App\Http\Controllers\TipoMovimentoController::show() |
| configuracoes/movimento/{movimento}/edit | /configuracoes/movimento/1/edit | 200 | OK |
| configuracoes/responsavel | /configuracoes/responsavel | 200 | OK |
| configuracoes/responsavel/create | /configuracoes/responsavel/create | 200 | OK |
| configuracoes/responsavel/{responsavel} | /configuracoes/responsavel/1 | 200 | OK |
| configuracoes/responsavel/{responsavel}/edit | /configuracoes/responsavel/1/edit | 200 | OK |
| configuracoes/restricao | /configuracoes/restricao | 200 | OK |
| configuracoes/restricao/create | /configuracoes/restricao/create | 200 | OK |
| configuracoes/restricao/{restricao} | /configuracoes/restricao/1 | 200 | OK |
| configuracoes/restricao/{restricao}/edit | /configuracoes/restricao/1/edit | 200 | OK |
