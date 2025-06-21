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
            'nom_apelido' => $this->faker->lastName(),
            'tip_genero' => $this->faker->randomElement(['M', 'F']), // Masculino, Feminino
            'tel_pessoa' => $this->faker->phoneNumber(),
            'eml_pessoa' => $this->faker->unique()->safeEmail(),
            'des_endereco' => $this->faker->address(),
            'dat_nascimento' => $this->faker->date('Y-m-d', '-20 years'),
            'tam_camiseta' => $this->faker->randomElement(['P', 'M', 'G', 'GG']),
            'ind_toca_instrumento' => $this->faker->boolean(),
            'ind_consentimento' => $this->faker->randomElement(['sim', 'não']),
        ];
    }
}
