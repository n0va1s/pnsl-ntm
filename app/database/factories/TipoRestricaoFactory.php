<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class TipoRestricaoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'des_restricao' => $this->faker->randomElement(['Gluten', 'Ovo', 'Castanhas', 'Frutos do Mar', 'Motor', 'Auditivo', 'Neurodivergente']),
            'tip_restrição' => $this->faker->randomElement(['ALE', 'INT', 'PNE']), // alergia, intolerância, PNE
        ];
    }
}
