<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(10)->create();
        Schema::disableForeignKeyConstraints();
        $this->call([
            DominiosSeeder::class,
            EquipeVEMSeeder::class,  // Fase 1: 11 equipes VEM — depende de DominiosSeeder (tipo_movimento)
            EventoSeeder::class,
            PessoaSeeder::class,
            FichaSeeder::class,
            EnvolvidoSeeder::class,
            ContatoSeeder::class,
        ]);
        Schema::enableForeignKeyConstraints();
    }
}
