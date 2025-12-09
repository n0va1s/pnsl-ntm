<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\TipoEquipe;
use App\Models\Trabalhador;
use App\Models\Voluntario;
use App\Services\VoluntarioService;
use App\Traits\LogContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TrabalhadorController extends Controller
{
    use LogContext;

    protected $voluntarioService;

    public function __construct(VoluntarioService $voluntarioService)
    {
        $this->voluntarioService = $voluntarioService;
    }

    public function index(Request $request): View
    {
        $start = microtime(true);
        $context = $this->getLogContext($request);

        $search = $request->get('search');
        $idt_evento = $request->get('evento');
        $idt_equipe = $request->get('equipe');
        $evento = null;

        Log::info('Requisição de listagem de trabalhadores iniciada', array_merge($context, [
            'search_term' => $search,
            'evento_filtro' => $idt_evento,
            'equipe_filtro' => $idt_equipe,
        ]));

        if ($idt_evento) {
            $evento = Evento::find($idt_evento);
        }

        $equipes = TipoEquipe::where('idt_movimento', $evento->idt_movimento ?? null)
            ->select('idt_equipe', 'des_grupo')->get();

        $trabalhadores = Trabalhador::with([
            'pessoa' => function ($query) {
                $query->select('idt_pessoa', 'nom_pessoa', 'nom_apelido');
            },
            'evento' => function ($query) {
                $query->select('idt_evento', 'des_evento');
            },
            'equipe',
        ])
            ->when($search, function ($query, $search) {
                return $query->whereHas('pessoa', function ($q) use ($search) {
                    $q->searchByName($search);
                });
            })->when($idt_equipe, function ($query, $idt_equipe) {
                return $query->where('idt_equipe', $idt_equipe);
            })->evento($idt_evento)
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        $duration = round((microtime(true) - $start) * 1000, 2);
        Log::notice('Listagem de trabalhadores concluída com sucesso', array_merge($context, [
            'total_trabalhadores' => $trabalhadores->total(),
            'duration_ms' => $duration,
        ]));

        return view('trabalhador.list', compact(
            'trabalhadores',
            'search',
            'evento',
            'equipes',
            'idt_equipe'
        ));
    }

    public function create(Request $request): View
    {
        $start = microtime(true);
        $context = $this->getLogContext($request);
        $eventoId = $request->get('evento');
        Log::info('Acesso ao formulário de candidatura de trabalhador', array_merge($context, ['evento_id' => $eventoId]));

        $evento = null;
        if ($eventoId) {
            $evento = Evento::find($eventoId);
        }

        $equipes = TipoEquipe::where('idt_movimento', $evento->idt_movimento ?? null)
            ->select('idt_equipe', 'des_grupo')->get();

        $duration = round((microtime(true) - $start) * 1000, 2);

        Log::notice('Equipes obtidas', array_merge($context, [
            'pessoa_id' => $equipes->count(),
            'duration_ms' => $duration,
        ]));

        return view('trabalhador.form', compact('equipes', 'evento'));
    }

    public function store(Request $request)
    {
        $start = microtime(true);
        $context = $this->getLogContext($request);
        Log::info('Tentativa de registro de candidatura de trabalhador', array_merge($context, [
            'evento_id' => $request->input('idt_evento'),
            'total_equipes_enviadas' => count($request->input('equipes', [])),
        ]));

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
            $pessoa = Auth::user()->pessoa;
            Log::info('Pessoa autenticada para candidatura', array_merge($context, ['pessoa_id' => $pessoa->idt_pessoa]));

            $this->voluntarioService->candidatura(
                $dados['equipes'],
                $dados['idt_evento'],
                $pessoa
            );

            $duration = round((microtime(true) - $start) * 1000, 2);
            Log::notice('Candidaturas de trabalhador enviadas com sucesso', array_merge($context, [
                'pessoa_id' => $pessoa->idt_pessoa,
                'evento_id' => $dados['idt_evento'],
                'duration_ms' => $duration,
            ]));

            return redirect()
                ->route('eventos.index')
                ->with('success', 'Suas candidaturas foram enviadas com sucesso! Entraremos em contato em breve.');
        } catch (ValidationException $e) {
            $duration = round((microtime(true) - $start) * 1000, 2);
            Log::warning('Erro de validação ao registrar candidatura de trabalhador', array_merge($context, [
                'erros' => $e->errors(),
                'duration_ms' => $duration,
            ]));

            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            $duration = round((microtime(true) - $start) * 1000, 2);
            Log::error('Erro geral ao registrar candidatura de trabalhador', array_merge($context, [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'duration_ms' => $duration,
            ]));

            return back()->with('error', 'Ocorreu um erro ao registrar suas candidaturas. Por favor, tente novamente.')->withInput();
        }
    }

    // Lista de voluntarios para indicacao das equipes que ele(a) querem trabalhar
    public function mount(Request $request): View
    {
        $start = microtime(true);
        $context = $this->getLogContext($request);
        $eventoId = $request->get('evento');
        Log::info('Acesso à tela de montagem de equipes (voluntários agrupados)', array_merge($context, ['evento_id' => $eventoId]));

        $eventoId = $request->get('evento');

        $evento = Evento::find($eventoId);

        $voluntarios = $evento
            ? Voluntario::listarAgrupadoPorPessoa($evento->idt_evento)
            : collect(); // coleção vazia se não tiver evento

        $duration = round((microtime(true) - $start) * 1000, 2);
        Log::notice('Carregamento da montagem de equipes concluída', array_merge($context, [
            'evento_id' => $eventoId,
            'total_voluntarios_agrupados' => $voluntarios->count(),
            'duration_ms' => $duration,
        ]));

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
        $start = microtime(true);
        $context = $this->getLogContext($request);
        Log::info('Tentativa de confirmação de trabalhador para equipe', array_merge($context, [
            'voluntario_id' => $request->input('idt_voluntario'),
            'equipe_id_destino' => $request->input('idt_equipe'),
        ]));

        $dados = $request->validate([
            'idt_voluntario' => 'required|exists:voluntario,idt_voluntario',
            'idt_equipe' => 'required|exists:tipo_equipe,idt_equipe',
            'ind_coordenador' => 'nullable|boolean',
            'ind_primeira_vez' => 'nullable|boolean',
        ], [
            'idt_voluntario.required' => 'O voluntário é obrigatório.',
            'idt_equipe.required' => 'A equipe é obrigatória.',
        ]);

        $voluntario = $this->voluntarioService->confirmacao(
            $dados['idt_voluntario'],
            $dados['idt_equipe'],
            $dados['ind_coordenador'] ?? false,
            $dados['ind_primeira_vez'] ?? false
        );

        $duration = round((microtime(true) - $start) * 1000, 2);
        Log::notice('Trabalhador confirmado com sucesso', array_merge($context, [
            'voluntario_id_origem' => $dados['idt_voluntario'],
            'evento_id' => $voluntario->idt_evento ?? null,
            'equipe_id_destino' => $dados['idt_equipe'],
            'duration_ms' => $duration,
        ]));

        return redirect()
            ->route('eventos.index', ['evento' => $voluntario->idt_evento])
            ->with('success', 'Trabalhador confirmado com sucesso!');
    }

    // Gera o quadrante dos trabalhadores do evento
    public function generate(Request $request)
    {
        $start = microtime(true);
        $context = $this->getLogContext($request);
        $eventoId = $request->get('evento');
        Log::info('Requisição para geração do quadrante de trabalhadores', array_merge($context, ['evento_id' => $eventoId]));

        $evento = Evento::find($eventoId);
        $trabalhadoresPorEquipe = collect();

        if ($evento) {
            // Carrega todos os trabalhadores do evento com suas equipes e pessoas
            $trabalhadores = Trabalhador::with(['pessoa', 'equipe'])
                ->where('idt_evento', $evento->idt_evento)
                ->get();

            // Agrupa por equipe e ordena coordenadores no topo
            $trabalhadoresPorEquipe = $trabalhadores
                ->groupBy(fn ($t) => $t->equipe->des_grupo)
                ->map(function (Collection $grupo) {
                    return $grupo->sortByDesc('ind_coordenador')->values();
                });
        }

        $duration = round((microtime(true) - $start) * 1000, 2);
        Log::notice('Geração do quadrante concluída', array_merge($context, [
            'evento_id' => $eventoId,
            'total_equipes_no_quadrante' => $trabalhadoresPorEquipe->count(),
            'duration_ms' => $duration,
        ]));

        return view('evento.quadrante', [
            'evento' => $evento,
            'trabalhadoresPorEquipe' => $trabalhadoresPorEquipe,
        ]);
    }

    // Avaliacao do trabalhador apos o evento
    public function review(Request $request)
    {
        $start = microtime(true);
        $context = $this->getLogContext($request);
        Log::info('Acesso ao formulário de avaliação de trabalhador', array_merge($context, [
            'evento_id' => $request->get('evento'),
            'equipe_id' => $request->get('equipe'),
            'pessoa_id' => $request->get('pessoa'),
        ]));

        $trabalhador = Trabalhador::with(['pessoa', 'evento', 'equipe'])
            ->where('idt_evento', $request->get('evento'))
            ->where('idt_equipe', $request->get('equipe'))
            ->where('idt_pessoa', $request->get('pessoa'))
            ->first();

        $duration = round((microtime(true) - $start) * 1000, 2);
        Log::notice('Dados do trabalhador para avaliação carregados', array_merge($context, [
            'trabalhador_encontrado' => (bool) $trabalhador,
            'duration_ms' => $duration,
        ]));

        return view('evento.avaliacao', compact('trabalhador'));
    }

    // Salva a avaliacao do trabalhador
    public function send(Request $request)
    {
        $start = microtime(true);
        $context = $this->getLogContext($request);
        $trabalhadorId = $request->input('idt_trabalhador');
        Log::info('Tentativa de registro de avaliação de trabalhador', array_merge($context, [
            'trabalhador_id' => $trabalhadorId,
        ]));

        $dados = $request->validate([
            'idt_trabalhador' => 'required',
            'ind_recomendado' => 'nullable|boolean',
            'ind_lideranca' => 'nullable|boolean',
            'ind_destaque' => 'nullable|boolean',
            'ind_camiseta_pediu' => 'nullable|boolean',
            'ind_camiseta_pagou' => 'nullable|boolean',
        ], [
            'idt_trabalhador.required' => 'O trabalhador é obrigatório.',
        ]);

        $trabalhador = Trabalhador::find($dados['idt_trabalhador']);

        // Atualiza os campos booleanos, se existirem
        $trabalhador->fill([
            'ind_recomendado' => $dados['ind_recomendado'] ?? false,
            'ind_lideranca' => $dados['ind_lideranca'] ?? false,
            'ind_destaque' => $dados['ind_destaque'] ?? false,
            'ind_camiseta_pediu' => $dados['ind_camiseta_pediu'] ?? false,
            'ind_camiseta_pagou' => $dados['ind_camiseta_pagou'] ?? false,
            'ind_avaliacao' => true,
        ]);

        $trabalhador->save();

        $duration = round((microtime(true) - $start) * 1000, 2);
        Log::notice('Avaliação de trabalhador registrada com sucesso', array_merge($context, [
            'trabalhador_id' => $trabalhadorId,
            'avaliado_como_recomendado' => $trabalhador->ind_recomendado,
            'duration_ms' => $duration,
        ]));

        return redirect()->route('quadrante.list', ['evento' => $trabalhador->idt_evento]);
    }

    public function destroy($id)
    {
        //
    }
}
