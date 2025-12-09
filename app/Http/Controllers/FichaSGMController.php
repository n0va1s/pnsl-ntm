<?php

namespace App\Http\Controllers;

use App\Http\Requests\FichaRequest;
use App\Http\Requests\FichaSGMRequest;
use App\Models\Evento;
use App\Models\Ficha;
use App\Models\TipoMovimento;
use App\Services\FichaService;
use App\Traits\LogContext;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FichaSGMController extends Controller
{
    use LogContext;

    protected $fichaService;

    public function __construct(FichaService $fichaService)
    {
        $this->fichaService = $fichaService;
    }

    public function index(Request $request)
    {
        $start = microtime(true);
        $context = $this->getLogContext($request);

        $search = $request->get('search');
        $eventoId = $request->get('evento');
        $evento = null;

        Log::info('Requisição de listagem de fichas SGM iniciada', array_merge($context, [
            'search_term' => $search,
            'evento_filtro' => $eventoId,
        ]));

        if ($eventoId) {
            $evento = Evento::find($eventoId);
        }

        $fichas = Ficha::with(['fichaSGM', 'fichaSaude', 'analises.situacao'])
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('nom_candidato', 'like', "%{$search}%")
                        ->orWhere('nom_apelido', 'like', "%{$search}%");
                });
            })
            ->when($eventoId, function ($query, $eventoId) {
                return $query->where('idt_evento', $eventoId);
            })
            ->whereHas('evento', function ($query) {
                $query->where('idt_movimento', TipoMovimento::SegueMe);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        $duration = round((microtime(true) - $start) * 1000, 2);

        Log::notice('Listagem de fichas SGM concluída com sucesso', array_merge($context, [
            'total_fichas' => $fichas->total(),
            'duration_ms' => $duration,
        ]));

        return view('ficha.listSGM', compact('fichas', 'search', 'evento'));
    }

    public function create()
    {
        $context = $this->getLogContext(request());

        Log::info('Acesso ao formulário de criação de ficha SGM', $context);

        $ficha = new Ficha;
        $eventos = Evento::getByTipo(TipoMovimento::SegueMe, 'E', 3);

        return view('ficha.formSGM', array_merge($this->fichaService::dadosFixosFicha($ficha), [
            'ficha' => $ficha,
            'eventos' => $eventos,
            'movimentopadrao' => TipoMovimento::SegueMe,
        ]));
    }

    public function store(
        FichaRequest $fichaRequest,
        FichaSGMRequest $sgmRequest
    ) {
        $start = microtime(true);
        $context = $this->getLogContext($fichaRequest);

        Log::info('Tentativa de criação de ficha SGM', array_merge($context, [
            'candidato' => $fichaRequest->input('nom_candidato'),
            'evento_id' => $fichaRequest->input('idt_evento'),
        ]));

        $data = $fichaRequest->validated();
        $ficha = Ficha::create($data);

        if ($fichaRequest->filled('nom_mae')) {
            $sgmData = $sgmRequest->validated();
            $ficha->fichaSGM()->create($sgmData);
        }

        if ($fichaRequest->filled('restricoes')) {
            foreach ($fichaRequest->restricoes as $idt_restricao => $value) {
                if ($value) {
                    $ficha->fichaSaude()->create([
                        'idt_resticao' => $idt_restricao,
                        'txt_complemento' => $fichaRequest->input("complementos.$idt_restricao"),
                    ]);
                }
            }
        }

        $duration = round((microtime(true) - $start) * 1000, 2);

        Log::notice('Ficha SGM criada com sucesso', array_merge($context, [
            'ficha_id' => $ficha->idt_ficha,
            'duration_ms' => $duration,
        ]));

        return redirect()->route('sgm.index', ['evento' => $ficha->idt_evento])->with('success', 'Ficha cadastrada com sucesso!');
    }

    public function show($id)
    {
        $context = $this->getLogContext(request());
        Log::info('Visualização de ficha SGM', array_merge($context, ['ficha_id' => $id]));

        $ficha = Ficha::with(['fichaSGM', 'fichaSaude', 'analises.situacao'])->find($id);

        return view('ficha.formSGM', array_merge($this->fichaService::dadosFixosFicha($ficha), [
            'ficha' => $ficha,
            'eventos' => Evento::where('idt_movimento', TipoMovimento::VEM)->get(),
            'movimentopadrao' => TipoMovimento::SegueMe,
        ]));
    }

    public function edit($id)
    {
        $context = $this->getLogContext(request());

        Log::info('Acesso ao formulário de edição de ficha SGM', array_merge($context, ['ficha_id' => $id]));

        $ficha = Ficha::with(['fichaSGM', 'fichaSaude', 'analises.situacao'])->find($id);

        return view('ficha.formSGM', array_merge($this->fichaService::dadosFixosFicha($ficha), [
            'ficha' => $ficha,
            'eventos' => Evento::where('idt_movimento', TipoMovimento::VEM)->get(),
            'movimentopadrao' => TipoMovimento::SegueMe,
        ]));
    }

    public function update(
        FichaRequest $fichaRequest,
        FichaSGMRequest $sgmRequest,
        $id
    ) {

        $start = microtime(true);
        $context = $this->getLogContext($fichaRequest);

        Log::info('Tentativa de atualização de ficha SGM', array_merge($context, [
            'ficha_id' => $id,
            'candidato' => $fichaRequest->input('nom_candidato'),
        ]));

        $ficha = Ficha::with(['fichaSGM', 'fichaSaude', 'analises'])->findOrFail($id);

        $fichaData = $fichaRequest->validated();
        $ficha->update($fichaData);

        if ($fichaRequest->filled('nom_mae') || $fichaRequest->filled('nom_pai')) {
            $sgmData = $sgmRequest->validated();
            $sgmData['idt_ficha'] = $ficha->idt_ficha;

            if ($ficha->fichaSGM) {
                $ficha->fichaSGM()->update($sgmData);
            } else {
                $ficha->fichaSGM()->create($sgmData);
            }
        }

        if ($fichaRequest->filled('idt_situacao')) {
            $situacao = $fichaRequest->input('idt_situacao');
            $analise = $ficha->analises()->where('idt_situacao', $situacao)->first();
            // A ficha ja tem a situacao
            if ($analise) {
                $analise->update([
                    'txt_analise' => $fichaRequest->input('txt_analise'),
                ]);
            } else {
                $ficha->analises()->create([
                    'idt_situacao' => $situacao,
                    'txt_analise' => $fichaRequest->input('txt_analise'),
                ]);
            }
        }

        $ficha->fichaSaude()->delete();

        if ($fichaRequest->filled('ind_restricoes') == 1) {
            foreach ($fichaRequest->input('restricoes', []) as $idt_restricao => $value) {
                if ($value) {
                    $ficha->fichaSaude()->create([
                        'idt_resticao' => $idt_restricao,
                        'txt_complemento' => $fichaRequest->input("complementos.$idt_restricao"),
                    ]);
                }
            }
        }

        $duration = round((microtime(true) - $start) * 1000, 2);

        Log::notice('Ficha SGM atualizada com sucesso', array_merge($context, [
            'ficha_id' => $id,
            'duration_ms' => $duration,
        ]));

        return redirect()->route('sgm.index')->with('success', 'Ficha atualizada com sucesso!');
    }

    public function approve($id)
    {
        $start = microtime(true);
        $context = $this->getLogContext(request());

        Log::warning('Tentativa de atualização de aprovação de ficha SGM', array_merge($context, [
            'ficha_id' => $id,
        ]));

        $this->fichaService::atualizarAprovacaoFicha($id);

        $duration = round((microtime(true) - $start) * 1000, 2);

        Log::notice('Aprovação de ficha SGM atualizada com sucesso', array_merge($context, [
            'ficha_id' => $id,
            'duration_ms' => $duration,
        ]));

        return redirect()->route('sgm.index')->with('success', 'Aprovação atualizada com sucesso!');
    }

    public function destroy($id)
    {
        $start = microtime(true);
        $context = $this->getLogContext(request());

        Log::warning('Tentativa de exclusão de ficha SGM', array_merge($context, [
            'ficha_id' => $id,
        ]));

        try {
            Ficha::find($id)->delete();

            $duration = round((microtime(true) - $start) * 1000, 2);

            Log::notice('Ficha SGM excluída com sucesso', array_merge($context, [
                'ficha_id' => $id,
                'duration_ms' => $duration,
            ]));

            return redirect()
                ->route('sgm.index')
                ->with('success', 'Ficha excluída com sucesso!');
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return redirect()
                    ->route('sgm.index')
                    ->with('error', 'Não é possível excluir esta ficha. É preciso apagar os dados associados.');
            }

            $duration = round((microtime(true) - $start) * 1000, 2);

            Log::error('Erro de Query ao excluir ficha SGM', array_merge($context, [
                'ficha_id' => $id,
                'sql_state' => $e->getCode(),
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'duration_ms' => $duration,
            ]));

            return redirect()
                ->route('sgm.index')
                ->with('error', 'Erro ao tentar excluir a ficha.');
        }
    }
}
