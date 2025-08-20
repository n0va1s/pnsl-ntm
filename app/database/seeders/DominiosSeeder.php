<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Factories\TipoEquipeFactory;
use Database\Factories\TipoMovimentoFactory;
use Database\Factories\TipoResponsavelFactory;
use Database\Factories\TipoRestricaoFactory;
use Database\Factories\TipoSituacaoFactory;

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
