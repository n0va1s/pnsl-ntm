<?php

namespace Database\Seeders;

use App\Models\Evento;
use App\Models\EventoFoto;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EventoSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        //Evento::factory()->count(10)->create(); // as factories filhas ja criam eventos
        EventoFoto::factory()->count(50)->create();
    }
}
