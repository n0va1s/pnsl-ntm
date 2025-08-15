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
        // ! Apagar depois, apenas para testes

        User::factory()->create([
            'name' => 'Usuário',
            'email' => 'user@gmail.com',
            'role' => 'user',
            'password' => bcrypt('12345678'),
        ]);


        User::factory()->create([
            'name' => 'Coordenador',
            'email' => 'coord@gmail.com',
            'role' => 'coord',
            'password' => bcrypt('12345678'),
        ]);


        User::factory()->create([
            'name' => 'Administrador',
            'email' => 'admin@gmail.com',
            'role' => 'admin',
            'password' => bcrypt('12345678'),
        ]);


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
