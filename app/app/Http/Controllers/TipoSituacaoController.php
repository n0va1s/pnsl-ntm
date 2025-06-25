<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TipoSituacao;

class TipoSituacaoController extends Controller
{

    protected array $regras = [
        'des_situacao' => 'required|string|max:255',
    ];

    public function index()
    {
        {
        $situacoes = \App\Models\TipoSituacao::all();
        return view('configuracoes.TipoSituacaoList', compact('situacoes'));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('configuracoes.TipoSituacaoForm', [
            'situacao' => new TipoSituacao(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate($this->regras);

        TipoSituacao::create($validated);

        return redirect()->route('tiposituacao.index')
            ->with('success', 'Tipo de situação adicionado com sucesso!');
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
        $situacao = TipoSituacao::findOrFail($id);
        return view('configuracoes.TipoSituacaoForm', compact('situacao'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate($this->regras);

        $situacao = TipoSituacao::findOrFail($id);
        $situacao->update($validated);

        return redirect()->route('tiposituacao.index')
            ->with('success', 'Tipo de situação atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
    $situacao = TipoSituacao::findOrFail($id);

        try {
            $situacao->delete();
            return redirect()->route('tiposituacao.index')
                ->with('success', 'Tipo de situação excluído com sucesso!');
        } catch (\Exception $e) {
            return redirect()->route('tiposituacao.index')
                ->with('error', 'Erro ao excluir tipo de situação: ' . $e->getMessage());
        }
    }
}
