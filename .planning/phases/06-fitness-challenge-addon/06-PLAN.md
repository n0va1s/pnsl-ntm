# Phase 6 Plan - Addon Fitness Challenge

**Criado:** 2026-04-25
**Status:** DONE
**GSD:** planejado manualmente porque `gsd-sdk` local falha com modulo ausente `@gsd-build/sdk/dist/cli.js`.

## Goal

Criar a primeira fatia funcional do addon de desafios fitness, inspirado no GymRats, como modulo plugavel e ativavel/desativavel por feature flag.

## Escopo desta fatia

- Backend core do addon.
- Modulo isolado em `modules/fitness-challenge/`.
- Feature flag `FEATURE_FITNESS_CHALLENGE`.
- Migrations, models, scoring service, leaderboard service e API principal.
- Testes unitarios do scoring e testes feature dos endpoints principais.

## Decisoes

- A especificacao original usa TypeScript/Express/React, mas o projeto real e Laravel 12 + Livewire/Volt. A implementacao foi adaptada para PHP/Laravel mantendo os conceitos.
- O modulo fica em `modules/fitness-challenge/` com namespace `Modules\FitnessChallenge`.
- Integrações fora do modulo sao minimas e explicitas:
  - `composer.json`: autoload PSR-4 do modulo.
  - `bootstrap/providers.php`: registra `FitnessChallengeServiceProvider`.
  - `phpunit.xml`: inclui testes e source do modulo.
  - `.env.example`: documenta `FEATURE_FITNESS_CHALLENGE=false`.
  - `tests/Pest.php`: inclui os testes do modulo no bootstrap Laravel.
- As rotas sao registradas pelo service provider sob `/api/fitness`, com middleware `web`, `auth` e `fitness.enabled`.
- Quando a feature flag esta desligada, o middleware retorna 404 silencioso.
- Check-ins exigem `title`, `media_path` e `media_type`; upload real/thumbnail/FFmpeg fica para fase frontend/midia.
- Likes foram modelados como tabela relacional (`fitness_check_in_likes`) em vez de array JSON para preservar integridade e consultas.

## Entregaveis

- `modules/fitness-challenge/config/fitness-challenge.php`
- `modules/fitness-challenge/src/FitnessChallengeServiceProvider.php`
- `modules/fitness-challenge/src/Http/Middleware/RequireFitnessChallenge.php`
- `modules/fitness-challenge/src/Http/Controllers/*`
- `modules/fitness-challenge/src/Models/*`
- `modules/fitness-challenge/src/Services/*`
- `modules/fitness-challenge/src/Enums/ScoringType.php`
- `modules/fitness-challenge/database/migrations/2026_04_25_000001_create_fitness_challenge_tables.php`
- `modules/fitness-challenge/routes/api.php`
- `modules/fitness-challenge/tests/*`

## Gates

- `C:/xampp/php/php.exe composer.phar dump-autoload`
- `C:/xampp/php/php.exe -l` nos arquivos PHP do modulo
- `C:/xampp/php/php.exe vendor/bin/pint .env.example bootstrap/providers.php composer.json phpunit.xml tests/Pest.php modules/fitness-challenge --test`
- `C:/xampp/php/php.exe vendor/bin/pest modules/fitness-challenge/tests --stop-on-failure`
- `C:/xampp/php/php.exe artisan route:list --name=fitness`
- `C:/xampp/php/php.exe vendor/bin/pest`
