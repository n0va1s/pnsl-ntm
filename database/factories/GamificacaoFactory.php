<?php

namespace Database\Factories;

use App\Models\Gamificacao;
use App\Models\Participante;
use App\Models\Pessoa;
use App\Models\Trabalhador;
use Illuminate\Database\Eloquent\Factories\Factory;

class GamificacaoFactory extends Factory
{
    protected $model = Gamificacao::class;

    public function definition(): array
    {
        $origemType = $this->faker->randomElement([Trabalhador::class, Participante::class]);

        $origemId = $origemType::inRandomOrder()->first()?->getKey() ?? $origemType::factory();

        return [
            'idt_pessoa' => Pessoa::inRandomOrder()->first()?->idt_pessoa ?? Pessoa::factory(),
            'qtd_pontos' => $this->faker->randomElement([5, 10, 15, 20, -5]),
            'des_motivo' => $this->faker->sentence(4),
            'origem_type' => $origemType,
            'origem_id' => $origemId,
            'created_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ];
    }

    /**
     * Estado para pontos de bonificação (Trabalho)
     */
    public function trabalho(): static
    {
        return $this->state(fn (array $attributes) => [
            'qtd_pontos' => 10,
            'des_motivo' => 'Bônus por trabalho em evento',
            'origem_type' => Trabalhador::class,
        ]);
    }

    /**
     * Estado para pontos de participação
     */
    public function participacao(): static
    {
        return $this->state(fn (array $attributes) => [
            'qtd_pontos' => 3,
            'des_motivo' => 'Participação em Pós-VEM',
            'origem_type' => Participante::class,
        ]);
    }
}
