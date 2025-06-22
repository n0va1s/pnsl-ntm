<?php

namespace Database\Seeders;

use App\Models\Habilidade;
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
            'Oração',
            'Emaús',
            'Secretaria',
            'Vendinha',
        ];

        foreach ($equipes as $equipe) {
            DB::table('tipo_equipe')->insert([
                'des_grupo' => $equipe,
            ]);
        }

        //Cria habilidades para equipes
        Habilidade::factory()->count(15)->create();
    }
}
