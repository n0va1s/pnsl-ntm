<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Trabalhador;
use App\Models\TipoEquipe;
use App\Models\Pessoa;

class TrabalhadorController extends Controller
{

    protected array $regras = [
        'nom_completo' => 'required|string|max:255',
        'num_telefone' => 'required|string|max:11',
        'equipes' => 'nullable|array',
        'equipes.*' => 'string|in:Alimentação,Bandinha,Emaús,Limpeza,Oração,Recepção,Reportagem,Sala,Secretaria,Troca de ideias,Vendinha',
        'des_habilidades' => 'nullable|string|max:255',
        'bol_primeira_vez' => 'nullable|boolean'

    ];

    public function index()
    {
        $trabalhadores = \App\Models\Pessoa::all();
        return view('trabalhadores.list', compact('trabalhadores'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        $equipes = TipoEquipe::all();
        return view('trabalhadores.form', [
            'trabalhador' => new Trabalhador(),
            'equipes' => $equipes,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate($this->regras);

        Trabalhador::create($validated);


        return redirect()->route('trabalhadores.index')
            ->with('success', 'Inscrição para trabalhar feita com sucesso!');
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
    public function edit(string $idt_pessoa)
    {
        $trabalhador = Pessoa::where('idt_pessoa', $idt_pessoa)->firstOrFail();
        $equipes = TipoEquipe::all();

        return view('trabalhadores.form', compact('trabalhador', 'equipes'));
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
