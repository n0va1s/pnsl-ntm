<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoMovimento;

class TipoMovimentoSeeder extends Seeder
{
    public function run(): void
    {
        $movimentos = [
            ['ECC', 'Encontro de Casais com Cristo', '1980-01-01'],
            ['VEM', 'Encontro de Adolescentes com Cristo', '2000-07-01'],
            ['Segue-Me', 'Encontro de Jovens com Cristo', '1990-12-31'],
            
        ];

        foreach ($movimentos as [$sigla, $descricao, $data]) {
            TipoMovimento::firstOrCreate([
                'des_sigla' => $sigla,
                'nom_movimento' => $descricao,
                'dat_inicio' => $data
            ]);
        }
    }
}
