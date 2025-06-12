<?php

namespace Database\Factories;

use App\Models\Trabalhador;
use App\Models\Pessoa;
use App\Models\Evento;
use App\Models\TipoEquipe;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrabalhadorFactory extends Factory
{
    protected $model = Trabalhador::class;

    public function definition(): array
    {
        return [
            'idt_pessoa' => Pessoa::inRandomOrder()->first()->idt_pessoa ?? Pessoa::factory(),
            'idt_evento' => Evento::inRandomOrder()->first()->idt_evento ?? Evento::factory(),
            'idt_equipe' => TipoEquipe::inRandomOrder()->first()->idt_equipe ?? 1,
            'ind_recomendado' => $this->faker->boolean(80),
            'ind_lideranca' => $this->faker->boolean(40),
            'ind_destaque' => $this->faker->boolean(30),
            'ind_coordenador' => $this->faker->boolean(20),
        ];
    }
}
