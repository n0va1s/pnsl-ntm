<?php

namespace Database\Seeders;

use App\Models\Pessoa;
use App\Models\PessoaFoto;
use App\Models\PessoaHabilidade;
use App\Models\PessoaSaude;
use App\Models\Presenca;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PessoaSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Pessoas sem parceiro
        Pessoa::factory(50)->create([
            'idt_parceiro' => null,
        ]);

        // Pessoas com parceiro
        Pessoa::factory(150)->create()->each(function ($pessoa) {
            // 50% de chance de ter um parceiro
            if (random_int(0, 1) === 1) {
                // Tenta encontrar um parceiro que não seja ela mesma e que não tenha parceiro ainda
                $parceiro = Pessoa::where('idt_pessoa', '!=', $pessoa->idt_pessoa)
                    ->whereNull('idt_parceiro')
                    ->inRandomOrder()
                    ->first();

                if ($parceiro) {
                    $pessoa->idt_parceiro = $parceiro->idt_pessoa;
                    $pessoa->save();

                    // Se você quer relação bidirecional
                    $parceiro->idt_parceiro = $pessoa->idt_pessoa;
                    $parceiro->save();
                }
            }
        });

        PessoaSaude::factory()->count(50)->create();
        PessoaFoto::factory()->count(50)->create();
    }
}
