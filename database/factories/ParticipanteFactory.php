<?php

namespace Database\Factories;

use App\Models\Evento;
use App\Models\Participante;
use App\Models\Pessoa;
use Illuminate\Database\Eloquent\Factories\Factory;

class ParticipanteFactory extends Factory
{
    protected $model = Participante::class;

    public function definition(): array
    {
        return [
            'idt_pessoa' => Pessoa::factory(),
            'idt_evento' => Evento::inRandomOrder()->first()->idt_evento ?? Evento::factory(),
            'tip_cor_troca' => $this->faker->randomElement(['vermelha', 'azul', 'verde', 'amarela', 'laranja']),
        ];
    }
}
