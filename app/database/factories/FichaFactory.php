<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class FichaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'idt_tipo_responsavel' => TipoResponsavelFactory::inRandomOrder()->first()?->id ?? TipoResponsavelFactory::factory(),
            'nom_responsavel' => $this->faker->name,
            'tel_responsavel' => $this->faker->phoneNumber,
            'nom_candidato' => $this->faker->name,
            'des_telefone' => $this->faker->phoneNumber,
            'des_endereco' => $this->faker->address,
            'dat_nascimento' => $this->faker->date(),
            'des_onde_estuda' => $this->faker->company,
            'des_mora_quem' => $this->faker->word,
            'tam_camiseta' => $this->faker->randomElement(['PP', 'P', 'M', 'G', 'GG']),
            'num_satisfacao' => $this->faker->numberBetween(0, 10),
            'ind_toca_instrumento' => $this->faker->boolean,
            'ind_aprovado' => $this->faker->boolean,
        ];
    }
}
