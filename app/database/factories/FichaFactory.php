<?php

namespace Database\Factories;

use App\Models\Ficha;
use App\Models\TipoResponsavel;
use Illuminate\Database\Eloquent\Factories\Factory;

class FichaFactory extends Factory
{
    protected $model = Ficha::class;

    public function definition(): array
    {
        return [
            'idt_tipo_responsavel' => TipoResponsavel::inRandomOrder()->first()->idt_responsavel ?? 1,
            'nom_responsavel' => $this->faker->name(),
            'tel_responsavel' => $this->faker->phoneNumber(),
            'nom_candidato' => $this->faker->name(),
            'des_telefone' => $this->faker->phoneNumber(),
            'des_endereco' => $this->faker->address(),
            'dat_nascimento' => $this->faker->date('Y-m-d', '-10 years'),
            'des_onde_estuda' => $this->faker->company(),
            'des_mora_quem' => $this->faker->name(),
            'tam_camiseta' => $this->faker->randomElement(['P', 'M', 'G', 'GG']),
            'num_satisfacao' => $this->faker->numberBetween(0, 10),
            'ind_toca_instrumento' => $this->faker->boolean(),
            'ind_aprovado' => $this->faker->boolean(70),
        ];
    }
}
