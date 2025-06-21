<?php

namespace Database\Factories;

use App\Models\Ficha;
use Illuminate\Database\Eloquent\Factories\Factory;

class FichaEccFactory extends Factory
{
    protected $model = \App\Models\FichaEcc::class;

    public function definition(): array
    {
        return [
            'idt_ficha' => Ficha::factory(),
            'nom_conjuge' => $this->faker->name(),
            'nom_apelido_conjuge' => $this->faker->optional()->firstName(),
            'tel_conjuge' => $this->faker->numerify('###########'),
            'dat_nascimento_conjuge' => $this->faker->date('Y-m-d', '-18 years'),
            'tam_camiseta_conjuge' => $this->faker->randomElement(['P', 'M', 'G', 'GG']),
        ];
    }
}
