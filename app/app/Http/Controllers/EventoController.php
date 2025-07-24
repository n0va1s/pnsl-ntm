<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventoRequest;
use App\Models\Evento;
use App\Models\Participante;
use App\Models\Pessoa;
use App\Models\TipoMovimento;
use App\Models\User;
use App\Models\Voluntario;
use App\Services\UserService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class EventoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');

        // Obter ou criar a pessoa vinculada ao usuário logado
        // A linha "TODO: substituir pela app() instance" está OK,
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

        $eventos = Evento::with(['movimento'])
            ->withCount([
                'fichas',
                'voluntarios as voluntarios_count' => function ($query) {
                    $query->select(DB::raw('count(DISTINCT idt_pessoa)')) // idt_pessoa idt_evento idt_equipe
                        ->whereNull('idt_trabalhador'); // voluntario ainda nao confirmados como trabalhador
                },
                'trabalhadores',
                'participantes'
            ])
            ->when($search, function ($query, $search) {
                return $query->search($search);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view(
            'evento.list',
            compact(
                'eventos',
                'search',
                'pessoa',
                'posEncontrosInscritos',
                'eventosInscritos'
            )
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $movimentos = TipoMovimento::all();
        return view(
            'evento.form',
            [
                'evento' => new Evento,
                'movimentos' => $movimentos
            ]
        );
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(EventoRequest $request): RedirectResponse
    {
        Evento::create($request->validated());

        return redirect()
            ->route('eventos.index')
            ->with('success', 'Evento criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Evento $evento): View
    {
        return view('evento.form', compact('evento'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Evento $evento): View
    {
        $movimentos = TipoMovimento::all();
        $evento->load('foto'); // Carrega a foto associada ao evento, se existir
        return view('evento.form', compact('evento', 'movimentos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EventoRequest $request, Evento $evento): RedirectResponse
    {
        $evento->update($request->validated());

        // Foto
        if ($request->hasFile('med_foto')) {
            $arquivo = $request->file('med_foto');
            $caminho = $arquivo->store('fotos/evento/', 'public'); // pasta 'storage/app/public/fotos'

            if ($evento->foto) {
                $evento->foto()->update(['med_foto' => $caminho]);
            } else {
                $evento->foto()->create(['med_foto' => $caminho]);
            }
        }

        return redirect()
            ->route('eventos.index')
            ->with('success', 'Evento atualizado com sucesso!');
    }

    // Confirmar a participacao de uma pessoa em um evento
    public function confirm(Evento $evento, Pessoa $pessoa): RedirectResponse
    {

        Participante::create([
            'idt_evento' => $evento->idt_evento,
            'idt_pessoa' => $pessoa->idt_pessoa,
        ]);

        return redirect()
            ->route('eventos.index')
            ->with('success', 'Sua participação  foi confirmada. Até lá!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Evento $evento): RedirectResponse
    {
        try {
            $evento->delete();

            return redirect()
                ->route('eventos.index')
                ->with('success', 'Evento excluído com sucesso!');
        } catch (QueryException $e) {
            Log::error('Erro ao excluir evento:', [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
            if ($e->getCode() === '23000') {
                return redirect()
                    ->route('eventos.index')
                    ->with('error', 'Não é possível excluir este evento. Ele está associado a fichas ou participantes.');
            } elseif ($e->getCode() === '42000') {
                return redirect()
                    ->route('eventos.index')
                    ->with('error', 'Erro de sintaxe na consulta SQL.');
            }

            return redirect()
                ->route('eventos.index')
                ->with('error', 'Erro ao tentar excluir o evento.');
        }
    }
}
