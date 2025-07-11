<?php

namespace Database\Seeders;

use App\Models\TipoEquipe;
use App\Models\TipoMovimento;
use App\Models\TipoResponsavel;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(10)->create();

        $this->call([
            TipoRestricaoSeeder::class,
            TipoSituacaoSeeder::class,
            TipoResponsavelSeeder::class,
            TipoMovimentoSeeder::class,
            TipoEquipeSeeder::class,
            EventoSeeder::class,
            PessoaSeeder::class,
            FichaSeeder::class,
            ParticipanteSeeder::class,
            TrabalhadorSeeder::class,
        ]);
    }
}
