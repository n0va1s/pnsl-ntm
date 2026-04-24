<?php

namespace Database\Factories;

use App\Models\Equipe;
use App\Models\TipoMovimento;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class EquipeFactory extends Factory
{
    protected $model = Equipe::class;

    public function definition(): array
    {
        $nome = $this->faker->unique()->words(2, true);

        return [
            'idt_movimento' => TipoMovimento::where('des_sigla', 'VEM')->value('idt_movimento')
                ?? TipoMovimento::factory()->create(['des_sigla' => 'VEM'])->idt_movimento,
            'nom_equipe'    => ucfirst($nome),
            'des_slug'      => Str::slug($nome),
            'des_descricao' => $this->faker->sentence(),
            'ind_ativa'     => true,
        ];
    }

    /**
     * Retorna os dados das 11 equipes VEM padrão.
     * Nomes e slugs conforme tabela oficial REQUIREMENTS.md EQUIPE-03.
     */
    public function defaults(): array
    {
        $idtVEM = TipoMovimento::where('des_sigla', 'VEM')->value('idt_movimento');

        $nomes = [
            'Alimentação',
            'Bandinha',
            'Emaús',
            'Limpeza',
            'Oração',
            'Recepção',
            'Reportagem',
            'Sala',
            'Secretaria',
            'Troca de Ideias',
            'Vendinha',
        ];

        return array_map(fn ($n) => [
            'idt_movimento' => $idtVEM,
            'nom_equipe'    => $n,
            'des_slug'      => Str::slug($n),
            'des_descricao' => null,
            'ind_ativa'     => true,
        ], $nomes);
    }

    /**
     * Insere as 11 equipes VEM via firstOrCreate — idempotente.
     */
    public static function seedDefaults(): void
    {
        foreach ((new self)->defaults() as $data) {
            Equipe::firstOrCreate(
                ['idt_movimento' => $data['idt_movimento'], 'des_slug' => $data['des_slug']],
                $data
            );
        }
    }
}
