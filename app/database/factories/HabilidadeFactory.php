<?php

namespace Database\Factories;

use App\Models\Habilidade;
use App\Models\TipoEquipe;
use Illuminate\Database\Eloquent\Factories\Factory;

class HabilidadeFactory extends Factory
{
    protected $model = Habilidade::class;

    public function definition(): array
    {
        return [
            'idt_equipe' => $this->faker->numberBetween(1,10),
            'des_habilidade' => $this->faker->unique()->words(2, true),
        ];
    }
}
