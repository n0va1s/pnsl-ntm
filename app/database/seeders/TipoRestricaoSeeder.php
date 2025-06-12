<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoRestricao;

class TipoRestricaoSeeder extends Seeder
{
    public function run(): void
    {
        $restricoes = [
            ['Gluten', 'INT'],
            ['Gluten', 'ALE'],
            ['Ovo', 'ALE'],
            ['Castanhas', 'ALE'],
            ['Frutos do Mar', 'ALE'],
            ['Motor', 'PNE'],
            ['Auditivo', 'PNE'],
            ['Neurodivergente', 'PNE'],
        ];

        foreach ($restricoes as [$descricao, $tipo]) {
            TipoRestricao::firstOrCreate([
                'des_restricao' => $descricao,
                'tip_restricao' => $tipo,
            ]);
        }
    }
}
