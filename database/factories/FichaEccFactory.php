<?php

namespace Database\Factories;

use App\Enums\TamanhoCamiseta;
use App\Models\FichaEcc;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class FichaEccFactory extends Factory
{
    protected $model = FichaEcc::class;

    public function definition(): array
    {
        return [
            'idt_ficha' => null, // Será preenchido pelo Seeder para evitar recursividade
            'nom_conjuge' => $this->faker->name(),
            'tel_conjuge' => $this->faker->numerify('619########'),
            'dat_nascimento_conjuge' => Carbon::parse($this->faker->date('Y-m-d', '-20 years'))->format('Y-m-d'),
            'tam_camiseta_conjuge' => $this->faker->randomElement(TamanhoCamiseta::cases())->value,
        ];
    }
}
