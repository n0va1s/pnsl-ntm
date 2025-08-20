<?php

namespace Database\Factories;

use App\Models\TipoRestricao;
use Illuminate\Database\Eloquent\Factories\Factory;

class TipoRestricaoFactory extends Factory
{
    protected $model = TipoRestricao::class;

    public function definition(): array
    {
        // Valores aleatórios para testes
        return [
            'des_restricao' => $this->faker->word(),
            'tip_restricao' => $this->faker->randomElement(['INT', 'ALE', 'PNE', 'VEG']),
        ];
    }

    /**
     * Retorna as restrições padrão
     */
    public function defaults(): array
    {
        return [
            ['des_restricao' => 'Gluten', 'tip_restricao' => 'INT'],
            ['des_restricao' => 'Gluten', 'tip_restricao' => 'ALE'],
            ['des_restricao' => 'Remédio', 'tip_restricao' => 'ALE'],
            ['des_restricao' => 'Ovo', 'tip_restricao' => 'ALE'],
            ['des_restricao' => 'Castanhas', 'tip_restricao' => 'ALE'],
            ['des_restricao' => 'Frutos do Mar', 'tip_restricao' => 'ALE'],
            ['des_restricao' => 'Motor', 'tip_restricao' => 'PNE'],
            ['des_restricao' => 'Auditivo', 'tip_restricao' => 'PNE'],
            ['des_restricao' => 'Neurodivergente', 'tip_restricao' => 'PNE'],
            ['des_restricao' => 'Vegano', 'tip_restricao' => 'VEG'],
            ['des_restricao' => 'Vegeteariano', 'tip_restricao' => 'VEG'],
        ];
    }

    /**
     * Popula todas as restrições padrão no banco
     */
    public static function seedDefaults(): void
    {
        foreach ((new self())->defaults() as $data) {
            TipoRestricao::firstOrCreate($data);
        }
    }
}
