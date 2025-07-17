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

        //TODO: substituir pela app() instance
        $pessoa = UserService::createPessoaFromLoggedUser();

        // Buscar os eventos ja inscritos da pessoa
        if ($pessoa) {
            $participacoes = Participante::where('idt_pessoa', $pessoa->idt_pessoa)
                ->pluck('idt_evento')
                ->toArray();

            $eventosFeitos = Voluntario::where('idt_pessoa', $pessoa->idt_pessoa)
                ->pluck('idt_evento')
                ->toArray();
        }

        $eventos = Evento::with(['movimento'])
            ->withCount([
                'fichas',
                'voluntarios',
                'trabalhadores',
                'participantes'
            ])->when($search, function ($query, $search) {
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
                'participacoes',
                'eventosFeitos'
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
        return view('evento.form', compact('evento', 'movimentos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EventoRequest $request, Evento $evento): RedirectResponse
    {
        $evento->update($request->validated());

        return redirect()
            ->route('eventos.index')
            ->with('success', 'Evento atualizado com sucesso!');
    }

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
