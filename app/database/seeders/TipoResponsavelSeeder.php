<?php

namespace Database\Seeders;

use App\Models\TipoResponsavel;
use Illuminate\Database\Seeder;

class TipoResponsavelSeeder extends Seeder
{
    public function run(): void
    {
        $responsaveis = ['Pai', 'Mãe', 'Avô', 'Avó', 'Madrinha', 'Padrinho', 'Outro'];

        foreach ($responsaveis as $responsavel) {
            TipoResponsavel::firstOrCreate([
                'des_responsavel' => $responsavel
            ]);
        }
    }
}
