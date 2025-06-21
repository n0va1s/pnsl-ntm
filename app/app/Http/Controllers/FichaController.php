<?php

namespace App\Http\Controllers;

use App\Http\Requests\FichaEccRequest;
use App\Http\Requests\FichaRequest;
use App\Http\Requests\FichaVemRequest;
use App\Models\Ficha;
use Illuminate\Http\Request;

class FichaController extends Controller
{
    /**
     * Listagem das fichas.
     */
    public function index()
    {
        $fichas = Ficha::with(['fichaVem', 'fichaEcc'])->paginate(10);

        return view('fichas.index', compact('fichas'));
    }

    /**
     * Formulário de criação.
     */
    public function create()
    {
        return view('fichas.create');
    }

    /**
     * Armazenar nova ficha (com dados opcionais de vem/ecc).
     */
    public function store(
        FichaRequest  $fichaRequest,
        FichaVemRequest $vemRequest,
        FichaEccRequest $eccRequest
    ) {
        $data = $fichaRequest->validate();

        $ficha = Ficha::create($data);

        // Cria FichaVem se enviado
        if ($fichaRequest->has('vem')) {
            $vemData = $vemRequest->validate();

            $ficha->fichaVem()->create($vemData['vem']);
        }

        // Cria FichaEcc se enviado
        if ($fichaRequest->has('ecc')) {
            $eccData = $eccRequest->validate();

            $ficha->fichaEcc()->create($eccData['ecc']);
        }

        return redirect()->route('fichas.index')->with('success', 'Ficha cadastrada com sucesso!');
    }

    /**
     * Exibir ficha individual.
     */
    public function show(Ficha $ficha)
    {
        //$ficha = Ficha::with(['fichaVem', 'fichaEcc'])->findOrFail($id);

        return view('fichas.show', compact('ficha'));
    }

    /**
     * Formulário de edição.
     */
    public function edit(Ficha $ficha)
    {
        //$ficha = Ficha::with(['fichaVem', 'fichaEcc'])->findOrFail($id);

        return view('fichas.edit', compact('ficha'));
    }

    /**
     * Atualizar ficha e dados relacionados.
     */
    public function update(
        FichaRequest  $fichaRequest,
        FichaVemRequest $vemRequest,
        FichaEccRequest $eccRequest,
        Ficha $ficha
    ) {
        //$ficha = Ficha::findOrFail($id);

        $data = $fichaRequest->validate();

        $ficha->update($data);

        if ($fichaRequest->has('vem')) {
            $vemData = $vemRequest->validate();

            $ficha->fichaVem()->updateOrCreate([], $vemData['vem']);
        }

        if ($fichaRequest->has('ecc')) {
            $eccData = $eccRequest->validate();

            $ficha->fichaEcc()->updateOrCreate([], $eccData['ecc']);
        }

        return redirect()->route('fichas.index')->with('success', 'Ficha atualizada com sucesso!');
    }

    /**
     * Remover ficha.
     */
    public function destroy(Ficha $ficha)
    {
        //$ficha = Ficha::findOrFail($id);

        // Deleta FichaVem e FichaEcc com cascade no banco
        // Nao ha necessidade de deletar os filhos aqui
        $ficha->delete();

        return redirect()->route('fichas.index')->with('success', 'Ficha excluída com sucesso!');
    }
}
