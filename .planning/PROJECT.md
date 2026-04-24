# pnsl-ntm — Sistema de Gestão do Movimento Canônico

## What This Is

Sistema web PWA de gestão paroquial para os movimentos canônicos (VEM, ECC, SGM) — administra pessoas, fichas de inscrição, eventos, equipes e gamificação. Construído em Laravel 12 + Livewire 3 (Flux/Volt) com foco em acessibilidade mobile-first para coordenações e membros.

## Core Value

Permitir que a coordenação paroquial e as equipes de cada movimento gerenciem inscrições, eventos, presenças e engajamento dos membros de forma digital, sem depender de planilhas e fluxos manuais.

## Requirements

### Validated

<!-- Inferido do código existente e uso em produção (Hostinger FTP deploy) -->

- ✓ Autenticação via Volt SFC (login, registro, recuperação, verificação) — v1.0
- ✓ Cascata de onboarding `User ↔ Pessoa` com senha temporária DDMMYYYY + `BoasVindasMail` — v1.0
- ✓ Cadastro de fichas VEM/ECC/SGM com estado civil e log de inclusão/alteração — v1.0
- ✓ Cadastro de eventos com fotos, tipo de movimento e contagem de fichas — v1.0
- ✓ Presença em eventos (`Presenca`) — v1.0
- ✓ Gamificação via `GamificacaoObserver` atualizando `pessoa.qtd_pontos_total` — v1.0
- ✓ Email de aniversário agendado para 08:00 (`aniversario:enviar`) — v1.0
- ✓ RBAC flat (`users.role` = user/admin/coord) + middleware `manager` — v1.0
- ✓ `TraceIdMiddleware` global (UUID por request para logs correlacionados) — v1.0
- ✓ PWA ("Movimento Canônico", autoUpdate, theme `#2563eb`, standalone) — v1.0
- ✓ Dashboards: Home, Timeline, Contato, Aniversário, Quadrante, Montagem — v1.0
- ✓ Escopo por movimento via `users.idt_movimento` (VEM/ECC/SGM) — v1.0
- ✓ Interface 100% pt_BR — v1.0
- ✓ Deploy automático Hostinger via FTP (GitHub Action) — v1.0

### Active

<!-- Marco v1.1: Gestão de Equipes VEM — Fundação (em andamento desde 2026-04-21) -->

**Escopo do milestone v1.1 (fundação):**

- [ ] **RBAC escopado por equipe** — introduzir papéis `coord-geral`, `coord-equipe-h`, `coord-equipe-m`, `membro-equipe` via tabela pivot `equipe_usuario`, coexistindo com `users.role` flat; autorização migra para Gate/Policy nativos do Laravel
- [ ] **Estrutura de 11 equipes VEM** — CRUD + seed das equipes (sala, limpeza, reportagem, oração, vendinha, alimentação, emaús, secretaria, troca de ideias, recepção, bandinha); cada equipe aceita 2 coordenadores (H+M) e N membros
- [ ] **Atribuição de membros às equipes** — tela de gestão para a coordenação geral vincular/desvincular pessoas e definir papéis na equipe

### Backlog (milestones futuros)

<!-- Itens depriorizados do marco VEM após decisão de 2026-04-21 -->

- [ ] **v1.2 Espaços de equipe** — comunicados/instruções, eventos internos (reuniões, ensaios, tarefas) com confirmação de presença e histórico
- [ ] **v1.3 Gamificação Score 0-100** — frequência + avaliação do coordenador, rubrica thinkworklab (faixas qualitativas), aplica a todos (inclusive coordenadores), visibilidade restrita à coordenação geral
- [ ] **v1.4 Módulo Vendinha** — registro de vendas, estatísticas (receita, volume, ticket médio, top produtos)
- [ ] **v1.5 Análise de IA para Vendinha** — ROI por produto, sazonalidade, recomendações

### Out of Scope

- **Integrações HTTP externas** — nenhum client HTTP fora do Laravel padrão; drivers em `log` para broadcasting/mail em dev
- **OAuth/SSO** — auth apenas via guard `web` (eloquent provider); sem Socialite
- **REST/GraphQL API pública** — `routes/api.php` não existe; sistema é UI-only Livewire
- **Error tracking externo (Sentry/Bugsnag)** — sem custo adicional; logs locais + `laravel/pail` cobrem a operação
- **Broadcasting/realtime** — `routes/channels.php` não existe; PWA faz autoUpdate de build
- **Multi-paróquia/multi-tenancy** — fora do escopo; escopo atual é uma paróquia

