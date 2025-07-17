<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Contato;

class ContatoSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        Contato::factory()->count(100)->create();

    }
}
