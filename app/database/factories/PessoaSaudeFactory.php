<?php

namespace Database\Factories;

use App\Models\Pessoa;
use App\Models\PessoaSaude;
use App\Models\TipoRestricao;
use Illuminate\Database\Eloquent\Factories\Factory;

class PessoaSaudeFactory extends Factory
{
    protected $model = PessoaSaude::class;

    public function definition(): array
    {
        return [
            'idt_pessoa' => Pessoa::inRandomOrder()->first()->id ?? Pessoa::factory(),
            'idt_restricao' => TipoRestricao::inRandomOrder()->first()->idt_restricao,
            'ind_remedio_regular' => $this->faker->boolean(),
            'txt_complemento' => $this->faker->optional()->sentence,
        ];
    }
}
