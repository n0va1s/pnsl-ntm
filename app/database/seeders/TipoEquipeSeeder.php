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
            'Alimentação' => 2,
            'Bandinha' => 2,
            'Coordenação Geral' => 2,
            'Emaús' => 2,
            'Limpeza' => 2,
            'Oração' => 2,
            'Recepção' => 2,
            'Reportagem' => 2,
            'Sala' => 2,
            'Secretaria' => 2,
            'Vendinha' => 2,
            'Visitação' => 3,
            'Mini-Mercado' => 3,
            'Estacionamento' => 3,
            'Sala' => 3,
            'Ligação' => 3,
            'Alimentação' => 3,
            'Equipe ECC 1' => 1,
            'Equipe ECC 2' => 1,
            'Equipe ECC 3' => 1,
            'Equipe ECC 4' => 1,
            'Equipe ECC 5' => 1,
        ];

        foreach ($equipes as $des_grupo => $idt_movimento) {
            DB::table('tipo_equipe')->insert([
                'des_grupo' => $des_grupo,
                'idt_movimento' => $idt_movimento,
            ]);
        }

        // TODO: Refatorar a criação de Habilidades para um HabilidadeSeeder próprio
        // Obs.: está violando o SRP (Single Responsibility Principle)
        //Cria habilidades para equipes
        //Habilidade::factory()->count(15)->create();
    }
}