## Context

- **Ambiente:** Hostinger (shared hosting) com deploy FTP; PHP 8.2+ exigido, CI usa 8.4.
- **Banco:** SQLite por padrão em dev/testes (`database/database.sqlite`, `database/testing.sqlite`), MySQL em produção.
- **Frontend:** Tailwind v4 (config no CSS, sem `tailwind.config.js`), Vite 6, Flux UI kit (Free) + Volt SFCs.
- **Testes:** Pest 3.8 com ~24 Feature + 3 Unit; CI roda `vendor/bin/pint --test` + `php artisan test`.
- **Serviços:** `EventoService` (~150 linhas, usa `DB::transaction`), `FichaService` (~121 linhas, `Cache::remember` 3600s), `VoluntarioService` (~136 linhas, `ValidationException`), `UserService` (fino).
- **Sem Gate/Policy classes** ainda — toda autorização passa pelo alias `manager` em rotas.
- **i18n:** apenas `resources/lang/pt_BR/auth.php`.
- **Cache/Queue:** driver `database` em prod, `array`/`sync` em testes.

## Constraints

- **Tech stack**: Laravel 12 + Livewire 3 (Flux 2.1 / Volt 1.7) + PHP 8.2+ — impostos pelo `composer.json`; evitar migrar para outros frameworks.
- **Hospedagem**: Hostinger shared via FTP — não há acesso a Redis/workers dedicados; soluções baseadas em `database` queue/cache.
- **Coverage**: Pest 80%+ em features novas — padrão do repo; CI roda Pint --test obrigatório.
- **Código**: PSR-12, `declare(strict_types=1);`, DTOs readonly imutáveis, `FormRequest` para validação, services fat para regra de negócio.
- **UX**: Mobile-first PWA; interface em pt_BR; Flux kit como fonte de verdade visual.
- **Permissões atuais**: `users.role` string flat + `OnlyManagerMiddleware` — refatoração planejada no Marco VEM.
- **Assets**: Vite 6 com `refresh: true`; service worker precacheia `public/build/assets/*`.

## Key Decisions

| Decision | Rationale | Outcome |
|----------|-----------|---------|
| RBAC flat via `users.role` (user/admin/coord) | Simplicidade inicial; número baixo de papéis | ⚠️ Revisit — Marco VEM exige permissões escopadas por equipe |
| Gamificação via Observer (`GamificacaoObserver`) | Mantém `pessoa.qtd_pontos_total` sempre consistente | ✓ Good |
| Dispatch de mail síncrono (apesar de `Queueable`) | Volume baixo; simplifica debug em shared hosting | ⚠️ Revisit se volume de emails crescer |
| Volt SFC como padrão de UI | Reduz boilerplate de classes Livewire; co-localiza lógica | ✓ Good |
| SQLite em dev + MySQL em prod | Zero-config local; prod já pago | ✓ Good |
| Deploy FTP via GitHub Action | Hostinger não oferece Git deploy; FTP é o único canal | ✓ Good |
| `TraceIdMiddleware` global com UUID | Correlaciona logs sem APM externo | ✓ Good |
| Escopo por movimento via `users.idt_movimento` FK | Separa VEM/ECC/SGM sem multi-tenancy completo | ✓ Good |
| **v1.1**: Tabela pivot `equipe_usuario` + `users.role` flat preservado + Gate/Policy nativos | Baixo risco, reversível; não invalida middleware `manager` existente; permissões escopadas por equipe sem reescrever o modelo de usuário | ✓ Adopted 2026-04-21 |
| **v1.1**: Milestone restrito à fundação (RBAC + 11 equipes) — Vendinha/Gamification Score/Espaços/IA viram v1.2+ | Fundação sólida antes de features dependentes; evita escopo inchado; cada módulo futuro precisa do modelo de equipe primeiro | ✓ Adopted 2026-04-21 |

---
*Last updated: 2026-04-21 após escopo do Marco v1.1 definido (fundação: RBAC escopado + 11 equipes VEM)*
