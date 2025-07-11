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
        Ficha::factory()->count(20)->create();
        FichaVem::factory()->count(20)->create();
        FichaEcc::factory()->count(20)->create();
    }
}
