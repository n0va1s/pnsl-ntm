<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\TipoMovimento;
use App\Http\Requests\EventoRequest;
use App\Models\Participante;
use App\Models\Pessoa;
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

        $pessoa = Auth::check() ? $this->userService->createPessoaFromLoggedUser() : null;

        $posEncontrosInscritos = [];
        $eventosInscritos = [];
        $desafiosInscritos = [];

        // Verifica se o usuário está logado para popular as listas de eventos inscritos
        if ($pessoa) {
            $posEncontrosInscritos = Participante::where('idt_pessoa', $pessoa->idt_pessoa)
                ->whereHas('evento', function ($query) {
                    $query->where('tip_encontro', 'P');
                })
                ->get()
                ->pluck('idt_evento')
                ->toArray();

            $eventosInscritos = Participante::where('idt_pessoa', $pessoa->idt_pessoa)
                ->whereHas('evento', function ($query) {
                    $query->where('tip_encontro', 'E');
                })
                ->get()
                ->pluck('idt_evento')
                ->toArray();

            $desafiosInscritos = Participante::where('idt_pessoa', $pessoa->idt_pessoa)
                ->whereHas('evento', function ($query) {
                    $query->where('tip_encontro', 'D');
                })
                ->get()
                ->pluck('idt_evento')
                ->toArray();
        }

        $query = Evento::with(['movimento', 'foto'])->orderBy('dat_inicio', 'desc');

        if ($search) {
            $query->search($search);
        }

        $eventos = $query->paginate(12);

        return view('evento.list', compact(
            'eventos',
            'search',
            'posEncontrosInscritos',
            'eventosInscritos',
            'desafiosInscritos',
            'pessoa'
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

    public function info(Evento $evento)
    {
        $pessoa = UserService::createPessoaFromLoggedUser();

        $posEncontrosInscritos = [];
        $eventosInscritos = [];

        if ($pessoa) {
            $posEncontrosInscritos = Participante::where('idt_pessoa', $pessoa->idt_pessoa)
                ->pluck('idt_evento')
                ->toArray();

            // Esta parte já está correta, busca os IDs dos eventos que a pessoa se voluntariou
            $eventosInscritos = Voluntario::where('idt_pessoa', $pessoa->idt_pessoa)
                ->pluck('idt_evento')
                ->toArray();
        }

        return view('evento.info', compact('evento', 'pessoa', 'posEncontrosInscritos', 'eventosInscritos'));
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
            ->with('success', 'Sua participação foi confirmada. Até lá!');
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
