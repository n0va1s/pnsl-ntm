<?php

namespace Database\Seeders;

use App\Models\TipoSituacao;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TipoSituacaoSeeder extends Seeder
{
    use WithoutModelEvents;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $situacoes = ['Cadastrada', 'Avaliada', 'Encaminhada', 'Contactada', 'Pendente', 'Aprovada', 'Cancelada'];
        foreach ($situacoes as $situacao) {
            TipoSituacao::firstOrCreate(['des_situacao' => $situacao]);
        }
    }
}
