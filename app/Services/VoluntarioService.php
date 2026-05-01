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

        // 1. Filtrar e validar o formato básico usando Collections
        $candidaturas = collect($equipesData)
            ->filter(fn ($dados) => ($dados['selecionado'] ?? null) === '1')
            ->map(fn ($dados) => trim($dados['habilidade'] ?? ''));

        // 2. Validação de quantidade
        $quantidade = $candidaturas->count();
        if ($quantidade < 1 || $quantidade > 3) {
            throw ValidationException::withMessages([
                'equipes' => $quantidade < 1
                    ? 'Selecione ao menos 1 equipe.'
                    : 'Selecione no máximo 3 equipes.',
            ]);
        }

        // 3. Validação de conteúdo das habilidades (Fail Fast)
        $candidaturas->each(function ($habilidade) {
            if (empty($habilidade) || mb_strlen($habilidade) <= 5) {
                throw ValidationException::withMessages(['equipes' => 'Habilidade inválida ou muito curta.']);
            }
            if (preg_match('/(.)\1{4,}/', $habilidade)) {
                throw ValidationException::withMessages(['equipes' => 'A descrição contém caracteres repetidos inválidos.']);
            }
        });

        // 4. Validação de duplicidade Otimizada (Uma única query)
        $equipesIds = $candidaturas->keys()->all();
        $equipesJaInscritas = Voluntario::where('idt_pessoa', $pessoa->idt_pessoa)
            ->where('idt_evento', $eventoId)
            ->whereIn('idt_equipe', $equipesIds)
            ->exists();

        if ($equipesJaInscritas) {
            throw ValidationException::withMessages([
                'equipes' => 'Você já possui inscrição em uma ou mais equipes selecionadas.',
            ]);
        }

        // 5. Persistência em lote
        DB::transaction(function () use ($candidaturas, $eventoId, $pessoa) {
            $insertData = $candidaturas->map(fn ($habilidade, $equipeId) => [
                'idt_pessoa' => $pessoa->idt_pessoa,
                'idt_evento' => $eventoId,
                'idt_equipe' => $equipeId,
                'txt_habilidade' => $habilidade,
                'created_at' => now(), // Importante se usar query builder ou se não for automático
                'updated_at' => now(),
            ])->values()->all();

            Voluntario::insert($insertData);
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

        if (! $voluntario) {
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
