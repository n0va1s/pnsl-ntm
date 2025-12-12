<?php

namespace App\Http\Controllers;

use App\Models\TipoRestricao;
use Illuminate\Http\Request;

class TipoRestricaoController extends Controller
{
    protected array $regras = [
        'des_restricao' => 'required|string|max:255',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $restricoes = \App\Models\TipoRestricao::all();

        return view('configuracoes.TipoRestricaoList', compact('restricoes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('configuracoes.TipoRestricaoForm', [
            'restricao' => new TipoRestricao,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $validated = $request->validate($this->regras);

        TipoRestricao::create($validated);

        return redirect()->route('restricao.index')
            ->with('success', 'Tipo de restricao adicionado com sucesso!');
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
        $restricao = TipoRestricao::findOrFail($id);

        return view('configuracoes.TipoRestricaoForm', compact('restricao'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate($this->regras);
        $restricao = TipoRestricao::findOrFail($id);
        $restricao->update($validated);

        return redirect()->route('restricao.index')
            ->with('success', 'Tipo de restrição atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $restricao = TipoRestricao::findOrFail($id);

        try {
            $restricao->delete();

            return redirect()->route('restricao.index')
                ->with('success', 'Tipo de restrição excluído com sucesso!');
        } catch (\Exception $e) {
            return redirect()->route('restricao.index')
                ->with('error', 'Erro ao excluir tipo de restrição: ' . $e->getMessage());
        }
    }
}
