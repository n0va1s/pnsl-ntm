<?php

namespace Database\Factories;

use App\Models\Evento;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventoFactory extends Factory
{
    protected $model = Evento::class;

    public function definition(): array
    {
        $dataInicio = $this->faker->dateTimeBetween('-1 year', '+1 month');
        $dataTermino = (clone $dataInicio)->modify('+3 days');

        return [
            'des_evento' => $this->faker->words(2, true) . ' VEM',
            'num_evento' => $this->faker->numberBetween(1, 99),
            'dat_inicio' => $dataInicio->format('Y-m-d'),
            'dat_termino' => $dataTermino->format('Y-m-d'),
            'val_trabalhador' => $this->faker->randomFloat(2, 45),
            'val_venista' => $this->faker->randomFloat(2, 50),
            'val_camiseta' => $this->faker->randomFloat(2, 40),
            'ind_pos_encontro' => $this->faker->boolean(30),
        ];
    }
}
