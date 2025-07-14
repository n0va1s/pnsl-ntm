<?php

namespace Database\Factories;

use App\Models\Voluntario;
use App\Models\Pessoa;
use App\Models\Evento;
use App\Models\TipoEquipe;
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
            'txt_habilidade' => $this->faker->paragraph(3, true),
        ];
    }
}
