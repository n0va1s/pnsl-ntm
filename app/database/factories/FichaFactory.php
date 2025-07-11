<?php

namespace Database\Factories;

use App\Models\Evento;
use App\Models\Pessoa;
use Illuminate\Database\Eloquent\Factories\Factory;

class FichaFactory extends Factory
{
    protected $model = \App\Models\Ficha::class;

    public function definition(): array
    {
        return [
            'idt_evento' => Evento::inRandomOrder()->first()->idt_evento,
            'idt_pessoa' => Pessoa::inRandomOrder()->first()->idt_pessoa,
            'tip_genero' => $this->faker->randomElement(['M', 'F', 'O']),
            'nom_candidato' => $this->faker->name(),
            'nom_apelido' => $this->faker->firstName(),
            'dat_nascimento' => $this->faker->date('Y-m-d', '-12 years'),
            'tel_candidato' => $this->faker->optional()->phoneNumber(),
            'eml_candidato' => $this->faker->safeEmail(),
            'des_endereco' => $this->faker->optional()->address(),
            'tam_camiseta' => $this->faker->randomElement(['P', 'M', 'G', 'GG']),
            'tip_como_soube' => $this->faker->optional()->randomElement(['IND', 'PAD', 'OUT']),
            'ind_catolico' => $this->faker->boolean(80),
            'ind_toca_instrumento' => $this->faker->boolean(30),
            'ind_consentimento' => $this->faker->boolean(95),
            'ind_aprovado' => $this->faker->boolean(60),
            'ind_restricao' => $this->faker->boolean(60),
            'txt_observacao' => $this->faker->optional()->sentence(),
        ];
    }
}
