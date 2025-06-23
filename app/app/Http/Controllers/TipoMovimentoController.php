<?php

namespace App\Http\Controllers;

use App\Models\TipoMovimento;
use Illuminate\Http\Request;

class TipoMovimentoController extends Controller
{
    //Por ser pequeno, optei por nao criar um request
    protected array $regras = [
        'nom_movimento' => 'required|string|max:255',
        'des_sigla' => 'required|string|max:10',
        'dat_inicio' => 'required|date',
    ];

    public function index()
    {
        $tipos = TipoMovimento::orderBy('dat_inicio', 'desc')->paginate(10);
        return view('configuracoes.TipoMovimentoList', compact('tipos'));
    }

    public function create()
    {
        return view('configuracoes.TipoMovimentoForm', ['tipo' => new TipoMovimento()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->regras);

        TipoMovimento::create($validated);

        return redirect()->route('tiposmovimentos.index')
            ->with('success', 'Movimento criado com sucesso!');
    }

    public function edit($id)
    {
        $tipo = TipoMovimento::findOrFail($id);
        return view('configuracoes.TipoMovimentoForm', compact('tipo'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate($this->regras);
        $tipo = TipoMovimento::findOrFail($id);
        $tipo->update($validated);

        return redirect()->route('tiposmovimentos.index')
            ->with('success', 'Movimento atualizado com sucesso!');
    }

    public function destroy($id)
    {
        $tipo = TipoMovimento::findOrFail($id);

        try {
            $tipo->delete();
            return redirect()->route('tiposmovimentos.index')
                ->with('success', 'Movimento excluído com sucesso!');
        } catch (\Throwable $e) {
            return redirect()->route('tiposmovimentos.index')
                ->with('error', 'Erro ao excluir o movimento. Verifique se há vínculos.');
        }
    }
}
