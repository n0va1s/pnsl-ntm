<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventoRequest;
use App\Models\Evento;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EventoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $search = $request->get('search');
        
        $eventos = Evento::when($search, function ($query, $search) {
                return $query->search($search);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('evento.list', compact('eventos', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('evento.form');
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
        return view('evento.form', compact('evento'));
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Evento $evento): RedirectResponse
    {
        $evento->delete();

        return redirect()
            ->route('eventos.index')
            ->with('success', 'Evento excluído com sucesso!');
    }
}