<?php

namespace Modules\Vendinha;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Vendinha\Http\Middleware\RequireVendinhaAccess;

class VendinhaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/vendinha.php',
            'vendinha'
        );
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'vendinha');

        Route::aliasMiddleware('vendinha.access', RequireVendinhaAccess::class);

        Route::middleware(['web', 'auth', 'vendinha.access'])
            ->prefix('vendinha')
            ->name('vendinha.')
            ->group(__DIR__.'/../routes/web.php');
    }
}
