<?php

namespace Database\Seeders;

use App\Models\TipoMovimento;
use Database\Factories\TipoEquipeFactory;
use Database\Factories\TipoMovimentoFactory;
use Database\Factories\TipoResponsavelFactory;
use Database\Factories\TipoRestricaoFactory;
use Illuminate\Database\Seeder;

class DominiosSeeder extends Seeder
{
    public function run(): void
    {
        if (TipoMovimento::count() > 0) {
            return;
        }
        TipoRestricaoFactory::seedDefaults();
        TipoResponsavelFactory::seedDefaults();
        TipoMovimentoFactory::seedDefaults();
        TipoEquipeFactory::seedDefaults();
    }
}
