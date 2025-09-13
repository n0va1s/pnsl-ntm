<?php

namespace Database\Factories;

use App\Models\Contato;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContatoFactory extends Factory
{
    protected $model = Contato::class;

    public function definition(): array
    {
        $dataInicio = $this->faker->dateTimeBetween('-1 year', '+1 month');
        $dataTermino = (clone $dataInicio)->modify('+3 days');

        return [
            'idt_movimento' => $this->faker->numberBetween(1, 3),
            'dat_contato' => now(),
            'nom_contato' => $this->faker->name(),
            'eml_contato' => $this->faker->email(),
            'tel_contato' => $this->faker->phoneNumber(),
            'txt_mensagem' => $this->faker->text(),
        ];
    }
}
