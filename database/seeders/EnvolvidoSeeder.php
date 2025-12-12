<?php

namespace Database\Seeders;

use App\Models\Evento;
use App\Models\Participante;
use App\Models\Pessoa;
use App\Models\Presenca;
use App\Models\TipoEquipe;
use App\Models\Trabalhador;
use App\Models\Voluntario;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

// Participante, Voluntario e Trabalhador
class EnvolvidoSeeder extends Seeder
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
            ]);
        }
        Participante::factory()->count(200)->create();
        Presenca::factory()->count(200)->create();
        Voluntario::factory()->count(200)->create();
        Trabalhador::factory()->count(200)->create();
    }
}
