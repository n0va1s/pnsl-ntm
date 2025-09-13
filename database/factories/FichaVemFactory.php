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
            'idt_ficha'       => Ficha::factory(),
            'idt_falar_com'   => TipoResponsavel::factory(), // garante dado válido
            'des_onde_estuda' => $this->faker->company(),
            'des_mora_quem'   => $this->faker->words(3, true),
        ];
    }
}
