<?php

namespace Database\Factories;

use App\Models\Ficha;
use App\Models\TipoResponsavel;
use Illuminate\Database\Eloquent\Factories\Factory;

class FichaSGMFactory extends Factory
{
    protected $model = \App\Models\FichaSGM::class;

    public function definition(): array
    {
        return [
            'idt_ficha' => Ficha::factory(),
            'idt_falar_com' => TipoResponsavel::factory(),
            'des_mora_quem' => $this->faker->words(3, true),
            'nom_pai' => $this->faker->name('male'),
            'tel_pai' => $this->faker->numerify('###########'),
            'nom_mae' => $this->faker->name('female'),
            'tel_mae' => $this->faker->numerify('###########'),
        ];
    }
}
