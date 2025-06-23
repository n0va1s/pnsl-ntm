<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class PresencaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ind_par' => Participante::inRandomOrder()->first()->idt_participante ?? Participante::factory(),
            'dat_presenca' => $this->faker->dateTimeBetween('-3 day', 'now')->format('d/m/Y'),
            'inc_presenca' => $this->faker->boolean(80), // 80% chance de presença
        ];
    }
}
