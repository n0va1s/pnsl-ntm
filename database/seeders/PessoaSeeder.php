<?php

namespace Database\Seeders;

use App\Models\Pessoa;
use App\Models\PessoaFoto;
use App\Models\PessoaSaude;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PessoaSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Criar pessoas sem parceiro em massa (Bulk Insert)
        // Usamos factory()->make() para gerar os dados na memória sem salvar um a um
        $pessoasSimples = Pessoa::factory(500)->make(['idt_parceiro' => null])->toArray();

        // Dividimos em pedaços (chunks) para não estourar o limite de placeholders do SQLite/MySQL
        foreach (array_chunk($pessoasSimples, 100) as $chunk) {
            Pessoa::insert($chunk);
        }

        // 2. Criar pessoas que terão parceiros
        // Geramos 150 pessoas extras
        Pessoa::factory(150)->create();

        // 3. Lógica de Pareamento Otimizada
        // Em vez de inRandomOrder() dentro de um loop, pegamos os IDs e embaralhamos na memória
        $pessoasSemParceiro = Pessoa::whereNull('idt_parceiro')->pluck('idt_pessoa')->toArray();
        shuffle($pessoasSemParceiro);

        DB::transaction(function () use ($pessoasSemParceiro) {
            // Pegamos pares do array embaralhado (Ex: [1, 2], [3, 4]...)
            while (count($pessoasSemParceiro) >= 2) {
                $id1 = array_pop($pessoasSemParceiro);
                $id2 = array_pop($pessoasSemParceiro);

                // Atualização direta via Query Builder é muito mais rápida que via Model
                DB::table('pessoa')->where('idt_pessoa', $id1)->update(['idt_parceiro' => $id2]);
                DB::table('pessoa')->where('idt_pessoa', $id2)->update(['idt_parceiro' => $id1]);
            }
        });

        // 4. Outros dados relacionados (Bulk Insert)
        $saude = PessoaSaude::factory(50)->make()->toArray();
        PessoaSaude::insert($saude);

        $fotos = PessoaFoto::factory(50)->make()->toArray();
        PessoaFoto::insert($fotos);
    }
}
