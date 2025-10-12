<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\TipoMovimento;
use App\Http\Requests\EventoRequest;
use App\Models\Participante;
use App\Models\Pessoa;
use App\Services\EventoService;
use App\Services\UserService;
use App\Traits\LogContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class EventoController extends Controller
{
    use LogContext;
    protected $eventoService;
    protected $userService;

    /**
     * Injeção de dependência do EventoService e UserService no construtor.
     *
     * @param EventoService $eventoService
     * @param UserService $userService
     */
    public function __construct(EventoService $eventoService, UserService $userService)
    {
        $this->eventoService = $eventoService;
        $this->userService = $userService;
    }

    /**
     * Exibe a página de listagem de eventos.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request): View
    {
        $start = microtime(true);
        $context = $this->getLogContext($request);

        $search = trim($request->input('search', ''));
        $idt_movimento = $request->input('idt_movimento');

        Log::info('Requisição de listagem de eventos iniciada', array_merge($context, [
            'search_term' => $search,
            'idt_movimento_filtro' => $idt_movimento,
        ]));

        $pessoa = Auth::check() ? $this->userService->createPessoaFromLoggedUser() : null;

        $eventosInscritos = [];
        $encontrosInscritos = [];

        // Verifica se o usuário está logado para popular as listas de eventos inscritos
        if ($pessoa) {

            // Pos-entrontros e desafios
            $eventosInscritos = $this->eventoService->getEventosInscritos($pessoa);

            // Encontros anuais
            $encontrosInscritos = $this->eventoService->getEncontrosInscritos($pessoa);

            Log::debug('Inscrições de eventos carregadas', array_merge($context, [
                'pessoa_id' => $pessoa->idt_pessoa,
                'total_eventos_inscritos' => count($eventosInscritos),
                'total de encontros inscritos' => count($encontrosInscritos),
            ]));
        }

        $query = Evento::with(['movimento', 'foto'])
            ->withCount([
                'participantes as participantes_count' => function ($q) {
                    $q->select(DB::raw('COUNT(DISTINCT idt_pessoa)'));
                },
                'voluntarios as voluntarios_count' => function ($q) {
                    $q->whereNull('idt_trabalhador')
                        ->select(DB::raw('COUNT(DISTINCT idt_pessoa)'));
                },
                'voluntarios as trabalhadores_count' => function ($q) {
                    $q->whereNotNull('idt_trabalhador')
                        ->select(DB::raw('COUNT(DISTINCT idt_pessoa)'));
                },
                'fichas',
            ])->when($search, function ($query, $search) {
                return $query->search($search);
            })->when($idt_movimento, function ($query, $idt_movimento) {
                return $query->movimento($idt_movimento);
            })->orderBy('dat_inicio', 'desc');

        $movimentos = TipoMovimento::select('idt_movimento', 'nom_movimento', 'des_sigla')
            ->orderBy('des_movimento')
            ->get();

        $eventos = $query->paginate(12);

        $duration = round((microtime(true) - $start) * 1000, 2);

        Log::notice('Listagem de eventos concluída com sucesso', array_merge($context, [
            'total_eventos' => $eventos->total(),
            'duration_ms' => $duration,
        ]));

        return view('evento.list', compact(
            'eventos',
            'search',
            'idt_movimento',
            'eventosInscritos',
            'encontrosInscritos',
            'pessoa',
            'movimentos'
        ));
    }

    /**
     * Exibe o formulário para criar um novo evento.
     *
     * @return \Illuminate\View\View
     */
    public function create(): View
    {
        $context = $this->getLogContext(request());
        Log::info('Acesso ao formulário de criação de evento', $context);

        $movimentos = TipoMovimento::all();
        $evento = new Evento();

        return view('evento.form', compact('movimentos', 'evento'));
    }

    /**
     * Armazena um novo evento no banco de dados.
     *
     * @param EventoRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(EventoRequest $request): RedirectResponse
    {
        $start = microtime(true);
        $context = $this->getLogContext($request);

        Log::info('Tentativa de criação de novo evento', array_merge($context, [
            'titulo' => $request->get('des_evento'),
        ]));

        try {
            DB::beginTransaction();
            $data = $request->validated();
            $evento = Evento::create($data);
            $this->eventoService->fotoUpload($evento, $request->file('med_foto'));
            DB::commit();

            $duration = round((microtime(true) - $start) * 1000, 2);

            Log::notice('Evento criado com sucesso', array_merge($context, [
                'evento_id' => $evento->idt_evento,
                'duration_ms' => $duration,
            ]));

            return redirect()
                ->route('eventos.index')
                ->with('success', 'Evento criado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();

            $duration = round((microtime(true) - $start) * 1000, 2);

            Log::error('Erro ao criar evento', array_merge($context, [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'duration_ms' => $duration,
                'input_data' => $request->validated(), // Loga dados validados para debugging
            ]));

            return redirect()
                ->route('eventos.index')
                ->with('error', 'Erro ao criar evento. Por favor, tente novamente.');
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
     *
     * @param Evento $evento
     * @return \Illuminate\View\View
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
     *
     * @param EventoRequest $request
     * @param Evento $evento
     * @return \Illuminate\Http\RedirectResponse
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
     * @param Evento $evento
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Evento $evento)
    {
        $start = microtime(true);
        $context = $this->getLogContext($request);

        Log::warning('Tentativa de exclusão de evento', array_merge($context, [
            'evento_id' => $evento->idt_evento,
            'titulo_evento' => $evento->des_evento,
        ]));

        try {
            $this->eventoService->excluirEventoComFoto($evento);

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
     * Confirma a participação de uma pessoa em um evento.
     *
     * @param Evento $evento
     * @param Pessoa $pessoa
     * @return \Illuminate\Http\RedirectResponse
     */
    public function confirm(Evento $evento, Pessoa $pessoa): RedirectResponse
    {
        $start = microtime(true);
        $context = $this->getLogContext(request());

        Log::info('Tentativa de confirmação de participação em evento', array_merge($context, [
            'evento_id' => $evento->idt_evento,
            'pessoa_id' => $pessoa->idt_pessoa,
        ]));

        Participante::create([
            'idt_evento' => $evento->idt_evento,
            'idt_pessoa' => $pessoa->idt_pessoa,
        ]);

        $duration = round((microtime(true) - $start) * 1000, 2);

        Log::notice('Participação confirmada com sucesso', array_merge($context, [
            'evento_id' => $evento->idt_evento,
            'pessoa_id' => $pessoa->idt_pessoa,
            'duration_ms' => $duration,
        ]));

        return redirect()
            ->route('eventos.index')
            ->with('success', 'Sua participação foi confirmada!');
    }

    /**
     * Exibe a linha do tempo de eventos de uma pessoa.
     *
     * @return \Illuminate\View\View
     */
    public function timeline(): View
    {
        $start = microtime(true);
        $context = $this->getLogContext(request());

        Log::info('Requisição da linha do tempo iniciada', $context);

        $pessoa = $this->userService->createPessoaFromLoggedUser();
        Log::debug('Carregando dados da linha do tempo e ranking', array_merge($context, [
            'pessoa_id' => $pessoa->idt_pessoa,
        ]));

        $timeline = $this->eventoService->getEventosTimeline($pessoa);
        $pontuacaoTotal = $this->eventoService->calcularPontuacao($pessoa);
        $posicaoNoRanking = $this->eventoService->calcularRanking($pessoa);

        $duration = round((microtime(true) - $start) * 1000, 2);

        Log::notice('Linha do tempo concluída com sucesso', array_merge($context, [
            'total_eventos_timeline' => count($timeline),
            'pontuacao_total' => $pontuacaoTotal,
            'posicao_ranking' => $posicaoNoRanking,
            'duration_ms' => $duration,
        ]));

        return view('evento.linhadotempo', compact('timeline', 'pontuacaoTotal', 'posicaoNoRanking', 'pessoa'));
    }
}
