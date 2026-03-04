<?php

namespace Database\Seeders;

use App\Models\Ficha;
use App\Models\FichaEcc;
use App\Models\FichaVem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FichaSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Ficha::factory()->count(1000)->create();

        $vem = FichaVem::factory()->count(1000)->make()->toArray();
        FichaVem::insert($vem);

        $ecc = FichaEcc::factory()->count(1000)->make()->toArray();
        FichaEcc::insert($ecc);
    }
}
