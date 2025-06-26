<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Evento;
use App\Models\Presenca;
use app\Models\Participante;
use Carbon\CarbonPeriod;

class PresencaSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        Presenca::factory()->count(10)->create();

    }
}
