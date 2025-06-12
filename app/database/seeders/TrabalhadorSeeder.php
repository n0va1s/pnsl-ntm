<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Pessoa;
use App\Models\Evento;
use App\Models\TipoEquipe;
use App\Models\Trabalhador;

class TrabalhadorSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Garante que existam pessoas, eventos e equipes
        if (Pessoa::count() === 0 || Evento::count() === 0 || TipoEquipe::count() === 0) {
            $this->command->warn('Dependências faltando. Rodando PessoaSeeder, EventoSeeder e TipoEquipeSeeder...');
            $this->call([
                PessoaSeeder::class,
                EventoSeeder::class,
                TipoEquipeSeeder::class,
            ]);
        }

        // Gera trabalhadores usando a factory
        Trabalhador::factory()->count(20)->create();
    }
}
