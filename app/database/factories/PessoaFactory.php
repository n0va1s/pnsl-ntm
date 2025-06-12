<?php

namespace Database\Factories;

use App\Models\Pessoa;
use Illuminate\Database\Eloquent\Factories\Factory;

class PessoaFactory extends Factory
{
    protected $model = Pessoa::class;

    public function definition(): array
    {
        return [
            'nom_pessoa' => $this->faker->name(),
            'des_telefone' => $this->faker->phoneNumber(),
            'des_endereco' => $this->faker->address(),
            'dat_nascimento' => $this->faker->date('Y-m-d', '-20 years'),
            'tam_camiseta' => $this->faker->randomElement(['P', 'M', 'G', 'GG']),
            'ind_toca_instrumento' => $this->faker->boolean(),
        ];
    }
}
