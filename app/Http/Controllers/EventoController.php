<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventoRequest;
use App\Models\Evento;
use App\Models\Pessoa;
use App\Models\TipoMovimento;
use App\Services\EventoService;
use App\Traits\LogContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class EventoController extends Controller
{
    use LogContext;

    public function __construct(
        protected EventoService $eventoService
    ) {}

    public function index(Request $request): View
    {
        $context = $this->getLogContext($request);
        $pessoa = Auth::user()->pessoa;

        $eventos = Evento::query()
            ->with(['movimento:idt_movimento,des_sigla'])
            ->withCount([
                'participantes',
                'voluntarios as voluntarios_count' => fn ($q) => $q->whereNull('idt_trabalhador'),
                'voluntarios as trabalhadores_count' => fn ($q) => $q->whereNotNull('idt_trabalhador'),
            ])
            ->when($pessoa, function ($q) use ($pessoa) {
                $q->withExists(['participantes as ja_inscrito_participante' => fn ($q) => $q->where('idt_pessoa', $pessoa->idt_pessoa)])
                    ->withExists(['voluntarios as ja_inscrito_voluntario' => fn ($q) => $q->where('idt_pessoa', $pessoa->idt_pessoa)]);
            })
            ->when($request->search, fn ($q) => $q->search($request->search))
            ->when($request->idt_movimento, fn ($q) => $q->movimento($request->idt_movimento))
            ->orderBy('dat_inicio', 'desc')
            ->paginate(12)
            ->withQueryString();

        return view('evento.list', [
            'eventos' => $eventos,
            'movimentos' => TipoMovimento::all(['idt_movimento', 'des_sigla']),
            'search' => $request->search,
            'idt_movimento' => $request->idt_movimento,
        ]);
    }

    /**
     * Salva o evento delegando a complexidade ao Service.
     */
    public function store(EventoRequest $request): RedirectResponse
    {
        try {
            $evento = $this->eventoService->criarEventoComFoto($request->validated(), $request->file('med_foto'));

            Log::notice('Evento criado', ['evento_id' => $evento->id]);

            return redirect()->route('eventos.index')->with('success', 'Evento criado com sucesso!');
        } catch (\Exception $e) {
            Log::error('Falha ao criar evento', ['error' => $e->getMessage()]);

            return back()->with('error', 'Erro ao processar cadastro.')->withInput();
        }
    }

    /**
     * Confirma participação
     */
    public function confirm(Evento $evento, Pessoa $pessoa): RedirectResponse
    {
        $this->eventoService->confirmarParticipacao($evento, $pessoa);

        Log::notice('Participação confirmada', [
            'evento' => $evento->idt_evento,
            'pessoa' => $pessoa->idt_pessoa,
        ]);

        return redirect()->route('eventos.index')->with('success', 'Sua participação foi confirmada!');
    }

    /**
     * Linha do Tempo Otimizada.
     */
    public function timeline(): View
    {
        $start = microtime(true);
        $pessoa = Auth::user()->pessoa->fresh();

        // Dados cacheados/processados para alta performance
        $data = [
            'timeline' => $this->eventoService->getEventosTimeline($pessoa),
            'pontuacaoTotal' => $pessoa->qtd_pontos_total ?? 0,
            'posicaoNoRanking' => $this->eventoService->calcularRanking($pessoa),
            'pessoa' => $pessoa,
        ];

        $this->logPerformance($pessoa, $data, $start);

        return view('evento.linhadotempo', $data);
    }

    private function logPerformance($pessoa, $timeline, $start): void
    {
        Log::info('Timeline acessada', [
            'pessoa' => $pessoa->idt_pessoa,
            'count' => count($timeline),
            'ms' => round((microtime(true) - $start) * 1000, 2),
        ]);
    }
}
