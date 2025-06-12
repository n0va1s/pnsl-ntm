<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;

class TipoEquipeSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $equipes = [
            'Bandinha',
            'Recepção',
            'Reportagem',
            'Coordenação Geral',
            'Alimentação',
            'Sala',
            'Limpeza',
            'Liturgia',
            'Apoio Técnico',
            'Espiritualidade',
        ];

        foreach ($equipes as $equipe) {
            DB::table('tipo_equipe')->insert([
                'des_grupo' => $equipe,
            ]);
        }
    }
}
