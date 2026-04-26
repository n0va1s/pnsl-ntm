<?php

namespace Database\Seeders;

use App\Models\Equipe;
use App\Models\TipoMovimento;
use Database\Factories\EquipeFactory;
use Illuminate\Database\Seeder;

class EquipeVEMSeeder extends Seeder
{
    public function run(): void
    {
        // Guard idempotente — não duplica se ja existir dados
        if (Equipe::count() > 0) {
            return;
        }

        // Garantir que TipoMovimento VEM existe
        TipoMovimento::firstOrCreate(
            ['des_sigla' => 'VEM'],
            ['nom_movimento' => 'Encontro de Adolescentes com Cristo', 'dat_inicio' => '2000-07-01']
        );

        EquipeFactory::seedDefaults();
    }
}
