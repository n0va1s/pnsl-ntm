# Phase 6 Validation - Addon Fitness Challenge

**Criado:** 2026-04-25
**Status:** PASS

## Matriz de validacao

| Requisito | Verificacao | Evidencia esperada |
|-----------|-------------|--------------------|
| Addon isolado | Revisao de caminhos + autoload | Codigo de dominio fica em `modules/fitness-challenge/`; integracoes core sao explicitas |
| Feature flag | `FeatureFlagTest` | `FEATURE_FITNESS_CHALLENGE=false` retorna 404; true libera rotas |
| Migrations/models | Feature tests com `RefreshDatabase` | Tabelas sob prefixo `fitness_*` criam desafios, participantes, times, check-ins, likes e comentarios |
| Scoring flexivel | `ScoringServiceTest` | Todos os modos basicos e `hustle_points` calculam corretamente |
| CRUD desafio/convite | `FitnessChallengeApiTest` | Criador cria desafio e participante entra por convite |
| Check-in/feed/social | `FitnessChallengeApiTest` | Check-in calcula score, atualiza participante, permite like e comentario |
| Barreira de midia | `FitnessMediaModerationTest` | Check-in pendente nao aparece/pontua; termos sexuais sao bloqueados; upload valido fica em quarentena; usuario comum nao modera |
| Frontend Livewire | `FitnessFrontendTest` | Rotas `/fitness`, criacao, detalhe, check-in, ranking e historico renderizam quando a flag esta ativa; flag desligada retorna 404 |
| Leaderboard | `FitnessChallengeApiTest` | Ranking individual e por times retorna ordem/pontos esperados |

## Evidencia inicial

- `C:/xampp/php/php.exe composer.phar dump-autoload` PASS
- `C:/xampp/php/php.exe -l` nos arquivos PHP do modulo PASS
- `C:/xampp/php/php.exe vendor/bin/pint .env.example bootstrap/providers.php composer.json phpunit.xml tests/Pest.php modules/fitness-challenge --test` PASS
- `C:/xampp/php/php.exe vendor/bin/pest modules/fitness-challenge/tests --stop-on-failure` PASS: 11 testes, 38 assertions
- `C:/xampp/php/php.exe artisan route:list --name=fitness` PASS: 16 rotas
- Primeira tentativa de `C:/xampp/php/php.exe vendor/bin/pest` em branch limpa `upstream/main` falhou por baseline preexistente sem as correcoes das PRs anteriores (ex.: Vite manifest ausente e contratos legados). Decisao: empilhar esta feature sobre `codex/phase-5-hardening-regressao`, que e a base ja estabilizada, e rerodar a suite completa nessa base.

## Evidencia final em base estabilizada

- Branch final: `codex/fitness-challenge-addon`, rebaseado sobre `codex/phase-5-hardening-regressao`.
- `C:/xampp/php/php.exe composer.phar dump-autoload` PASS
- `C:/xampp/php/php.exe vendor/bin/pint .env.example bootstrap/providers.php composer.json phpunit.xml tests/Pest.php modules/fitness-challenge --test` PASS
- `C:/xampp/php/php.exe artisan route:list --name=fitness` PASS: 16 rotas
- `C:/xampp/php/php.exe vendor/bin/pest modules/fitness-challenge/tests --stop-on-failure` PASS: 11 testes, 38 assertions
- A primeira suite completa em base estabilizada falhou em 2 testes antigos de migrations de equipes, porque a nova migration do addon mudou a ordem global do rollback por `--step`.
- Correcao aplicada: testes `tests/Feature/Equipes/EquipeMigrationTest.php` e `tests/Feature/Equipes/EquipeUsuarioMigrationTest.php` passaram a fazer rollback por `--path`, removendo a dependencia da posicao global das migrations.
- `C:/xampp/php/php.exe vendor/bin/pest tests/Feature/Equipes/EquipeMigrationTest.php tests/Feature/Equipes/EquipeUsuarioMigrationTest.php modules/fitness-challenge/tests --stop-on-failure` PASS: 21 testes, 58 assertions
- `C:/xampp/php/php.exe vendor/bin/pint tests/Feature/Equipes/EquipeMigrationTest.php tests/Feature/Equipes/EquipeUsuarioMigrationTest.php modules/fitness-challenge --test` PASS
- `C:/xampp/php/php.exe vendor/bin/pest` PASS: 332 testes, 841 assertions

## Evidencia adicional - moderacao de midia

- Decisao de seguranca: provas de treino ficam `pending` por padrao via `FITNESS_CHALLENGE_REQUIRE_MANUAL_MEDIA_REVIEW=true`.
- Check-ins pendentes nao entram no feed publico, nao aceitam like/comentario e nao alteram `total_score`/leaderboard.
- Admin/coord podem aprovar via `POST /api/fitness/moderation/check-ins/{checkIn}/approve`; aprovacao calcula score e credita participante/time.
- Admin/coord podem rejeitar via `POST /api/fitness/moderation/check-ins/{checkIn}/reject`; rejeicao revoga pontuacao se houver.
- `C:/xampp/php/php.exe vendor/bin/pest modules/fitness-challenge/tests --stop-on-failure` PASS: 15 testes, 64 assertions
- `C:/xampp/php/php.exe artisan route:list --name=fitness` PASS: 19 rotas
- `C:/xampp/php/php.exe vendor/bin/pest tests/Feature/Equipes/EquipeMigrationTest.php tests/Feature/Equipes/EquipeUsuarioMigrationTest.php modules/fitness-challenge/tests --stop-on-failure` PASS: 25 testes, 84 assertions
- `C:/xampp/php/php.exe vendor/bin/pest` PASS: 336 testes, 867 assertions

## Evidencia adicional - frontend Livewire

- Rotas web adicionadas: `/fitness`, `/fitness/challenges/new`, `/fitness/challenges/{challenge}`, `/fitness/challenges/{challenge}/checkin`, `/fitness/challenges/{challenge}/ranking` e `/profile/fitness`.
- Nomes web usam prefixo `fitness.app.*`, exceto `fitness.index`, para evitar colisao com a API.
- `C:/xampp/php/php.exe vendor/bin/pint routes/web.php resources/views/components/layouts/app/sidebar.blade.php resources/views/livewire/fitness modules/fitness-challenge/tests/Feature/FitnessFrontendTest.php --test` PASS
- `C:/xampp/php/php.exe artisan route:list --name=fitness` PASS: 25 rotas
- `C:/xampp/php/php.exe vendor/bin/pest modules/fitness-challenge/tests --stop-on-failure` PASS: 18 testes, 79 assertions
- `C:/xampp/php/php.exe vendor/bin/pest tests/Feature/Equipes/EquipeMigrationTest.php tests/Feature/Equipes/EquipeUsuarioMigrationTest.php modules/fitness-challenge/tests --stop-on-failure` PASS: 28 testes, 99 assertions
- `C:/xampp/php/php.exe vendor/bin/pest` PASS: 339 testes, 882 assertions

## Resultado GSD/Nyquist

Todos os requisitos desta fatia possuem teste automatizado ou gate executado. A limitacao restante e funcional, nao de validacao: thumbnails de video, notificacoes por hooks, streak system e filtros avancados ficam fora desta primeira fatia e devem virar proximas fases do addon.
