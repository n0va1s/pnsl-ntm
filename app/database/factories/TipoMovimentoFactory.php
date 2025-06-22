<?php

namespace Database\Factories;

use App\Models\TipoMovimento;
use Illuminate\Database\Eloquent\Factories\Factory;

class TipoMovimentoFactory extends Factory
{
    protected $model = TipoMovimento::class;

    public function definition(): array
    {
        return [
            'nom_movimento' => $this->faker->randomElement(['Encontro de Adolescentes com Cristo', 'Encontro de Casais com Cristo', 'Encontro de Jovens com Cristo']),
            'des_sigla' => $this->faker->randomElement(['VEM', 'ECC', 'Segue-Me']),
            'dat_inicio' => $this->faker->date(),
        ];
    }
}
