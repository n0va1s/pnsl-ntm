<?php

namespace Database\Factories;

use App\Models\Evento;
use App\Models\Pessoa;
use App\Models\TipoEquipe;
use App\Models\Trabalhador;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrabalhadorFactory extends Factory
{
    protected $model = Trabalhador::class;

    public function definition(): array
    {
        return [
            'idt_pessoa' => Pessoa::factory(),
            'idt_evento' => Evento::factory(),
            'idt_equipe' => TipoEquipe::factory(),
            'ind_coordenador' => $this->faker->boolean(20),
            'ind_primeira_vez' => $this->faker->boolean(50),
            'ind_avaliacao' => $this->faker->boolean(30),
            'ind_recomendado' => $this->faker->boolean(80),
            'ind_lideranca' => $this->faker->boolean(40),
            'ind_destaque' => $this->faker->boolean(30),
            'ind_camiseta_pediu' => $this->faker->boolean(),
            'ind_camiseta_pagou' => $this->faker->boolean(),
        ];
    }
}
