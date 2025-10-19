<?php

namespace Database\Factories;

use App\Models\Evento;
use Illuminate\Database\Eloquent\Factories\Factory;

class FichaFactory extends Factory
{
    protected $model = \App\Models\Ficha::class;

    public function definition(): array
    {
        return [
            'idt_evento' => Evento::factory(),
            'tip_genero' => $this->faker->randomElement(['M', 'F', 'O']),
            'nom_candidato' => $this->faker->name(),
            'nom_apelido' => $this->faker->firstName(),
            'dat_nascimento' => $this->faker->date('Y-m-d', '-12 years'),
            'eml_candidato' => $this->faker->safeEmail(),
            'tam_camiseta' => $this->faker->randomElement(['P', 'M', 'G', 'GG']),
            'ind_consentimento' => true,
            'ind_restricao' => false,
        ];
    }
}
