<?php

namespace Database\Factories;

use App\Models\Participante;
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
            'idt_participante' => Participante::inRandomOrder()->first()->idt_participante ?? Participante::factory(),
            'dat_presenca' => $this->faker->dateTimeBetween('-15 days', 'now')->format('Y-m-d'),
            'ind_presente' => $this->faker->boolean(80), // 80% chance de presença
        ];
    }
}
