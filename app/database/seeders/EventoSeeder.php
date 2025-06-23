<?php

namespace Database\Seeders;

use App\Models\Evento;
use App\Models\TipoMovimento;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EventoSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        Evento::factory()->count(10)->create();
    }
}
