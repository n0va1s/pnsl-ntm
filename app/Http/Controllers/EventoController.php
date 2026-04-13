<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventoRequest;
use App\Models\Evento;
use App\Models\Pessoa;
use App\Models\TipoMovimento;
use App\Services\EventoService;
use App\Traits\LogContext;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
                'fichas as fichas_count',
                'participantes as participantes_count' => fn($q) => $q->whereNull('tip_cor_troca'),
                'participantes as inscritos_count' => fn($q) => $q->whereNotNull('tip_cor_troca'),

                // contar IDs de pessoas únicos para evitar duplicidade por múltiplas equipes
                'voluntarios as voluntarios_count' => fn($q) => $q
                    ->select(DB::raw('count(distinct(idt_pessoa))'))
                    ->whereNull('idt_trabalhador'),

                'voluntarios as trabalhadores_count' => fn($q) => $q
                    ->select(DB::raw('count(distinct(idt_pessoa))'))
                    ->whereNotNull('idt_trabalhador'),
            ])
            ->when($pessoa, function ($q) use ($pessoa) {
                $q->withExists([
                    'participantes as ja_inscrito_participante' => fn($q) => $q
                        ->where('idt_pessoa', $pessoa->idt_pessoa),
                ])
                    ->withExists([
                        'voluntarios as ja_inscrito_voluntario' => fn($q) => $q
                            ->where('idt_pessoa', $pessoa->idt_pessoa),
                    ]);
            })
            ->when($request->search, fn($q) => $q->search($request->search))
            ->when($request->idt_movimento, fn($q) => $q->movimento($request->idt_movimento))
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
     * Exibe o formulário para criar um novo evento.
     */
    public function create(): View
    {
        $context = $this->getLogContext(request());
        Log::info('Acesso ao formulário de criação de evento', $context);

        $movimentos = TipoMovimento::all();
        $evento = new Evento;

        return view('evento.form', compact('movimentos', 'evento'));
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

    public function show(Evento $evento): View
    {
        $context = $this->getLogContext(request());
        Log::info('Visualização de evento', array_merge($context, ['evento_id' => $evento->idt_evento]));

        $movimentos = TipoMovimento::all();

        return view('evento.form', compact('movimentos', 'evento'));
    }

    /**
     * Exibe o formulário para editar um evento.
     */
    public function edit(Evento $evento): View
    {
        $context = $this->getLogContext(request());
        Log::info('Acesso ao formulário de edição de evento', array_merge($context, ['evento_id' => $evento->idt_evento]));

        $movimentos = TipoMovimento::all();

        return view('evento.form', compact('movimentos', 'evento'));
    }

    /**
     * Atualiza o evento no banco de dados.
     */
    public function update(EventoRequest $request, Evento $evento): RedirectResponse
    {
        $start = microtime(true);
        $context = $this->getLogContext($request);

        Log::info('Tentativa de atualização de evento', array_merge($context, [
            'evento_id' => $evento->idt_evento,
            'titulo' => $request->get('des_evento'),
        ]));

        try {
            DB::beginTransaction();
            $data = $request->validated();

            $data['dat_limite_inscricao'] = $data['dat_limite_inscricao'] ?? null;
            $data['qtd_vaga'] = $data['qtd_vaga'] ?? null;

            $evento->update($data);

            $this->eventoService->fotoUpload($evento, $request->file('med_foto'));
            DB::commit();

            $duration = round((microtime(true) - $start) * 1000, 2);

            Log::notice('Evento atualizado com sucesso', array_merge($context, [
                'evento_id' => $evento->idt_evento,
                'duration_ms' => $duration,
            ]));

            return redirect()
                ->route('eventos.index')
                ->with('success', 'Evento atualizado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();

            $duration = round((microtime(true) - $start) * 1000, 2);
            Log::error('Erro ao atualizar evento', array_merge($context, [
                'evento_id' => $evento->idt_evento,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'duration_ms' => $duration,
                'input_data' => $request->validated(),
            ]));

            return redirect()
                ->route('eventos.index')
                ->with('error', 'Erro ao atualizar evento. Por favor, tente novamente.');
        }
    }

    /**
     * Remove o evento do banco de dados.
     *
     * @return RedirectResponse
     */
    public function destroy(Evento $evento)
    {
        $start = microtime(true);
        $context = $this->getLogContext(request());

        Log::warning('Tentativa de exclusão de evento', array_merge($context, [
            'evento_id' => $evento->idt_evento,
            'titulo_evento' => $evento->des_evento,
        ]));

        try {
            // deleta a foto e o evento
            $this->eventoService->fotoDelete($evento);

            $duration = round((microtime(true) - $start) * 1000, 2);

            Log::notice('Evento excluído com sucesso', array_merge($context, [
                'evento_id' => $evento->idt_evento,
                'duration_ms' => $duration,
            ]));

            return redirect()->route('eventos.index')
                ->with('success', 'Evento excluído com sucesso!');
        } catch (QueryException $e) {

            if ($e->getCode() === '23000') {
                return redirect()->route('eventos.index')
                    ->with('error', 'Não é possível excluir o evento, pois ele possui participantes vinculados.');
            }

            return redirect()->route('eventos.index')
                ->with('error', 'Ocorreu um erro de banco de dados ao excluir o evento.');
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
