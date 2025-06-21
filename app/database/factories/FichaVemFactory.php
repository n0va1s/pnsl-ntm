<?php

namespace Database\Factories;

use App\Models\Ficha;
use App\Models\TipoResponsavel;
use Illuminate\Database\Eloquent\Factories\Factory;

class FichaVemFactory extends Factory
{
    protected $model = \App\Models\FichaVem::class;

    public function definition(): array
    {
        return [
            'idt_ficha' => Ficha::factory(),
            'idt_falar_com' => TipoResponsavel::factory(),
            'des_onde_estuda' => $this->faker->company(),
            'des_mora_quem' => $this->faker->name(),
            'nom_pai' => $this->faker->optional()->name('male'),
            'tel_pai' => $this->faker->optional()->numerify('###########'),
            'nom_mae' => $this->faker->optional()->name('female'),
            'tel_mae' => $this->faker->optional()->numerify('##########'),
        ];
    }
}
