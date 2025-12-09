<?php

namespace Database\Factories;

use App\Models\Evento;
use App\Models\TipoMovimento;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventoFactory extends Factory
{
    protected $model = Evento::class;

    public function definition(): array
    {
        $dataInicio = $this->faker->dateTimeBetween('-1 year', '+1 month');
        $dataTermino = (clone $dataInicio)->modify('+3 days');

        return [
            'idt_movimento' => TipoMovimento::factory(),
            'des_evento' => $this->faker->words(2, true),
            'num_evento' => $this->faker->numberBetween(1, 99),
            'dat_inicio' => $dataInicio->format('Y-m-d'),
            'dat_termino' => $dataTermino->format('Y-m-d'),
            'val_camiseta' => $this->faker->randomNumber(2, 40),
            'val_trabalhador' => $this->faker->randomNumber(2, 45),
            'val_venista' => $this->faker->randomNumber(2, 50),
            'val_entrada' => $this->faker->randomNumber(2, 50),
            'tip_evento' => $this->faker->randomElement(['E', 'P', 'D']),
            'txt_informacao' => $this->faker->optional()->paragraph(),
        ];
    }
}
