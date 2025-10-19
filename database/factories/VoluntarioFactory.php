<?php

namespace Database\Factories;

use App\Models\Evento;
use App\Models\Pessoa;
use App\Models\TipoEquipe;
use App\Models\Trabalhador;
use App\Models\Voluntario;
use Illuminate\Database\Eloquent\Factories\Factory;

class VoluntarioFactory extends Factory
{
    protected $model = Voluntario::class;

    public function definition(): array
    {
        return [
            'idt_pessoa' => Pessoa::inRandomOrder()->first()->idt_pessoa ?? Pessoa::factory(),
            'idt_evento' => Evento::inRandomOrder()->first()->idt_evento ?? Evento::factory(),
            'idt_equipe' => TipoEquipe::inRandomOrder()->first()->idt_equipe ?? TipoEquipe::factory(),
            'idt_trabalhador' => Trabalhador::inRandomOrder()->first()->idt_trabalhador ?? null,
            'txt_habilidade' => $this->faker->paragraph(3, true),
        ];
    }
}
