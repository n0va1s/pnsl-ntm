<?php

namespace Database\Factories;

use App\Models\TipoResponsavel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class TipoResponsavelFactory extends Factory
{
    protected $model = TipoResponsavel::class;

    public function definition(): array
    {
        return [
            'des_responsavel' => $this->faker->randomElement(['Pai', 'Mãe', 'Avô', 'Avó', 'Madrinha', 'Padrinho', 'Outro']),
        ];
    }
}
