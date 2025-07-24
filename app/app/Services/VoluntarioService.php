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
        // Lógica de validação personalizada (mínimo de 3 selecionadas e habilidade preenchida)
        $selectedTeams = [];
        foreach ($equipesData as $equipeId => $equipeData) {
            if (isset($equipeData['selecionado']) && $equipeData['selecionado'] === '1') {
                if (empty($equipeData['habilidade'])) {
                    throw ValidationException::withMessages([
                        'equipes.' . $equipeId . '.habilidade' => 'A descrição da habilidade é obrigatória para equipes selecionadas.',
                    ]);
                }
                $selectedTeams[$equipeId] = $equipeData['habilidade'];
            }
        }

        if (count($selectedTeams) < 3) {
            throw ValidationException::withMessages([
                'equipes' => 'Selecione ao menos 3 equipes para se voluntariar.',
            ]);
        }

        DB::transaction(function () use ($selectedTeams, $eventoId, $pessoa) {
            // Limpa candidaturas anteriores da pessoa para este evento, se necessário
            Voluntario::where('idt_pessoa', $pessoa->idt_pessoa)
                ->where('idt_evento', $eventoId)
                ->delete();

            // Salvar os voluntários para cada equipe selecionada
            foreach ($selectedTeams as $equipeId => $habilidade) {
                Voluntario::create([
                    'idt_pessoa' => $pessoa->idt_pessoa,
                    'idt_evento' => $eventoId,
                    'idt_equipe' => $equipeId,
                    'txt_habilidade' => $habilidade, // Usando txt_habilidade agora
                ]);
            }
        });
    }

    public function confirmacao(int $voluntarioId, int $equipeId, bool $isCoordenador = false, bool $isPrimeiraVez = false): void
    {
        $voluntario = Voluntario::find($voluntarioId);

        if (!$voluntario) {
            throw new \Exception('Voluntário não encontrado para confirmação.');
        }

        DB::transaction(function () use ($voluntario, $equipeId, $isCoordenador, $isPrimeiraVez) {
            $trabalhador = Trabalhador::updateOrCreate([
                'idt_pessoa' => $voluntario->idt_pessoa,
                'idt_evento' => $voluntario->idt_evento,
                'idt_equipe' => $equipeId
            ], [
                'ind_coordenador' => $isCoordenador,
                'ind_primeira_vez' => $isPrimeiraVez,
            ]);

            // Atualizar voluntário
            $voluntario->update([
                'idt_trabalhador' => $trabalhador->idt_trabalhador
            ]);
        });
    }
}
