<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Equipe;
use App\Policies\EquipePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Mapa explícito Model → Policy.
     * O registro explícito em $policies tem precedência sobre o auto-discovery do Laravel 12
     * e blinda contra renomeação silenciosa do model. RBAC-09.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Equipe::class => EquipePolicy::class,
    ];

    public function boot(): void
    {
        // registerPolicies() é chamado automaticamente pela base class em register()
        // via booting() callback — não chamar novamente aqui (redundante em Laravel 12).
        // Adicionar Gates extras aqui nas fases futuras se necessário.
    }
}
