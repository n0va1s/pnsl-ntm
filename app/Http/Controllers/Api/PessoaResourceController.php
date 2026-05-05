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
     * Retorna a query base para buscar pessoas vinculadas ao Segue-me
     */
    private function getQuerySgm()
    {
        return Pessoa::whereHas('fichas', function ($q) {
            $q->join('ficha_sgm', 'ficha.idt_ficha', '=', 'ficha_sgm.idt_ficha');
        });
    }

    /**
     * Retorna todas as pessoas vinculadas ao Segue-me
     */
    public function indexSgm()
    {
        return PessoaResource::collection($this->getQuerySgm()->get());
    }

    /**
     * Retorna apenas os candidatos vinculados ao Segue-me (sem usuário logado)
     */
    public function candidatosSgm()
    {
        return PessoaResource::collection($this->getQuerySgm()->candidatos()->get());
    }

    /**
     * Retorna apenas os usuários vinculados ao Segue-me
     */
    public function usuariosSgm()
    {
        return PessoaResource::collection($this->getQuerySgm()->pessoasComUsuario()->get());
    }

    /**
     * Retorna apenas uma pessoa específica vinculada ao Segue-me
     */
    public function showSgm(string $id)
    {
        // Busca a pessoa pelo ID somente se possuir a ficha do SGM
        $pessoa = Pessoa::whereHas('fichas', function ($q) {
            $q->join('ficha_sgm', 'ficha.idt_ficha', '=', 'ficha_sgm.idt_ficha');
        })->findOrFail($id);

        return new PessoaResource($pessoa);
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
