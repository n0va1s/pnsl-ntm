<?php

namespace Modules\Vendinha\Services;

use App\Models\Equipe;
use App\Models\User;

class VendinhaAccessService
{
    public function equipe(): ?Equipe
    {
        return Equipe::query()
            ->where('des_slug', config('vendinha.equipe_slug', 'vendinha'))
            ->where('ind_ativa', true)
            ->first();
    }

    public function canAccess(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->isAdmin() || $user->isCoordenadorGeral()) {
            return true;
        }

        $equipe = $this->equipe();

        return $equipe !== null && $user->isCoordenadorDe($equipe);
    }
}
