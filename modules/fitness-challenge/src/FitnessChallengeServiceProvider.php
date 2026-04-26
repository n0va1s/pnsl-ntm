<?php

namespace Modules\FitnessChallenge;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\FitnessChallenge\Http\Middleware\RequireFitnessChallenge;

class FitnessChallengeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/fitness-challenge.php',
            'fitness-challenge'
        );
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        Route::aliasMiddleware('fitness.enabled', RequireFitnessChallenge::class);

        Route::middleware(['web', 'auth', 'fitness.enabled'])
            ->prefix('api/fitness')
            ->name('fitness.')
            ->group(__DIR__.'/../routes/api.php');
    }
}
