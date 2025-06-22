<?php

namespace Database\Factories;

use App\Models\Habilidade;
use App\Models\Pessoa;
use App\Models\PessoaHabilidade;
use Illuminate\Database\Eloquent\Factories\Factory;

class PessoaHabilidadeFactory extends Factory
{
    protected $model = PessoaHabilidade::class;

    public function definition(): array
    {
        return [
            'idt_pessoa' => Pessoa::inRandomOrder()->first()->id ?? Pessoa::factory(),
            'idt_habilidade' =>Habilidade::factory(),
            'num_escala' => $this->faker->numberBetween(0, 5),
            'txt_complemento' => $this->faker->sentence,
        ];
    }
}
