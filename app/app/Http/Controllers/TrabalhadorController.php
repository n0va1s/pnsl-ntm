<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Trabalhador;
use App\Models\TipoEquipe;
use App\Models\Pessoa;
use App\Models\Evento;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TrabalhadorController extends Controller
{
    protected array $regras = [
        'nom_pessoa' => 'required|string|max:255',
        'tel_pessoa' => 'required|string|max:11',
        'equipes' => 'nullable|array',
        'equipes.*' => 'string|in:Alimentação,Bandinha,Emaús,Limpeza,Oração,Recepção,Reportagem,Sala,Secretaria,Troca de ideias,Vendinha',
        'bol_primeira_vez' => 'nullable|boolean',
        'idt_evento' => 'required|exists:evento,idt_evento',
        'des_habilidades' => 'nullable|string|max:255',
    ];

    // Listagem
    public function index()
    {
        $trabalhadores = Trabalhador::with('pessoa', 'evento', 'equipe')
            ->orderBy('nom_pessoa')
            ->get();

        return view('trabalhadores.list', compact('trabalhadores'));
    }

    // Formulário de criação
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

    // Armazenar novo trabalhador
    public function store(Request $request)
    {
        $validated = $request->validate($this->regras);

        $idtEquipe = null;
        if (!empty($validated['equipes'][0])) {
            $equipeSelecionada = TipoEquipe::where('des_grupo', $validated['equipes'][0])->first();
            if ($equipeSelecionada) {
                $idtEquipe = $equipeSelecionada->idt_equipe;
            }
        }

        // Cria a pessoa antes de criar o trabalhador
        $pessoa = Pessoa::create([
            'nom_pessoa' => $validated['nom_pessoa'],
            'tel_pessoa' => $validated['tel_pessoa'],
        ]);

        Trabalhador::create([
            'idt_pessoa' => $pessoa->idt_pessoa,
            'bol_primeira_vez' => $validated['bol_primeira_vez'] ?? false,
            'idt_evento' => $validated['idt_evento'],
            'idt_equipe' => $idtEquipe,
        ]);

        // return redirect()->route('trabalhadores.index')
        //     ->with('success', 'Inscrição para trabalhar feita com sucesso!');

        return redirect('/teste-redirect');
    }

    // Exibir trabalhador
    public function show(string $idt_pessoa)
    {
        $trabalhador = Pessoa::with('trabalhador.equipe', 'trabalhador.evento')
            ->where('idt_pessoa', $idt_pessoa)
            ->firstOrFail();

        return view('trabalhadores.show', compact('trabalhador'));
    }

    // Formulário de edição
    public function edit(string $idt_pessoa)
    {
        $trabalhador = Trabalhador::with('pessoa', 'evento', 'equipe')
            ->where('idt_pessoa', $idt_pessoa)
            ->orderBy('idt_evento', 'desc') // Pega o evento mais recente (ID mais alto)
            ->firstOrFail();
        $equipes = TipoEquipe::all();
        $eventos = Evento::all();

        return view('trabalhadores.form', compact('trabalhador', 'eventos', 'equipes'));
    }

    // Atualizar trabalhador
    public function update(Request $request, string $idt_pessoa)
    {
        $validated = $request->validate($this->regras);

        try {
            // 1. Encontre o Trabalhador pelo ID da pessoa (chave primária)
            // Usar firstOrFail() garante que se o trabalhador não for encontrado, uma exceção será lançada.
            $trabalhador = Trabalhador::where('idt_pessoa', $idt_pessoa)->firstOrFail();
            // 2. Atualiza a Pessoa associada (isso já estava correto)
            $trabalhador->pessoa->update([
                'nom_pessoa' => $validated['nom_pessoa'],
                'tel_pessoa' => $validated['tel_pessoa'],
                // Adicione outros campos da Pessoa aqui se eles puderem ser atualizados no form
            ]);

            // 3. Dados para atualizar o registro do Trabalhador
            $dadosTrabalhador = [
                'idt_evento' => $validated['idt_evento'], // <-- Use o NOVO ID DO EVENTO do formulário
                'bol_primeira_vez' => $request->boolean('bol_primeira_vez'),
                // 'des_habilidades' => $validated['des_habilidades'] ?? null, // Ative se usar o campo de habilidades
            ];

            // 4. Lógica para a Equipe (baseado na sua estrutura de relacionamento 1:1 ou 1:N)
            // Se `trabalhador` tem uma coluna `idt_equipe` que armazena o ID de UMA equipe:
            $idtEquipe = null; // Valor padrão se nenhuma equipe for selecionada
            if (!empty($validated['equipes'][0])) { // Pega a primeira equipe selecionada dos checkboxes
                $equipeSelecionada = TipoEquipe::where('des_grupo', $validated['equipes'][0])->first();
                if ($equipeSelecionada) {
                    $idtEquipe = $equipeSelecionada->idt_equipe;
                }
            }
            $dadosTrabalhador['idt_equipe'] = $idtEquipe; // Adiciona o ID da equipe aos dados do trabalhador

            // 5. Realiza a atualização do Trabalhador
            $trabalhador->update($dadosTrabalhador);

        } catch (\Exception $e) {
            // É crucial logar o erro para depuração
            Log::error('Erro ao atualizar trabalhador: ' . $e->getMessage(), ['exception' => $e, 'request_data' => $request->all()]);
            // Em produção, você pode retornar uma mensagem mais amigável
            return redirect()->back()->with('error', 'Ocorreu um erro inesperado ao atualizar o trabalhador. Por favor, tente novamente.')->withInput();
        }

        // 6. Redirecionar após o sucesso
        return redirect()->route('trabalhadores.index')
            ->with('success', 'Trabalhador atualizado com sucesso!');
    }

    // Remover trabalhador (não implementado)
    public function destroy(string $id)
    {
        //
    }
}
