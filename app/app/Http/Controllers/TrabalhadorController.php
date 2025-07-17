<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\Pessoa;
use App\Models\TipoEquipe;
use App\Models\Trabalhador;
use App\Models\Voluntario;
use App\Services\PessoaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class TrabalhadorController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->get('search');
        $eventoId = $request->get('evento');

        $evento = null;
        if ($eventoId) {
            $evento = Evento::find($eventoId);
        }

        $trabalhadores = Trabalhador::with(['pessoa', 'evento', 'equipe'])
            ->when($search, function ($query, $search) {
                return $query->where('nom_pessoa', 'like', "%{$search}%")
                    ->orWhere('nom_apelido', 'like', "%{$search}%");
            })->when($eventoId, function ($query, $eventoId) {
                return $query->where('idt_evento', $eventoId);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('trabalhador.list', compact('trabalhadores', 'search', 'evento'));
    }

    public function create(Request $request): View
    {
        $eventoId = $request->get('evento');

        $evento = null;
        if ($eventoId) {
            $evento = Evento::find($eventoId);
        }

        $equipes  = TipoEquipe::where('idt_movimento', $evento->idt_movimento ?? null)
            ->select('idt_equipe', 'des_grupo')->get();
        return view('trabalhador.form', compact('equipes', 'evento'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'equipes' => 'required|array|min:3',
            'idt_evento' => 'required|exists:evento,idt_evento',
        ], [
            'equipes.min' => 'Selecione ao menos 3 equipes.',
            'idt_evento.required' => 'O evento é obrigatório.',
        ]);

        $pessoa = PessoaService::criarPessoaAPartirDoUsuario(auth()->user());
        $eventoId = $request->input('idt_evento');
        $equipesSelecionadas = collect($request->input('equipes', []))
            ->filter(fn($item) => array_key_exists('selecionado', $item));

        if ($equipesSelecionadas->count() > 3) {
            return back()->withErrors(['equipes' => 'Selecione no máximo 3 equipes.'])->withInput();
        }

        foreach ($equipesSelecionadas as $idt_equipe => $dados) {
            Voluntario::create([
                'idt_pessoa'     => $pessoa->idt_pessoa,
                'idt_evento'     => $eventoId,
                'idt_equipe'     => $idt_equipe,
                'txt_habilidade' => $dados['habilidade'] ?? null,
            ]);
        }

        return redirect()
            ->route('eventos.index')
            ->with('success', 'Recebemos seu pedido e entraremos em contato.');
    }

    //Lista de voluntarios para indicacao da equipe definitiva
    public function mount(Request $request)
    {
        $eventoId = $request->get('evento');

        $evento = Evento::find($eventoId);

        $voluntarios = $evento
            ? Voluntario::listarAgrupadoPorPessoa($evento->idt_evento)
            : collect(); // coleção vazia se não tiver evento

        return view('evento.montagem', [
            'evento' => $evento,
            'equipes' => TipoEquipe::select('idt_equipe', 'des_grupo')
                ->where('idt_movimento', $evento->idt_movimento)
                ->get(),
            'voluntarios' => $voluntarios,
        ]);
    }

    // Confirma a equipe que o voluntario vai trabalhar
    // indica tambem se a pessoa e o coordenador ou a primeira vez
    public function confirm(Request $request)
    {
        $request->validate([
            'idt_voluntario' => 'required|exists:voluntario,idt_voluntario',
            'idt_equipe' => 'required|exists:tipo_equipe,idt_equipe',
            'ind_coordenador' => 'nullable|boolean',
            'ind_primeira_vez' => 'nullable|boolean',
        ], [
            'idt_voluntario.required' => 'O voluntário é obrigatório.',
            'idt_equipe.required' => 'A equipe é obrigatória.',
        ]);

        $voluntario = Voluntario::find($request->input('idt_voluntario'));

        if (!$voluntario) {
            return redirect()
                ->back()
                ->with('error', 'Voluntário não encontrado.');
        }

        Trabalhador::updateOrCreate([
            'idt_pessoa' => $voluntario->idt_pessoa,
            'idt_evento' => $voluntario->idt_evento,
            'idt_equipe' => $voluntario->idt_equipe,
            'idt_voluntario' => $voluntario->idt_voluntario,
            'ind_coordenador' => $request->get('ind_coordenador'),
            'ind_primeira_vez' => $request->get('ind_primeira_vez'),
        ]);

        return redirect()
            ->back()
            ->with('success', 'Trabalhador confirmado.');
    }

    // Gera o quadrante dos trabalhadores do evento
    public function generate(Request $request)
    {
        $eventoId = $request->get('evento');

        $evento = Evento::find($eventoId);

        $trabalhadoresPorEquipe = collect();

        if ($evento) {
            // Carrega todos os trabalhadores do evento com suas equipes e pessoas
            $trabalhadores = Trabalhador::with(['pessoa', 'equipe'])
                ->where('idt_evento', $evento->idt_evento)
                ->get();

            // Agrupa por equipe e ordena coordenadores no topo
            $trabalhadoresPorEquipe = $trabalhadores
                ->groupBy(fn($t) => $t->equipe->des_grupo)
                ->map(function (Collection $grupo) {
                    return $grupo->sortByDesc('ind_coordenador')->values();
                });
        }

        return view('evento.quadrante', [
            'evento' => $evento,
            'trabalhadoresPorEquipe' => $trabalhadoresPorEquipe,
        ]);
    }

    public function review(Request $request)
    {
        $trabalhador = Trabalhador::with(['pessoa', 'evento', 'equipe'])
            ->where('idt_evento', $request->get('evento'))
            ->where('idt_equipe', $request->get('equipe'))
            ->where('idt_pessoa', $request->get('pessoa'))
            ->first();

        return view('trabalhador.avaliacao', compact('trabalhador'));
    }

    public function send(Request $request)
    {
        $dados = $request->validate([
            'idt_pessoa' => 'required',
            'idt_equipe' => 'required',
            'idt_evento' => 'required',
            'ind_recomendado'  => 'nullable|boolean',
            'ind_lideranca'  => 'nullable|boolean',
            'ind_destaque'  => 'nullable|boolean',
            'ind_camiseta_pediu'  => 'nullable|boolean',
            'ind_camiseta_pagou'  => 'nullable|boolean',
        ], [
            'idt_trabalhador.required' => 'O trabalhador é obrigatório.',
            'idt_equipe.required' => 'A equipe é obrigatória.',
            'idt_evento.required' => 'O evento é obrigatório.',
        ]);

        $trabalhador = Trabalhador::where('idt_evento', $dados['idt_evento'])
            ->where('idt_equipe', $dados['idt_equipe'])
            ->where('idt_pessoa', $dados['idt_pessoa'])
            ->first();

        $trabalhador->ind_avaliacao = true;
        $trabalhador->update($dados);

        return redirect()
            ->route('quadrante.list', ['evento' => $dados['idt_evento']])
            ->with('success', 'Avaliação realizada. Valeu!');
    }
}
