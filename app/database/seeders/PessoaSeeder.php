<?php

namespace Database\Seeders;

use App\Models\Pessoa;
use App\Models\PessoaFoto;
// use App\Models\PessoaHabilidade;
use App\Models\PessoaSaude;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PessoaSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        Pessoa::factory()->count(15)->create();
        PessoaSaude::factory()->count(15)->create();
        PessoaFoto::factory()->count(15)->create();
        // PessoaHabilidade::factory()->count(15)->create();
    }
}
