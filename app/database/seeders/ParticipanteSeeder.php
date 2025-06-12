<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Pessoa;
use App\Models\Evento;
use App\Models\Participante;

class ParticipanteSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Garante que existam pessoas e eventos
        if (Pessoa::count() === 0 || Evento::count() === 0) {
            $this->command->warn('Pessoas ou Eventos não encontrados. Rodando PessoaSeeder e EventoSeeder...');
            $this->call([
                PessoaSeeder::class,
                EventoSeeder::class,
            ]);
        }

        // Gera participantes usando a factory
        Participante::factory()->count(15)->create();
    }
}
