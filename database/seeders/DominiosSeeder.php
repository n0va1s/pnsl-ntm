<?php

namespace Database\Seeders;

use Database\Factories\TipoEquipeFactory;
use Database\Factories\TipoMovimentoFactory;
use Database\Factories\TipoResponsavelFactory;
use Database\Factories\TipoRestricaoFactory;
use Database\Factories\TipoSituacaoFactory;
use Illuminate\Database\Seeder;

class DominiosSeeder extends Seeder
{
    public function run(): void
    {
        TipoRestricaoFactory::seedDefaults();
        TipoSituacaoFactory::seedDefaults();
        TipoResponsavelFactory::seedDefaults();
        TipoMovimentoFactory::seedDefaults();
        TipoEquipeFactory::seedDefaults();
    }
}
