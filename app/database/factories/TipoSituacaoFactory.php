<?php

namespace Database\Factories;

use App\Models\TipoSituacao;
use Illuminate\Database\Eloquent\Factories\Factory;

class TipoSituacaoFactory extends Factory
{
    protected $model = TipoSituacao::class;

    public function definition(): array
    {
        // Caso precise gerar valores aleatórios para testes
        return [
            'des_situacao' => $this->faker->unique()->word(),
        ];
    }

    /**
     * Retorna as situações padrão
     */
    public function defaults(): array
    {
        return [
            ['des_situacao' => 'Cadastrada'],
            ['des_situacao' => 'Avaliada'],
            ['des_situacao' => 'Encaminhada'],
            ['des_situacao' => 'Contactada'],
            ['des_situacao' => 'Pendente'],
            ['des_situacao' => 'Paga'],
        ];
    }

    /**
     * Popula todas as situações padrão no banco
     */
    public static function seedDefaults(): void
    {
        foreach ((new self())->defaults() as $data) {
            TipoSituacao::firstOrCreate($data);
        }
    }
}
