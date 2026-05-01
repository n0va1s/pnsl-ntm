<?php

namespace Database\Seeders;

use App\Enums\ComoSoube;
use App\Enums\Genero;
use App\Enums\TamanhoCamiseta;
use App\Models\Evento;
use App\Models\Ficha;
use App\Models\FichaEcc;
use App\Models\FichaSGM;
use App\Models\FichaVem;
use App\Models\Pessoa;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FichaSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Ficha::factory()->count(1000)->create();
        // FichaVem::factory()->count(100)->create();
        // FichaSGM::factory()->count(100)->create();
        // FichaEcc::factory()->count(100)->create();

        // Pegamos os IDs necessários
        $eventos = Evento::pluck('idt_evento');
        $pessoas = Pessoa::pluck('idt_pessoa');
        $user = User::first()?->id;

        if ($eventos->isEmpty() || $pessoas->isEmpty()) {
            $this->command->error('Pessoas ou Eventos não encontrados.');

            return;
        }

        for ($i = 0; $i < 300; $i++) {
            // CORREÇÃO: Usar os Enums corretos para evitar o ValueError
            $ficha = Ficha::create([
                'idt_evento' => $eventos->random(),
                'idt_pessoa' => $pessoas->random(),
                'tip_genero' => fake()->randomElement(Genero::cases()),
                'nom_candidato' => fake()->name,
                'nom_apelido' => fake()->firstName,
                'dat_nascimento' => fake()->date('Y-m-d', '-15 years'),
                'tel_candidato' => fake()->phoneNumber,
                'eml_candidato' => fake()->safeEmail,
                'des_endereco' => fake()->address,
                // Sorteia um caso real do Enum em vez de uma palavra aleatória
                'tam_camiseta' => fake()->randomElement(TamanhoCamiseta::cases()),
                'tip_como_soube' => fake()->randomElement(ComoSoube::cases()),
                'ind_catolico' => fake()->boolean,
                'ind_toca_instrumento' => fake()->boolean,
                'ind_consentimento' => true,
                'ind_aprovado' => fake()->boolean(80),
                'ind_restricao' => fake()->boolean(20),
                'usu_inclusao' => $user,
                'txt_observacao' => fake()->sentence,
            ]);

            // Cria a especialização sem disparar o pai novamente
            if ($i < 100) {
                FichaVem::factory()->create(['idt_ficha' => $ficha->idt_ficha]);
            } elseif ($i < 200) {
                FichaSGM::factory()->create(['idt_ficha' => $ficha->idt_ficha]);
            } else {
                FichaEcc::factory()->create(['idt_ficha' => $ficha->idt_ficha]);
            }
        }

        $this->command->info('Fichas criadas com sucesso!');
    }
}
