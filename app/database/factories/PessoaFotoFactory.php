<?php

namespace Database\Factories;

use App\Models\Pessoa;
use App\Models\PessoaFoto;
use Illuminate\Database\Eloquent\Factories\Factory;

class PessoaFotoFactory extends Factory
{
    protected $model = PessoaFoto::class;

    public function definition(): array
    {
        return [
            'idt_pessoa' => Pessoa::inRandomOrder()->first()->id ?? Pessoa::factory(),
            'med_foto' => 'fotos/' . $this->faker->uuid . '.jpg', // caminho fictício
        ];
    }
}
