<?php

namespace Database\Seeders;

use App\Models\Gamificacao;
use App\Models\Participante;
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

        Participante::factory()->count(50)->create();
        Voluntario::factory()->count(50)->create();
        Trabalhador::factory()->count(50)->create();
        Gamificacao::factory()->count(50)->create();
    }
}
