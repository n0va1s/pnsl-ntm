<?php

namespace Database\Factories;

use App\Enums\PapelEquipe;
use App\Models\Equipe;
use App\Models\EquipeUsuario;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EquipeUsuarioFactory extends Factory
{
    protected $model = EquipeUsuario::class;

    public function definition(): array
    {
        return [
            'idt_equipe' => Equipe::factory(),
            'user_id'    => User::factory(),
            'papel'      => PapelEquipe::MembroEquipe,
            // usr_* e dat_* preenchidos via booted() hook do model
        ];
    }

    /**
     * State para definir um papel especifico.
     */
    public function comoPapel(PapelEquipe $papel): static
    {
        return $this->state(['papel' => $papel]);
    }
}
