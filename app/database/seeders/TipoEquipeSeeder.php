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
            'Alimentação',
            'Bandinha',
            'Coordenação Geral',
            'Emaús',
            'Limpeza',
            'Oração',
            'Recepção',
            'Reportagem',
            'Sala',
            'Secretaria',
            'Vendinha',
        ];

        foreach ($equipes as $equipe) {
            DB::table('tipo_equipe')->insert([
                'des_grupo' => $equipe,
                'idt_movimento' => 2, //VEM 
            ]);
        }

        //Cria habilidades para equipes
        Habilidade::factory()->count(15)->create();
    }
}
