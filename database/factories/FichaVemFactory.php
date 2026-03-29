<?php

namespace Database\Factories;

use App\Models\Ficha;
use App\Models\FichaVem;
use App\Models\TipoResponsavel;
use Illuminate\Database\Eloquent\Factories\Factory;

class FichaVemFactory extends Factory
{
    protected $model = FichaVem::class;

    public function definition(): array
    {
        return [
            'idt_ficha' => Ficha::factory(),
            'idt_falar_com' => TipoResponsavel::inRandomOrder()->first()?->idt_responsavel,
            'des_onde_estuda' => $this->faker->company(),
            'des_mora_quem' => $this->faker->words(3, true),
        ];
    }
}
