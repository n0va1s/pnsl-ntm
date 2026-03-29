<?php

namespace App\Providers;

use App\Models\Gamificacao;
use App\Observers\GamificacaoObserver;
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
        Gamificacao::observe(GamificacaoObserver::class);
    }

    /**
     * Middleware para geracao do traceId para todas as requisicoes
     */
    public function configure(Middleware $middleware): void
    {
        //
    }
}
