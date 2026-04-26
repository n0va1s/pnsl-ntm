<?php

namespace App\Services;

use App\Models\Evento;
use App\Models\Participante;
use App\Models\Pessoa;
use App\Models\Trabalhador;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EventoService
{
    public function criarEventoComFoto(array $dados, ?UploadedFile $foto): Evento
    {
        return DB::transaction(function () use ($dados, $foto) {
            $evento = Evento::create($dados);

            if ($foto) {
                $this->fotoUpload($evento, $foto);
            }

            return $evento;
        });
    }

    /**
     * Busca a timeline otimizada.
     * Melhoria: Uso de map dinâmico para evitar repetição de chaves.
     */
    public function getEventosTimeline(Pessoa $pessoa): array
    {
        $relacoesBase = ['evento.movimento:idt_movimento,des_sigla'];

        $trabalhos = Trabalhador::where('idt_pessoa', $pessoa->idt_pessoa)
            ->whereHas('evento')
            ->with(array_merge($relacoesBase, ['equipe:idt_equipe,des_equipe']))
            ->get()
            ->map(fn ($t) => $this->formataTimeline($t, 'Trabalhador'));

        $participacoes = Participante::where('idt_pessoa', $pessoa->idt_pessoa)
            ->whereHas('evento')
            ->with($relacoesBase)
            ->get()
            ->map(fn ($p) => $this->formataTimeline($p, 'Participante'));

        $timeline = $trabalhos->concat($participacoes)->sortByDesc('date');

        return $this->agruparEventosPorDecadaEAno($timeline);
    }

    private function agruparEventosPorDecadaEAno(Collection $eventos): array
    {
        return $eventos
            ->groupBy(fn ($entry) => $this->decadaDaData($entry['date']))
            ->sortKeysDesc()
            ->map(fn ($decadeEntries, $decade) => [
                'decade' => $decade,
                'years' => $decadeEntries
                    ->groupBy(fn ($entry) => Carbon::parse($entry['date'])->year)
                    ->sortKeysDesc()
                    ->map(fn ($yearEntries, $year) => [
                        'year' => $year,
                        'events' => $yearEntries->values()->all(),
                    ])
                    ->values()
                    ->all(),
            ])
            ->values()
            ->all();
    }

    private function decadaDaData(string $date): string
    {
        $year = Carbon::parse($date)->year;

        return (int) floor($year / 10) * 10 .'s';
    }

    private function formataTimeline($model, string $type): array
    {
        // Verifica se o relacionamento 'evento' existe
        if (! $model->evento) {
            return [
                'type' => $type,
                'date' => null,
                'event' => null,
                'details' => [
                    'equipe' => $model->equipe->des_equipe ?? null,
                    'coordenador' => $model->ind_coordenador ?? false,
                ],
            ];
        }

        $details = $type === 'Trabalhador'
            ? [
                'equipe' => $model->equipe->des_equipe ?? null,
                'coordenador' => $model->ind_coordenador ?? false,
            ]
            : [];

        return [
            'type' => $type,
            'date' => $model->evento->dat_inicio,
            'event' => $model->evento,
            'details' => $details,
        ];
    }

    public function calcularPontuacao(Pessoa $pessoa): int
    {
        $participacoes = Participante::where('idt_pessoa', $pessoa->idt_pessoa)
            ->whereHas('evento')
            ->with('evento:idt_evento,des_evento,dat_inicio,tip_evento')
            ->get()
            ->map(fn (Participante $participante) => [
                'date' => $participante->evento->dat_inicio,
                'points' => $participante->evento->tip_evento === 'D' ? 3 : 1,
            ]);

        $trabalhos = Trabalhador::where('idt_pessoa', $pessoa->idt_pessoa)
            ->whereHas('evento')
            ->with('evento:idt_evento,des_evento,dat_inicio')
            ->get()
            ->map(fn (Trabalhador $trabalhador) => [
                'date' => $trabalhador->evento->dat_inicio,
                'points' => $trabalhador->ind_coordenador ? 4 : 2,
            ]);

        $eventos = $participacoes->concat($trabalhos);

        if ($eventos->isEmpty()) {
            return 0;
        }

        return $eventos->sum('points') + 5;
    }

    public function calcularRanking(Pessoa $pessoa): int
    {
        // Se a pessoa não tem pontos, está na última posição (count total + 1)
        $pontuacao = $this->calcularPontuacao($pessoa);
        $pessoasComPontuacaoMaior = Pessoa::query()
            ->get()
            ->filter(fn (Pessoa $candidata) => $this->calcularPontuacao($candidata) > $pontuacao)
            ->count();

        return $pessoasComPontuacaoMaior + 1;
    }

    public function fotoUpload(Evento $evento, ?UploadedFile $file): void
    {
        if (! $file) {
            return;
        }

        DB::transaction(function () use ($evento, $file) {
            $evento->load('foto'); // Carrega a relação

            if ($evento->foto && Storage::disk('public')->exists($evento->foto->med_foto)) {
                Storage::disk('public')->delete($evento->foto->med_foto);
            }

            $path = $file->store('fotos/evento', 'public'); // Salva o novo arquivo

            $evento->foto()->updateOrCreate(
                ['idt_evento' => $evento->idt_evento], // Busca por este ID
                ['med_foto' => $path]                  // Atualiza ou cria com este caminho
            );
        });
    }

    public function fotoDelete(Evento $evento): void
    {
        DB::transaction(function () use ($evento) {
            $evento->load('foto');

            if ($evento->foto) {
                if (Storage::disk('public')->exists($evento->foto->med_foto)) {
                    Storage::disk('public')->delete($evento->foto->med_foto);
                }
                $evento->foto->delete();
            }

            $evento->delete();
        });
    }

    public function excluirEventoComFoto(Evento $evento): void
    {
        $this->fotoDelete($evento);
    }

    public function confirmarParticipacao(Evento $evento, Pessoa $pessoa): Participante
    {
        return Participante::firstOrCreate([
            'idt_evento' => $evento->idt_evento,
            'idt_pessoa' => $pessoa->idt_pessoa,
        ]);
    }

    public function getEventosInscritos(Pessoa $pessoa): Collection
    {
        return Participante::where('idt_pessoa', $pessoa->idt_pessoa)
            ->pluck('idt_evento');
    }
}
