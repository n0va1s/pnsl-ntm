<?php

namespace App\Providers;

use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \App\Models\Gamificacao::observe(\App\Observers\GamificacaoObserver::class);
    }

    /**
     * Middleware para geracao do traceId para todas as requisicoes
     */
    public function configure(Middleware $middleware): void
    {
        //
    }
}
