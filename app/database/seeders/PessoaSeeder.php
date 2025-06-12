<?php

namespace Database\Seeders;

use App\Models\Pessoa;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PessoaSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        Pessoa::factory()->count(15)->create();
    }
}
