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
            'idt_movimento' => $this->faker->numberBetween(1,3),
            'des_evento' => $this->faker->words(2, true) . ' VEM',
            'num_evento' => $this->faker->numberBetween(1, 99),
            'dat_inicio' => $dataInicio->format('Y-m-d'),
            'dat_termino' => $dataTermino->format('Y-m-d'),
            'ind_pos_encontro' => $this->faker->boolean(30),
        ];
    }
}
