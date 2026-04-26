# ANTITRASHING — Prompt de sessão

> Cole/cite este arquivo no início de cada sessão para travar o contexto.
> Complementa `CLAUDE.md` (regras de leitura) e `CONTEXT.md` (checkpoint volátil).

## CONTEXTO DO PROJETO: pnsl-ntm

### Stack
- **Backend**: Laravel 12.x (PHP 8.2+)
- **UI**: Livewire 3 com Flux/Volt
- **Testes**: Pest 3.8
- **Frontend build**: Vite 6 + Tailwind 4
- **PWA**: service worker + manifest (`public/sw.js`, `public/workbox-*.js`, `public/manifest.webmanifest`)

### Estado atual do projeto
- [feature X] está IMPLEMENTADA e funcionando em `app/Livewire/...` / `resources/views/...`
- [feature Y] está PENDENTE
- [feature Z] foi DESCARTADA — não sugira ela

> Preencher com features reais quando iniciar uma sessão.

### Arquivos canônicos (verdade atual)
- `app/Livewire/[Componente].php` = implementação atual de [domínio]
- `resources/views/livewire/[componente].blade.php` = view ativa
- `routes/web.php` = rotas ativas
- `config/[arquivo].php` = configuração ativa
- NÃO referencie versões anteriores desses arquivos

### Regras de comportamento desta sessão
1. Se eu mostrar um trecho de código, assuma que é o estado ATUAL — não o estado anterior
2. Não repita soluções que já tentamos nesta conversa sem eu pedir
3. Se perceber contradição entre o que eu disse antes e agora, pergunte antes de agir
4. Ao implementar algo, confirme o arquivo-alvo antes de gerar código
5. Nunca sugira refatoração geral a menos que eu peça explicitamente

### O que NÃO fazer
- Não misture lógica de **componente Livewire** (`app/Livewire/`) com **Blade estática** (`resources/views/` fora de `livewire/`)
- Não misture **Flux/Volt** (sintaxe nova, single-file components) com Livewire **Class+Blade** tradicional no mesmo componente
- Não reescreva arquivos inteiros — faça mudanças cirúrgicas
- Não assuma que o erro anterior ainda existe (rodei `php artisan optimize:clear` / `npm run build`?)
- Não crie migrations novas sem checar `database/migrations/` primeiro
- Não mexa em `public/sw.js` / `public/workbox-*.js` sem pedido explícito — são gerados

### Foco desta sessão
Estou trabalhando em: **[descreva o objetivo específico da sessão]**
