<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class TipoSituacaoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'des_situacao' => $this->faker->randomElement(['Cadastrada', 'Avaliada', 'Visitada', 'Aprovada', 'Cancelada']),
        ];
    }
}
