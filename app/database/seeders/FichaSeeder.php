<?php

namespace Database\Seeders;

use App\Models\Ficha;
use App\Models\FichaVem;
use App\Models\FichaEcc;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\TipoResponsavel;

class FichaSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Certifique-se que existam responsáveis antes
        if (TipoResponsavel::count() === 0) {
            TipoResponsavel::insert([
                ['des_responsavel' => 'Pai'],
                ['des_responsavel' => 'Mãe'],
                ['des_responsavel' => 'Avô'],
                ['des_responsavel' => 'Avó'],
                ['des_responsavel' => 'Madrinha'],
                ['des_responsavel' => 'Padrinho'],
                ['des_responsavel' => 'Outro'],
            ]);
        }

        Ficha::factory()->count(10)->create();
        FichaVem::factory()->count(10)->create();
        FichaEcc::factory()->count(10)->create();
    }
}
