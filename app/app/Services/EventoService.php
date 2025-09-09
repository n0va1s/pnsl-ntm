<?php

namespace App\Services;

use App\Models\Evento;
use App\Models\Pessoa;
use App\Models\Trabalhador;
use App\Models\Participante;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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
        $trabalhadorEventos = Trabalhador::where('idt_pessoa', $pessoa->idt_pessoa)
            ->whereHas('evento', fn($query) => $query->whereNotNull('dat_inicio'))
            ->with(['evento.movimento', 'equipe'])
            ->get()
            ->map(fn($entry) => [
                'id' => 'trab-' . $entry->idt_trabalhador,
                'type' => 'Trabalhador',
                'date' => Carbon::parse($entry->evento->dat_inicio),
                'event' => $entry->evento,
                'details' => [
                    'equipe' => $entry->equipe->des_grupo ?? 'N/A',
                    'coordenador' => (bool)$entry->ind_coordenador,
                    'primeira_vez' => (bool)$entry->ind_primeira_vez,
                ],
            ]);

        $participanteEventos = Participante::where('idt_pessoa', $pessoa->idt_pessoa)
            ->whereHas('evento', fn($query) => $query->whereNotNull('dat_inicio'))
            ->with('evento.movimento')
            ->get()
            ->map(fn($entry) => [
                'id' => 'part-' . $entry->idt_participante,
                'type' => 'Participante',
                'date' => Carbon::parse($entry->evento->dat_inicio),
                'event' => $entry->evento,
                'details' => [],
            ]);

        $allEntries = $trabalhadorEventos->concat($participanteEventos)->sortByDesc('date');

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
        $trabalhadorEventos = Trabalhador::where('idt_pessoa', $pessoa->idt_pessoa)
            ->whereHas('evento', fn($q) => $q->where('tip_evento', 'A'))
            ->with('evento')
            ->get();

        $participanteEventos = Participante::where('idt_pessoa', $pessoa->idt_pessoa)
            ->whereHas('evento', fn($q) => $q->where('tip_evento', 'P'))
            ->with('evento')
            ->get();

        $desafioEventos = Participante::where('idt_pessoa', $pessoa->idt_pessoa)
            ->whereHas('evento', fn($q) => $q->where('tip_evento', 'D'))
            ->with('evento')
            ->get();

        $todosEventos = $trabalhadorEventos->map(fn($entry) => [
            'type' => 'Trabalhador',
            'date' => Carbon::parse($entry->evento->dat_inicio),
            'is_coordenador' => (bool)$entry->ind_coordenador,
        ])->concat($participanteEventos->map(fn($entry) => [
            'type' => 'Participante',
            'date' => Carbon::parse($entry->evento->dat_inicio),
            'is_coordenador' => false,
        ]))->concat($desafioEventos->map(fn($entry) => [
            'type' => 'Desafio',
            'date' => Carbon::parse($entry->evento->dat_inicio),
            'is_coordenador' => false,
        ]));

        $ordenados = $todosEventos->sortBy('date');
        $pontuacao = 0;
        $primeiro = true;

        foreach ($ordenados as $evento) {
            // Bônus do primeiro evento
            if ($primeiro) {
                $pontuacao += 5;
                $primeiro = false;
            }

            // Pontos por tipo
            if ($evento['type'] === 'Participante') {
                $pontuacao += 1;
            } elseif ($evento['type'] === 'Trabalhador') {
                $pontuacao += 2;
                if ($evento['is_coordenador']) {
                    $pontuacao += 2; // coordenador ganha extra
                }
            } elseif ($evento['type'] === 'Desafio') {
                $pontuacao += 3;
            }
        }

        return $pontuacao;
    }


    /**
     * Calcula a posição no ranking de uma pessoa.
     *
     * @param Pessoa $currentPessoa
     * @return int|string
     */
    public function calcularRanking(Pessoa $currentPessoa): int|string
    {
        $pessoas = Pessoa::all();
        $arrPessoaPontos = [];

        foreach ($pessoas as $pessoa) {
            $arrPessoaPontos[$pessoa->idt_pessoa] = $this->calcularPontuacao($pessoa);
        }

        arsort($arrPessoaPontos);
        $rank = 1;
        $pontuacaoAnterior = null;
        $posicaoAtual = 0;

        foreach ($arrPessoaPontos as $pessoaId => $pontuacao) {
            $posicaoAtual++;
            if ($pontuacaoAnterior !== $pontuacao) {
                $rank = $posicaoAtual;
            }
            if ($pessoaId === $currentPessoa->idt_pessoa) {
                return $rank;
            }
            $pontuacaoAnterior = $pontuacao;
        }
        return 'N/A';
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
            ->groupBy(fn($entry) => $entry['date']->year)
            ->sortKeysDesc()
            ->map(fn($yearEntries, $year) => [
                'year' => $year,
                'events' => $yearEntries->values()->all(),
            ])
            ->groupBy(fn($yearData) => floor($yearData['year'] / 10) * 10 . 's')
            ->sortKeysDesc()
            ->map(fn($decadeYears, $decade) => [
                'decade' => $decade,
                'years' => $decadeYears->values()->all(),
            ])
            ->values()
            ->all();
    }

    /**
     * Lida com o upload da foto do evento, excluindo a antiga se houver.
     *
     * @param Evento $evento O modelo do evento ao qual a foto está associada.
     * @param UploadedFile|null $file O arquivo da foto.
     * @return void
     */
    public function fotoUpload(Evento $evento, ?UploadedFile $file = null): void
    {
        DB::beginTransaction();
        try {
            if ($file) {
                $evento->load('foto');
                if ($evento->foto) {
                    $evento->foto->delete();
                }

                $caminho = $file->store('fotos/evento', 'public');
                $evento->foto()->create(['med_foto' => $caminho]);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Exclui um evento e sua foto associada de forma segura.
     *
     * @param Evento $evento
     * @return void
     */
    public function excluirEventoComFoto(Evento $evento): void
    {
        DB::beginTransaction();
        try {
            $foto = $evento->foto;
            $evento->delete();

            if ($foto) {
                $foto->delete();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Confirma a participação de uma pessoa em um evento.
     *
     * @param Evento $evento
     * @param Pessoa $pessoa
     * @return void
     */
    public function confirmarParticipacao(Evento $evento, Pessoa $pessoa): void
    {
        Participante::create([
            'idt_evento' => $evento->idt_evento,
            'idt_pessoa' => $pessoa->idt_pessoa,
        ]);
    }

    // Pós-encontros e desafios
    public function getEventosInscritos(Pessoa $pessoa): array
    {
        return Evento::whereHas('participantes', function ($query) use ($pessoa) {
            $query->where('idt_pessoa', $pessoa->idt_pessoa);
        })
            ->pluck('idt_evento')
            ->toArray();
    }

    // Encontros anuais
    public function getEncontrosInscritos(Pessoa $pessoa): array
    {
        return Evento::whereHas('voluntarios', function ($query) use ($pessoa) {
            $query->where('idt_pessoa', $pessoa->idt_pessoa);
        })
            ->pluck('idt_evento')
            ->toArray();
    }
}
