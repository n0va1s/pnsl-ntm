<?php

namespace Database\Factories;

use App\Models\TipoSituacao;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class TipoSituacaoFactory extends Factory
{
    protected $model = TipoSituacao::class;

    public function definition(): array
    {
        return [
            'des_situacao' => $this->faker->randomElement(['Cadastrada', 'Avaliada', 'Visitada', 'Aprovada', 'Cancelada']),
        ];
    }
}
