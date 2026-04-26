<?php

namespace Modules\Vendinha\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Vendinha\Models\VendinhaProduto;

class ProdutoController extends Controller
{
    public function create()
    {
        return view('vendinha::produtos.form', [
            'produto' => new VendinhaProduto(['ind_ativo' => true]),
        ]);
    }

    public function store(Request $request)
    {
        VendinhaProduto::create($this->validated($request));

        return redirect()->route('vendinha.dashboard')->with('success', 'Produto cadastrado com sucesso.');
    }

    public function edit(VendinhaProduto $produto)
    {
        return view('vendinha::produtos.form', compact('produto'));
    }

    public function update(Request $request, VendinhaProduto $produto)
    {
        $produto->update($this->validated($request));

        return redirect()->route('vendinha.dashboard')->with('success', 'Produto atualizado com sucesso.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        return $request->validate([
            'nom_produto' => ['required', 'string', 'max:120'],
            'des_produto' => ['nullable', 'string', 'max:1000'],
            'vlr_custo' => ['required', 'numeric', 'min:0'],
            'vlr_venda' => ['required', 'numeric', 'min:0'],
            'qtd_estoque' => ['nullable', 'integer', 'min:0'],
            'ind_ativo' => ['nullable', 'boolean'],
        ]) + ['ind_ativo' => false];
    }
}
