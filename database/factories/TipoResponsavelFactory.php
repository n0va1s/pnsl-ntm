<?php

namespace Database\Factories;

use App\Models\TipoResponsavel;
use Illuminate\Database\Eloquent\Factories\Factory;

class TipoResponsavelFactory extends Factory
{
    protected $model = TipoResponsavel::class;

    public function definition(): array
    {
        // Valores aleatórios para testes
        return [
            'des_responsavel' => $this->faker->randomElement([
                'Pai',
                'Mãe',
                'Avô',
                'Avó',
                'Tio',
                'Tia',
                'Padrinho',
                'Madrinha',
                'Outro(a)',
            ]),
        ];
    }

    /**
     * Retorna os responsáveis padrão
     */
    public function defaults(): array
    {
        return [
            ['des_responsavel' => 'Pai'],
            ['des_responsavel' => 'Mãe'],
            ['des_responsavel' => 'Avô'],
            ['des_responsavel' => 'Avó'],
            ['des_responsavel' => 'Tio'],
            ['des_responsavel' => 'Tia'],
            ['des_responsavel' => 'Padrinho'],
            ['des_responsavel' => 'Madrinha'],
            ['des_responsavel' => 'Outro(a)'],
        ];
    }

    /**
     * Popula todos os responsáveis padrão no banco
     */
    public static function seedDefaults(): void
    {
        foreach ((new self)->defaults() as $data) {
            TipoResponsavel::firstOrCreate($data);
        }
    }
}
