<?php

namespace Database\Factories;

use App\Models\TipoMovimento;
use Illuminate\Database\Eloquent\Factories\Factory;

class TipoMovimentoFactory extends Factory
{
    protected $model = TipoMovimento::class;

    public function definition(): array
    {
        return [
            'des_sigla' => $this->faker->unique()->lexify('???'),
            'nom_movimento' => $this->faker->sentence(3),
            'dat_inicio' => $this->faker->date('Y-m-d', 'now'),
        ];
    }

    /**
     * Retorna os movimentos fixos
     */
    public function defaults(): array
    {
        return [
            ['des_sigla' => 'ECC', 'nom_movimento' => 'Encontro de Casais com Cristo', 'dat_inicio' => '1980-01-01'],
            ['des_sigla' => 'VEM', 'nom_movimento' => 'Encontro de Adolescentes com Cristo', 'dat_inicio' => '2000-07-01'],
            ['des_sigla' => 'Segue-Me', 'nom_movimento' => 'Encontro de Jovens com Cristo', 'dat_inicio' => '1990-12-31'],
        ];
    }

    /**
     * Popula todos os movimentos padrão
     */
    public static function seedDefaults(): void
    {
        foreach ((new self)->defaults() as $data) {
            TipoMovimento::firstOrCreate($data);
        }
    }
}
