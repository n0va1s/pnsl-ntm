<?php

namespace Database\Seeders;

use App\Models\Contato;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContatoSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        Contato::factory()->count(500)->create();
    }
}
