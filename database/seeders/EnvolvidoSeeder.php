<?php

namespace Database\Seeders;

use App\Models\Gamificacao;
use App\Models\Participante;
use App\Models\Presenca;
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

        Participante::factory()->count(200)->create();
        // Presenca::factory()->count(200)->create();
        // Estava apresentando erro de unique constraint violation, então optei por criar as presenças manualmente
        $participantes = Participante::all();
        foreach ($participantes as $p) {
            Presenca::firstOrCreate([
                'idt_participante' => $p->idt_participante,
                'dat_presenca' => now()->format('Y-m-d'),
            ], [
                'ind_presente' => rand(0, 1),
            ]);
        }
        Voluntario::factory()->count(200)->create();
        Trabalhador::factory()->count(200)->create();
        Gamificacao::factory()->count(200)->create();
    }
}
