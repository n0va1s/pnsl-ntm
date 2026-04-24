# CLAUDE.md — pnsl-ntm

## Stack detectada
**Laravel 12 + Livewire (Flux/Volt) + Pest 3.8 + Vite 6 + Tailwind 4 + PWA (PHP 8.2+)**

- Backend: Laravel 12.x (PHP 8.2+)
- UI: Livewire 3 com Flux/Volt
- Testes: Pest 3.8
- Frontend build: Vite 6 + Tailwind 4
- PWA: service worker + manifest (`public/sw.js`, `public/workbox-*.js`, `public/manifest.webmanifest`)
- Assets compilados em `public/build/`

## Regras obrigatórias de leitura de arquivos (anti-thrashing)

Antes de abrir qualquer arquivo, Claude DEVE:

1. **Contar linhas primeiro**
   ```bash
   wc -l caminho/arquivo.php
   ```

2. **Se o arquivo tiver mais de 300 linhas, NUNCA ler tudo**
   - Use leitura em janelas com `sed`:
     ```bash
     sed -n '1,120p' caminho/arquivo.php
     sed -n '121,240p' caminho/arquivo.php
     ```
   - Ou use a ferramenta Read com `offset`/`limit` (máximo 200 linhas por chamada)

3. **Para buscar algo específico, SEMPRE grep antes**
   ```bash
   grep -rn "nome_do_simbolo" app/ resources/ routes/ --include="*.php" --include="*.blade.php"
   grep -rn "TermoDeBusca" app/Livewire/ --include="*.php"
   ```
   Nunca abra vários arquivos "para explorar" sem um alvo.

4. **head/tail para amostragem rápida**
   ```bash
   head -40 composer.json
   tail -60 storage/logs/laravel.log
   ```

5. **Arquivos/diretórios NUNCA abrir por padrão**
   - `vendor/`, `node_modules/`, `storage/app/`, `storage/logs/`, `storage/framework/`
   - `public/build/`, `public/sw.js`, `public/workbox-*.js`
   - `*.lock`, `package-lock.json`, `composer.lock`
   - `.env`, `.env.*` (exceto `.env.example`)
   - Já cobertos por `.claudeignore` — respeitar.

## Checkpoint obrigatório: CONTEXT.md

Para qualquer tarefa que **toca 3+ arquivos** ou dura mais de 10 minutos:

1. **Antes de começar**: leia `CONTEXT.md` para saber onde parou
2. **Durante**: atualize `CONTEXT.md` com arquivos tocados e decisões tomadas
3. **Ao pausar/entregar**: escreva o próximo passo em `CONTEXT.md`

`CONTEXT.md` é descartável (está no `.gitignore`) — use sem medo.

## Comandos úteis (Laravel/PHP)

```bash
# Testes
./vendor/bin/pest
./vendor/bin/pest --filter=NomeDoTeste

# Dev server
php artisan serve
npm run dev

# Build
npm run build

# Migrations
php artisan migrate
php artisan migrate:fresh --seed

# Livewire
php artisan livewire:make NomeComponente

# Limpar caches
php artisan optimize:clear
```

## Convenções do projeto

- Componentes Livewire em `app/Livewire/`
- Views Blade em `resources/views/`
- Rotas em `routes/web.php` e `routes/api.php`
- Testes Pest em `tests/Feature/` e `tests/Unit/`
- Migrations em `database/migrations/`

## Compact Instructions

Preserve with full detail (no vague summaries):
- Exact file paths of all created/modified files
- All architectural and technical decisions + reasoning
- Rejected approaches and why they were rejected
- Exact values: endpoints, env vars, schemas, versions, IDs
- Current task status: what's done, what's in progress, next step
- Any unresolved errors or explicit TODOs

After compacting, output a structured project status recap.
