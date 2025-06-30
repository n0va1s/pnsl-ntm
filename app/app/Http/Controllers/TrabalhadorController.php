<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Trabalhador;
use App\Models\TipoEquipe;
use App\Models\Pessoa;
use App\Models\Evento;

class TrabalhadorController extends Controller
{

    protected array $regras = [
        'nom_completo' => 'required|string|max:255',
        'num_telefone' => 'required|string|max:11',
        'equipes' => 'nullable|array',
        'equipes.*' => 'string|in:Alimentação,Bandinha,Emaús,Limpeza,Oração,Recepção,Reportagem,Sala,Secretaria,Troca de ideias,Vendinha',
        'bol_primeira_vez' => 'nullable|boolean',
        'idt_evento' => 'required|exists:evento,idt_evento',

    ];

    public function index()
    {
        $trabalhadores = \App\Models\Trabalhador::with('pessoa', 'evento', 'equipe')
            ->orderBy('nom_completo')
            ->get();
        return view('trabalhadores.list', compact('trabalhadores'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        $equipes = TipoEquipe::all();
        $eventos = Evento::all();

        return view('trabalhadores.form', [
            'trabalhador' => new Trabalhador(),
            'equipes' => $equipes,
            'eventos' => $eventos,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate($this->regras);

        $pessoa = auth()->user()->pessoa;

        $trablhador = Trabalhador::create([
            'idt_pessoa' => $pessoa->idt_pessoa,
            'nom_completo' => $validated['nom_completo'],
            'num_telefone' => $validated['num_telefone'],
            'bol_primeira_vez' => $validated['bol_primeira_vez'] ?? false,
        ]);


        return redirect()->route('trabalhadores.index')
            ->with('success', 'Inscrição para trabalhar feita com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $idt_pessoa)
    {
        $trabalhador = Pessoa::with('trabalhador.equipe', 'trabalhador.evento')
            ->where('idt_pessoa', $idt_pessoa)
            ->firstOrFail();

            return view('trabalhadores.show', compact('trabalhador'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $idt_pessoa)
    {
        // $trabalhador = Pessoa::where('idt_pessoa', $idt_pessoa)->firstOrFail();
        // $trabalhador = $trabalhador->trabalhador()->first();
        $trabalhador = Trabalhador::with('pessoa', 'evento')->where('idt_pessoa', $idt_pessoa)->firstOrFail();
        $equipes = TipoEquipe::all();
        $eventos = Evento::all();

        return view('trabalhadores.form', compact('trabalhador', 'eventos','equipes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate($this->regras);

        $pessoa = Pessoa::where('idt_pessoa', $id)->firstOrFail();
        $trabalhador = Trabalhador::where('idt_pessoa', $id)->firstOrFail();

        // Apagar após os testes, o nome e telefone não podem ser alterados
        $pessoa->update([
            'nom_pessoa' => $validated['nom_completo'],
            'tel_pessoa' => $validated['num_telefone'],
        ]);



        $trabalhador->idt_evento = $validated['idt_evento'];
        $trabalhador->idt_equipe = $validated['equipes'][0] ?? null;
        $trabalhador ->save();


        return redirect()->route('trabalhadores.index')
            ->with('success', 'Trabalhador atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
