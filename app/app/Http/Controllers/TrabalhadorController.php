<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\TipoEquipe;
use App\Models\Trabalhador;
use App\Models\Voluntario;
use App\Services\UserService;
use App\Services\VoluntarioService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class TrabalhadorController extends Controller
{
    protected $voluntarioService;

    public function __construct(VoluntarioService $voluntarioService)
    {
        $this->voluntarioService = $voluntarioService;
    }

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
        $dados = $request->validate([
            'idt_evento' => 'required|exists:evento,idt_evento',
            'equipes' => 'required|array',
            'equipes.*.selecionado' => 'nullable|in:1',
            'equipes.*.habilidade' => 'nullable|string|max:500',
        ], [
            'idt_evento.required' => 'O evento é obrigatório.',
            'idt_evento.exists' => 'O evento selecionado não é válido.',
            'equipes.required' => 'Selecione ao menos uma equipe para se voluntariar.',
            'equipes.array' => 'As equipes devem ser fornecidas em um formato válido.',
            'equipes.*.selecionado.in' => 'O valor de seleção para a equipe não é válido.',
            'equipes.*.habilidade.string' => 'A habilidade deve ser um texto.',
            'equipes.*.habilidade.max' => 'A habilidade deve ter no máximo :max caracteres.',
        ]);

        try {
            $pessoa = UserService::createPessoaFromLoggedUser();

            $this->voluntarioService->candidatura(
                $dados['equipes'],
                $dados['idt_evento'],
                $pessoa
            );
            
            return redirect()
                ->route('eventos.index')
                ->with('success', 'Suas candidaturas foram enviadas com sucesso! Entraremos em contato em breve.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Erro ao candidatar voluntário: ' . $e->getMessage(), ['exception' => $e]);
            return back()->with('error', 'Ocorreu um erro ao registrar suas candidaturas. Por favor, tente novamente.')->withInput();
        }

        // 4. Salvar os voluntários para cada equipe selecionada
        // Apaga o voluntario anterior para o evento
        try {
            // Remove pedidos anteriores para o mesmo evento da mesma pessoa
            Voluntario::where('idt_pessoa', $pessoa->idt_pessoa)
                ->where('idt_evento', $dados['idt_evento'])
                ->delete();

            foreach ($equipesSelecionadas as $equipeId => $habilidade) {
                Voluntario::create([
                    'idt_pessoa' => $pessoa->idt_pessoa,
                    'idt_evento' => $dados['idt_evento'],
                    'idt_equipe' => $equipeId,
                    'txt_habilidade' => $habilidade,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erro ao salvar voluntário: ' . $e->getMessage(), ['exception' => $e]);
            return back()->with('error', 'Ocorreu um erro ao registrar suas candidaturas. Por favor, tente novamente.')->withInput();
        }

        return redirect()
            ->route('eventos.index')
            ->with('success', 'Suas candidaturas foram enviadas com sucesso! Entraremos em contato em breve.');
    }

    //Lista de voluntarios para indicacao das equipes que ele(a) querem trabalhar
    public function mount(Request $request): View
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
    public function confirm(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'idt_voluntario' => 'required|exists:voluntario,idt_voluntario',
            'idt_equipe' => 'required|exists:tipo_equipe,idt_equipe',
            'ind_coordenador' => 'nullable|boolean',
            'ind_primeira_vez' => 'nullable|boolean',
        ], [
            'idt_voluntario.required' => 'O voluntário é obrigatório.',
            'idt_equipe.required' => 'A equipe é obrigatória.',
        ]);

        try {
            // 2. Delegar a lógica de negócio para o serviço
            $this->voluntarioService->confirmacao(
                $dados['idt_voluntario'],
                $dados['idt_equipe'],
                $dados['ind_coordenador'] ?? false,
                $dados['ind_primeira_vez'] ?? false
            );

            // 3. Obter o idt_evento para o redirecionamento
            $voluntario = Voluntario::find($dados['idt_voluntario']);
            $idt_evento = $voluntario ? $voluntario->idt_evento : null;

            return redirect()
                ->route('montagem.confirm', ['evento' => $idt_evento])
                ->with('success', 'Trabalhador confirmado e voluntário atualizado com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao confirmar trabalhador: ' . $e->getMessage(), ['exception' => $e]);
            return back()->with('error', $e->getMessage() ?: 'Ocorreu um erro ao confirmar o trabalhador. Por favor, tente novamente.');
        }
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

        if (!$trabalhador) {
            return redirect()->back()->with('error', 'Trabalhador não encontrado.');
        }

        // Atualiza os campos booleanos, se existirem
        $trabalhador->ind_recomendado = $dados['ind_recomendado'] ?? false;
        $trabalhador->ind_lideranca = $dados['ind_lideranca'] ?? false;
        $trabalhador->ind_destaque = $dados['ind_destaque'] ?? false;
        $trabalhador->ind_camiseta_pediu = $dados['ind_camiseta_pediu'] ?? false;
        $trabalhador->ind_camiseta_pagou = $dados['ind_camiseta_pagou'] ?? false;

        $trabalhador->save();

        return redirect()->route('trabalhadores.index')
            ->with('success', 'Trabalhador atualizado com sucesso!');
    }

    // Remover trabalhador (não implementado)
    public function destroy($id)
    {
        //
    }
}
