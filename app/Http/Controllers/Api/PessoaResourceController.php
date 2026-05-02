<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pessoa;
use App\Http\Resources\PessoaResource;


class PessoaResourceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pessoas = Pessoa::all();
        return PessoaResource::collection($pessoas);
    }

    /**
     * Retorna apenas as pessoas vinculadas ao Segue-me
     */
    public function indexSgm()
    {
        // Busca Pessoas que possuem Fichas, e garante (através do join)
        // que essa ficha existe fisicamente na tabela exclusiva do Segue-me (ficha_sgm)
        $pessoasSgm = Pessoa::whereHas('fichas', function ($query) {
            $query->join('ficha_sgm', 'ficha.idt_ficha', '=', 'ficha_sgm.idt_ficha');
        })
        ->get();

        return PessoaResource::collection($pessoasSgm);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $pessoa = Pessoa::findOrFail($id);
        return new PessoaResource($pessoa);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
