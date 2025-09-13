<?php

namespace Database\Factories;

use App\Models\Presenca;
use App\Models\Participante;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Log;

class PresencaFactory extends Factory
{
    protected $model = Presenca::class;

    public function definition(): array
    {
        $participante = Participante::inRandomOrder()->first();

        if (!$participante) {
            // Este erro indica que ParticipanteFactory precisa ser executada antes no seeder.
            throw new \Exception('Nenhum participante encontrado para criar Presenca. Verifique a ordem dos seeders.');
        }

        // Gera uma data aleatória inicial.
        $randomDate = $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d');

        return [
            'idt_participante' => $participante->idt_participante,
            'dat_presenca' => $randomDate,
            'ind_presente' => $this->faker->boolean(),
        ];
    }

    /**
     * Garante que a combinação idt_participante e dat_presenca é única.
     * Isso é executado *antes* do registro ser persistido no banco.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterMaking(function (Presenca $presenca) {
            $attempts = 0;
            $maxAttempts = 100; // Limite de tentativas para evitar loop infinito

            // Loop para encontrar uma combinação única de participante e data
            while (
                Presenca::where('idt_participante', $presenca->idt_participante)
                ->where('dat_presenca', $presenca->dat_presenca)
                ->exists() && $attempts < $maxAttempts
            ) {
                // Se a combinação já existe, gera uma nova data
                $presenca->dat_presenca = $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d');
                $attempts++;
            }

            if ($attempts >= $maxAttempts) {
                // Se não conseguir encontrar uma data única após muitas tentativas,
                // pode indicar que o espaço de datas para este participante está esgotado
                // ou que há um problema na lógica de seeding.
                // Você pode logar um aviso ou lançar uma exceção aqui,
                // ou simplesmente permitir que o Eloquent tente e o erro de UNIQUE ocorra.
                // Para seeding, é melhor tentar evitar o erro.
                Log::warning("Não foi possível gerar uma data única para o participante {$presenca->idt_participante} após {$maxAttempts} tentativas.");
            }
        });
    }
}
