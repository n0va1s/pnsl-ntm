<?php

namespace App\Http\Controllers;

use App\Models\TipoResponsavel;
use Illuminate\Http\Request;

class TipoResponsavelController extends Controller
{
    protected array $regras = [
        'des_responsavel' => 'required|string|max:255',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $responsavel = \App\Models\TipoResponsavel::all();

        return view('configuracoes.TipoResponsavelList', compact('responsavel'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('configuracoes.TipoResponsavelForm', [
            'responsavel' => new TipoResponsavel,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $validated = $request->validate($this->regras);

        TipoResponsavel::create($validated);

        return redirect()->route('responsavel.index')
            ->with('success', 'Tipo de responsável adicionado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $responsavel = TipoResponsavel::findOrFail($id);

        return view('configuracoes.TipoResponsavelForm', compact('responsavel'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate($this->regras);
        $responsavel = TipoResponsavel::findOrFail($id);
        $responsavel->update($validated);

        return redirect()->route('responsavel.index')
            ->with('success', 'Tipo de responsável atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $responsavel = TipoResponsavel::findOrFail($id);

        try {
            $responsavel->delete();

            return redirect()->route('responsavel.index')
                ->with('success', 'Tipo de responsável excluído com sucesso!');
        } catch (\Exception $e) {
            return redirect()->route('responsavel.index')
                ->with('error', 'Erro ao excluir tipo de responsável: '.$e->getMessage());
        }
    }
}
