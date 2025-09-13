<?php

namespace Database\Factories;

use App\Models\Pessoa;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PessoaFactory extends Factory
{
    protected $model = Pessoa::class;

    public function definition(): array
    {
        return [
            'idt_usuario' => User::factory(),
            'idt_parceiro' => null,
            'nom_pessoa' => $this->faker->name(),
            'nom_apelido' => $this->faker->lastName(),
            'tip_genero' => $this->faker->randomElement(['M', 'F']),
            'tel_pessoa' => $this->faker->numerify('(##) #####-####'),
            'eml_pessoa' => $this->faker->unique()->safeEmail(),
            'des_endereco' => $this->faker->address(),
            'dat_nascimento' => $this->faker->date('Y-m-d', '-20 years'),
            'tam_camiseta' => $this->faker->randomElement(['P', 'M', 'G', 'GG']),
            'ind_toca_violao' => $this->faker->boolean(),
            'ind_consentimento' => $this->faker->boolean(),
            'ind_restricao' => $this->faker->boolean(),
        ];
    }
}
