<?php

namespace Database\Seeders;

use App\Models\Pessoa;
use App\Models\PessoaFoto;
use App\Models\PessoaHabilidade;
use App\Models\PessoaSaude;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PessoaSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        Pessoa::factory()->count(50)->create();
        PessoaSaude::factory()->count(50)->create();
        PessoaFoto::factory()->count(50)->create();
        PessoaHabilidade::factory()->count(50)->create();
    }
}
