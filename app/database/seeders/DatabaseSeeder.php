<?php

namespace Database\Seeders;

use App\Models\User;
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
            EnvolvidoSeeder::class,
            ContatoSeeder::class,
        ]);
    }
}
