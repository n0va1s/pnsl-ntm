<?php

namespace App\Services;

use App\Models\Pessoa;
use App\Models\Trabalhador;
use App\Models\Voluntario;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VoluntarioService
{
    /**
     * Registra candidatura de uma pessoa a equipes de um evento
     *
     * Regras:
     * - Deve selecionar entre 1 e 3 equipes
     * - Habilidade obrigatória para equipes selecionadas
     * - Não permite candidatura duplicada para a mesma equipe no mesmo evento
     */
    public function candidatura(array $equipesData, int $eventoId, Pessoa $pessoa): void
    {
        $equipesSelecionadas = [];

        foreach ($equipesData as $equipeId => $dados) {
            if (($dados['selecionado'] ?? null) === '1') {
                $habilidade = trim($dados['habilidade'] ?? '');

                if ($habilidade === '') {
                    throw ValidationException::withMessages([
                        'equipes' => 'A descrição da habilidade é obrigatória para equipes selecionadas.',
                    ]);
                }

                if (mb_strlen($habilidade) <= 5) {
                    throw ValidationException::withMessages([
                        'equipes' => 'A habilidade deve ter mais de 5 caracteres.',
                    ]);
                }

                if (preg_match('/(.)\1{4,}/', $habilidade)) {
                    throw ValidationException::withMessages([
                        'equipes' => 'A habilidade não pode conter sequências de caracteres repetidos.',
                    ]);
                }

                $equipesSelecionadas[$equipeId] = $habilidade;
            }
        }

        $quantidade = count($equipesSelecionadas);

        if ($quantidade < 1 || $quantidade > 3) {
            throw ValidationException::withMessages([
                'equipes' => $quantidade < 1
                    ? 'Você deve selecionar ao menos 1 equipe.'
                    : 'Você pode selecionar no máximo 3 equipes.',
            ]);
        }

        // Validação de duplicidade
        foreach (array_keys($equipesSelecionadas) as $equipeId) {
            $duplicado = Voluntario::where('idt_pessoa', $pessoa->idt_pessoa)
                ->where('idt_evento', $eventoId)
                ->where('idt_equipe', $equipeId)
                ->exists();

            if ($duplicado) {
                throw ValidationException::withMessages([
                    'equipes' => 'Você já se candidatou para esta equipe neste evento.',
                ]);
            }
        }

        DB::transaction(function () use ($equipesSelecionadas, $eventoId, $pessoa) {
            foreach ($equipesSelecionadas as $equipeId => $habilidade) {
                Voluntario::create([
                    'idt_pessoa' => $pessoa->idt_pessoa,
                    'idt_evento' => $eventoId,
                    'idt_equipe' => $equipeId,
                    'txt_habilidade' => $habilidade,
                ]);
            }
        });
    }

    /**
     * Confirma um voluntário como trabalhador do evento
     *
     * Regras:
     * - Voluntário deve existir
     * - Pessoa só pode ter 1 trabalhador por evento
     * - Todas as candidaturas do voluntário são vinculadas ao trabalhador
     */
    public function confirmacao(
        int $voluntarioId,
        int $equipeId,
        bool $isCoordenador = false,
        bool $isPrimeiraVez = false
    ): Voluntario {
        $voluntario = Voluntario::find($voluntarioId);

        if (!$voluntario) {
            throw ValidationException::withMessages([
                'voluntario' => 'Voluntário não encontrado.',
            ]);
        }

        DB::transaction(function () use (
            $voluntario,
            $equipeId,
            $isCoordenador,
            $isPrimeiraVez
        ) {
            $trabalhador = Trabalhador::updateOrCreate(
                [
                    'idt_pessoa' => $voluntario->idt_pessoa,
                    'idt_evento' => $voluntario->idt_evento,
                ],
                [
                    'idt_equipe' => $equipeId,
                    'ind_coordenador' => $isCoordenador,
                    'ind_primeira_vez' => $isPrimeiraVez,
                ]
            );

            Voluntario::where('idt_pessoa', $voluntario->idt_pessoa)
                ->where('idt_evento', $voluntario->idt_evento)
                ->update([
                    'idt_trabalhador' => $trabalhador->idt_trabalhador,
                ]);
        });

        return $voluntario;
    }
}
