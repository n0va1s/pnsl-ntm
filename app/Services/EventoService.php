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

        return $this->agruparEventosPorAno($timeline);
    }

    private function agruparEventosPorAno(Collection $eventos): array
    {
        return $eventos
            ->groupBy(fn ($entry) => Carbon::parse($entry['date'])->year) // Agrupa por ano
            ->sortKeysDesc() // Garante o mais recente primeiro
            ->map(fn ($yearEntries, $year) => [
                'year' => $year,
                'events' => $yearEntries->values()->all(),
            ])
            ->values()
            ->all();
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

        return [
            'type' => $type,
            'date' => $model->evento->dat_inicio,
            'event' => $model->evento,
            'details' => [
                'equipe' => $model->equipe->des_equipe ?? null,
                'coordenador' => $model->ind_coordenador ?? false,
            ],
        ];
    }

    public function calcularRanking(Pessoa $pessoa): int
    {
        // Se a pessoa não tem pontos, está na última posição (count total + 1)
        if (! $pessoa->qtd_pontos_total) {
            return Pessoa::count();
        }

        return Pessoa::where('qtd_pontos_total', '>', $pessoa->qtd_pontos_total)
            ->count() + 1;
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

    public function confirmarParticipacao(Evento $evento, Pessoa $pessoa): Participante
    {
        return Participante::firstOrCreate([
            'idt_evento' => $evento->idt_evento,
            'idt_pessoa' => $pessoa->idt_pessoa,
        ]);
    }
}
