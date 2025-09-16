<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\TipoMovimento;
use App\Http\Requests\EventoRequest;
use App\Models\Participante;
use App\Models\Pessoa;
use App\Models\Trabalhador;
use App\Services\EventoService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class EventoController extends Controller
{
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
        $search = trim($request->input('search', ''));
        $idt_movimento = $request->input('idt_movimento');

        $pessoa = Auth::check() ? $this->userService->createPessoaFromLoggedUser() : null;

        $eventosInscritos = [];
        $encontrosInscritos = [];

        // Verifica se o usuário está logado para popular as listas de eventos inscritos
        if ($pessoa) {

            // Pos-entrontros e desafios
            $eventosInscritos = $this->eventoService->getEventosInscritos($pessoa);

            // Encontros anuais
            $encontrosInscritos = $this->eventoService->getEncontrosInscritos($pessoa);
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
        try {
            DB::beginTransaction();
            $data = $request->validated();
            $evento = Evento::create($data);
            $this->eventoService->fotoUpload($evento, $request->file('med_foto'));
            DB::commit();

            return redirect()
                ->route('eventos.index')
                ->with('success', 'Evento criado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar evento: ' . $e->getMessage(), ['exception' => $e]);

            return redirect()
                ->route('eventos.index')
                ->with('error', 'Erro ao criar evento. Por favor, tente novamente.');
        }
    }

    public function show(Evento $evento): View
    {
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
        try {
            DB::beginTransaction();
            $data = $request->validated();
            $evento->update($data);
            $this->eventoService->fotoUpload($evento, $request->file('med_foto'));
            DB::commit();

            return redirect()
                ->route('eventos.index')
                ->with('success', 'Evento atualizado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar evento: ' . $e->getMessage(), ['exception' => $e]);

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
        try {
            $this->eventoService->excluirEventoComFoto($evento);

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
        Participante::create([
            'idt_evento' => $evento->idt_evento,
            'idt_pessoa' => $pessoa->idt_pessoa,
        ]);

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
        $pessoa = $this->userService->createPessoaFromLoggedUser();

        $timeline = $this->eventoService->getEventosTimeline($pessoa);
        $pontuacaoTotal = $this->eventoService->calcularPontuacao($pessoa);
        $posicaoNoRanking = $this->eventoService->calcularRanking($pessoa);

        return view('evento.linhadotempo', compact('timeline', 'pontuacaoTotal', 'posicaoNoRanking', 'pessoa'));
    }
}
