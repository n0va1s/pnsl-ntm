<?php

namespace App\Services;

use App\Models\Pessoa;
use App\Models\Voluntario;
use App\Models\Trabalhador;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB; // Para transações, se necessário

class VoluntarioService
{
    public function candidatura(array $equipesData, int $eventoId, Pessoa $pessoa): void
    {

        $equipes = [];
        foreach ($equipesData as $id => $texto) {
            if (isset($texto['selecionado']) && $texto['selecionado'] === '1') {
                $habilidade = $texto['habilidade'] ?? '';

                if (empty($habilidade)) {
                    throw ValidationException::withMessages([
                        'equipes' => 'A descrição da habilidade é obrigatória para equipes selecionadas.',
                    ]);
                } elseif (strlen($habilidade) <= 5) {
                    throw ValidationException::withMessages([
                        'equipes' => 'A habilidade deve ter mais de 5 caracteres.',
                    ]);
                } elseif (preg_match('/(.)\1{4,}/', $habilidade)) { //obrigado gemini
                    throw ValidationException::withMessages([
                        'equipes' => 'A habilidade não pode conter sequências de caracteres repetidos (ex: "aaaaa" ou ".....").',
                    ]);
                }
                $equipes[$id] = $habilidade;
            }
        }
        $qtd = count($equipes);
        if ($qtd < 1 || $qtd > 3) {
            $message = '';
            if ($qtd < 1) {
                $message = 'Você deve selecionar ao menos 1 equipe.';
            } elseif ($qtd > 3) {
                $message = 'Você pode selecionar no máximo 3 equipes.';
            }
            throw ValidationException::withMessages([
                'equipes' => $message,
            ]);
        }

        DB::transaction(function () use ($equipes, $eventoId, $pessoa) {
            // Limpa candidaturas anteriores da pessoa para este evento, se necessário
            Voluntario::where('idt_pessoa', $pessoa->idt_pessoa)
                ->where('idt_evento', $eventoId)
                ->delete();

            // Salvar os voluntários para cada equipe selecionada
            foreach ($equipes as $equipeId => $habilidade) {
                Voluntario::create([
                    'idt_pessoa' => $pessoa->idt_pessoa,
                    'idt_evento' => $eventoId,
                    'idt_equipe' => $equipeId,
                    'txt_habilidade' => $habilidade,
                ]);
            }
        });
    }

    public function confirmacao(
        int $voluntarioId,
        int $equipeId,
        bool $isCoordenador = false,
        bool $isPrimeiraVez = false
    ): Voluntario {
        $voluntario = Voluntario::find($voluntarioId);

        DB::transaction(function () use (
            $voluntario,
            $equipeId,
            $isCoordenador,
            $isPrimeiraVez
        ) {

            $trabalhador = Trabalhador::updateOrCreate([
                'idt_pessoa' => $voluntario->idt_pessoa,
                'idt_evento' => $voluntario->idt_evento,
                'idt_equipe' => $equipeId
            ], [
                'ind_coordenador' => $isCoordenador,
                'ind_primeira_vez' => $isPrimeiraVez,
            ]);

            // Atualiza todos as equipes que o voluntário se inscreveu para este evento
            Voluntario::where('idt_pessoa', $voluntario->idt_pessoa)
                ->where('idt_evento', $voluntario->idt_evento)
                ->update([
                    'idt_trabalhador' => $trabalhador->idt_trabalhador
                ]);
        });

        return $voluntario;
    }
}
