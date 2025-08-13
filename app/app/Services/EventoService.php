<?php

namespace App\Services;

use App\Models\Pessoa;
use App\Models\Trabalhador;
use App\Models\Participante;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class EventoService
{
    /**
     * Busca e prepara todos os eventos de uma pessoa para a timeline.
     *
     * @param Pessoa $pessoa
     * @return array
     */
    public function getEventosTimeline(Pessoa $pessoa): array
    {
        // Buscar e mapear entradas como Trabalhador
        $trabalhadorEntries = Trabalhador::where('idt_pessoa', $pessoa->idt_pessoa)
            ->with(['evento.movimento', 'equipe'])
            ->get()
            ->map(function ($entry) {
                $eventDate = $entry->evento->dat_inicio ?? null;
                if (!$eventDate) {
                    return null;
                }
                return [
                    'id' => 'trab-' . $entry->idt_trabalhador, // Adiciona um ID único para cada entrada
                    'type' => 'Trabalhador',
                    'date' => Carbon::parse($eventDate),
                    'event' => $entry->evento,
                    'details' => [
                        'equipe' => $entry->equipe->des_grupo ?? 'N/A',
                        'coordenador' => (bool)$entry->ind_coordenador, // Garante booleano
                        'primeira_vez' => (bool)$entry->ind_primeira_vez, // Garante booleano
                    ],
                ];
            })->filter();

        // Buscar e mapear entradas como Participante
        $participanteEntries = Participante::where('idt_pessoa', $pessoa->idt_pessoa)
            ->with('evento.movimento')
            ->get()
            ->map(function ($entry) {
                $eventDate = $entry->evento->dat_inicio ?? null;
                if (!$eventDate) {
                    return null;
                }
                return [
                    'id' => 'part-' . $entry->idt_participante, // Adiciona um ID único para cada entrada
                    'type' => 'Participante',
                    'date' => Carbon::parse($eventDate),
                    'event' => $entry->evento,
                    'details' => [],
                ];
            })->filter();

        // Combinar e Ordenar todas as entradas cronologicamente
        $allEntries = $trabalhadorEntries->concat($participanteEntries)->sortBy('date');

        // Agrupar por Década e Ano
        return $this->agruparEventosPorDecadaEAno($allEntries);
    }

    /**
     * Calcula a pontuação total de uma pessoa com base em seus eventos.
     *
     * @param Pessoa $pessoa
     * @return int
     */
    public function calcularPontuacao(Pessoa $pessoa): int
    {
        $score = 0;

        // Pegar todos os eventos da pessoa (Trabalhador e Participante)
        $trabalhadorEvents = Trabalhador::where('idt_pessoa', $pessoa->idt_pessoa)
            ->with('evento')
            ->get();
        $participanteEvents = Participante::where('idt_pessoa', $pessoa->idt_pessoa)
            ->with('evento')
            ->get();

        $allEvents = collect();

        // Normaliza e adiciona eventos de Trabalhador
        foreach ($trabalhadorEvents as $entry) {
            if ($entry->evento && $entry->evento->dat_inicio) {
                $allEvents->push([
                    'type' => 'Trabalhador',
                    'date' => Carbon::parse($entry->evento->dat_inicio),
                    'is_coordenador' => (bool)$entry->ind_coordenador,
                ]);
            }
        }

        // Normaliza e adiciona eventos de Participante
        foreach ($participanteEvents as $entry) {
            if ($entry->evento && $entry->evento->dat_inicio) {
                $allEvents->push([
                    'type' => 'Participante',
                    'date' => Carbon::parse($entry->evento->dat_inicio),
                    'is_coordenador' => false, // Participantes não são coordenadores para pontuação
                ]);
            }
        }

        // Ordena os eventos cronologicamente para identificar o primeiro
        $sortedEvents = $allEvents->sortBy('date')->values();

        $isFirstEventOverall = true; // Flag para o bônus do primeiro evento

        foreach ($sortedEvents as $eventData) {
            // Regra do bônus de 10 pontos para o primeiro evento da pessoa
            if ($isFirstEventOverall) {
                $score += 10;
                $isFirstEventOverall = false; // Garante que o bônus é aplicado apenas uma vez
            }

            // Regras de pontuação base
            if ($eventData['type'] === 'Participante') {
                $score += 1; // 1 ponto por participação
            } elseif ($eventData['type'] === 'Trabalhador') {
                $score += 2; // 2 pontos por trabalhar
                if ($eventData['is_coordenador']) {
                    $score += 1; // +1 ponto se for coordenador (total de 3)
                }
            }
        }

        return $score;
    }

    /**
     * Calcula a posição no ranking de uma pessoa.
     * ATENÇÃO: Este método pode ser custoso para muitas pessoas.
     * Considere otimizações (caching, coluna de score no DB, jobs) em produção.
     *
     * @param Pessoa $currentPessoa
     * @return int|string
     */
    public function calcularRanking(Pessoa $currentPessoa): int|string
    {
        $allPeople = Pessoa::all(); // Busca todas as pessoas

        $personScores = [];
        foreach ($allPeople as $person) {
            // Reutiliza o método calcularPontuacao para cada pessoa
            $personScores[$person->idt_pessoa] = $this->calcularPontuacao($person);
        }

        // Ordena as pontuações em ordem decrescente (do maior para o menor)
        arsort($personScores);

        $rank = 1;
        $previousScore = null;
        $currentRankPosition = 0;

        foreach ($personScores as $pessoaId => $score) {
            $currentRankPosition++;
            // Se a pontuação atual é diferente da anterior, atualiza o rank
            if ($previousScore !== $score) {
                $rank = $currentRankPosition;
            }
            // Se encontrou a pessoa atual, retorna o rank
            if ($pessoaId === $currentPessoa->idt_pessoa) {
                return $rank;
            }
            $previousScore = $score;
        }

        return 'N/A'; // Caso a pessoa não seja encontrada (improvável)
    }

    /**
     * Agrupa eventos por década e ano para a visualização da timeline.
     *
     * @param Collection $allEntries
     * @return array
     */
    private function agruparEventosPorDecadaEAno(Collection $allEntries): array
    {
        return $allEntries
            ->groupBy(function ($entry) {
                return $entry['date']->year;
            })
            ->sortKeysDesc()
            ->map(function ($yearEntries, $year) {
                return [
                    'year' => $year,
                    'events' => $yearEntries->values()->all(),
                ];
            })
            ->groupBy(function ($yearData) {
                $year = $yearData['year'];
                $decade = floor($year / 10) * 10;
                return $decade . 's';
            })
            ->sortKeysDesc()
            ->map(function ($decadeYears, $decade) {
                return [
                    'decade' => $decade,
                    'years' => $decadeYears->values()->all(),
                ];
            })
            ->values()
            ->all();
    }
}
