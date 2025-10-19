<?php

namespace App\Http\Controllers;

use App\Http\Requests\FichaEccRequest;
use App\Models\Evento;
use App\Models\Ficha;
use App\Models\TipoMovimento;
use App\Services\FichaService;
use App\Traits\LogContext;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FichaEccController extends Controller
{
    use LogContext;

    protected $fichaService;

    public function __construct(FichaService $fichaService)
    {
        $this->fichaService = $fichaService;
    }

    /**
     * Listagem das fichas.
     */
    public function index(Request $request)
    {

        $start = microtime(true);
        $context = $this->getLogContext($request);

        $search = $request->get('search');
        $eventoId = $request->get('evento');
        $evento = null;

        Log::info('Requisição de listagem de fichas ECC iniciada', array_merge($context, [
            'search_term' => $search,
            'evento_filtro' => $eventoId,
        ]));

        if ($eventoId) {
            $evento = Evento::find($eventoId);
        }

        $fichas = Ficha::with(['fichaEcc', 'fichaSaude', 'analises.situacao'])
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
                $query->where('idt_movimento', TipoMovimento::ECC);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        $duration = round((microtime(true) - $start) * 1000, 2);

        Log::notice('Listagem de fichas ECC concluída com sucesso', array_merge($context, [
            'total_fichas' => $fichas->total(),
            'duration_ms' => $duration,
        ]));

        return view('ficha.listECC', compact('fichas', 'search', 'evento'));
    }

    /**
     * Formulário de criação.
     */
    public function create()
    {
        $context = $this->getLogContext(request());
        Log::info('Acesso ao formulário de criação de ficha ECC', $context);

        $ficha = new Ficha;
        $eventos = Evento::getByTipo(TipoMovimento::ECC, 'E', 3);

        return view('ficha.formECC', array_merge($this->fichaService::dadosFixosFicha($ficha), [
            'ficha' => $ficha,
            'eventos' => $eventos,
            'movimentopadrao' => TipoMovimento::ECC,
        ]));
    }

    /**
     * Armazenar nova ficha (com dados opcionais de vem/ecc).
     */
    public function store(
        FichaEccRequest $eccRequest
    ) {
        $start = microtime(true);
        $context = $this->getLogContext($eccRequest);

        Log::info('Tentativa de criação de ficha ECC', array_merge($context, [
            'candidato' => $eccRequest->input('nom_candidato'),
            'evento_id' => $eccRequest->input('idt_evento'),
        ]));

        $data = $eccRequest->validated();

        $data['tip_genero'] = $eccRequest->input('tip_genero', 'M');
        $data['tel_candidato'] = $eccRequest->input('tel_candidato');
        $data['eml_candidato'] = $eccRequest->input('eml_candidato');

        // Defaults
        $data['tam_camiseta'] = $eccRequest->input('tam_camiseta', 'M');
        $data['tip_como_soube'] = $eccRequest->input('tip_como_soube', null);
        $data['ind_catolico'] = false;
        $data['ind_toca_instrumento'] = false;
        $data['ind_consentimento'] = false;
        $data['ind_aprovado'] = false;
        $data['ind_restricao'] = false;
        $data['txt_observacao'] = null;

        $ficha = Ficha::create($data);

        // Cria FichaEcc se enviado
        if ($eccRequest->filled('nom_conjuge')) {

            $eccData = $eccRequest->validated();
            $eccData = $eccRequest->only([
                'nom_conjuge',
                'nom_apelido_conjuge',
                'tel_conjuge',
                'dat_nascimento_conjuge',
                'tam_camiseta_conjuge',
            ]);
            $ficha->fichaEcc()->create($eccData);
        }

        if ($eccRequest->filled('restricoes')) {
            foreach ($eccRequest->restricoes as $idt_restricao => $value) {
                if ($value) {
                    $ficha->fichaSaude()->create([
                        'idt_restricao' => $idt_restricao,
                        'txt_complemento' => $eccRequest->input("complementos.$idt_restricao"),
                    ]);
                }
            }
        }

        $duration = round((microtime(true) - $start) * 1000, 2);

        Log::notice('Ficha ECC criada com sucesso', array_merge($context, [
            'ficha_id' => $ficha->idt_ficha,
            'duration_ms' => $duration,
        ]));

        return redirect()->route('ecc.index')->with('success', 'Ficha cadastrada com sucesso!');
    }

    /**
     * Exibir ficha individual.
     */
    public function show($id)
    {
        $context = $this->getLogContext(request());
        Log::info('Visualização de ficha ECC', array_merge($context, ['ficha_id' => $id]));

        $ficha = Ficha::with(['fichaEcc', 'fichaSaude', 'analises.situacao'])->find($id);
        $ultimaAnalise = $ficha->analises()->latest('created_at')->first();

        return view('ficha.formECC', array_merge($this->fichaService::dadosFixosFicha($ficha), [
            'ficha' => $ficha,
            'eventos' => Evento::where('idt_movimento', TipoMovimento::ECC)->get(),
            'movimentopadrao' => TipoMovimento::ECC,
        ]));
    }

    /**
     * Formulário de edição.
     */
    public function edit($id)
    {
        $context = $this->getLogContext(request());
        Log::info('Acesso ao formulário de edição de ficha ECC', array_merge($context, ['ficha_id' => $id]));

        $ficha = Ficha::with(['fichaEcc', 'fichaSaude', 'analises.situacao'])->find($id);
        $ultimaAnalise = $ficha->analises()->latest('created_at')->first();

        return view('ficha.formECC', array_merge($this->fichaService::dadosFixosFicha($ficha), [
            'ficha' => $ficha,
            'eventos' => Evento::where('idt_movimento', TipoMovimento::ECC)->get(),
            'movimentopadrao' => TipoMovimento::ECC,
        ]));
    }

    public function update(FichaEccRequest $eccRequest, $id)
    {
        $start = microtime(true);
        $context = $this->getLogContext($eccRequest);

        Log::info('Tentativa de atualização de ficha ECC', array_merge($context, [
            'ficha_id' => $id,
            'candidato' => $eccRequest->input('nom_candidato'),
        ]));

        $ficha = Ficha::with(['fichaEcc', 'fichaSaude', 'analises'])->findOrFail($id);

        $data = $eccRequest->validated();

        $fichaData = collect($data)->only([
            'nom_candidato',
            'eml_candidato',
            'nom_apelido',
            'dat_nascimento',
            'tip_genero',
            'tam_camiseta',
            'ind_consentimento',
            'ind_restricao',
        ])->toArray();

        $ficha->update($fichaData);

        $eccData = collect($data)->only([
            'nom_conjuge',
            'nom_apelido_conjuge',
            'tel_conjuge',
            'dat_nascimento_conjuge',
            'tam_camiseta_conjuge',
        ])->toArray();

        if (! empty($eccData)) {
            $eccData['idt_ficha'] = $ficha->idt_ficha;

            if ($ficha->fichaEcc) {
                $ficha->fichaEcc()->update($eccData);
            } else {
                $ficha->fichaEcc()->create($eccData);
            }
        }

        if ($eccRequest->filled('idt_situacao')) {
            $situacao = $eccRequest->input('idt_situacao');
            $analise = $ficha->analises()->where('idt_situacao', $situacao)->first();

            if ($analise) {
                $analise->update(['txt_analise' => $eccRequest->input('txt_analise')]);
            } else {
                $ficha->analises()->create([
                    'idt_situacao' => $situacao,
                    'txt_analise' => $eccRequest->input('txt_analise'),
                ]);
            }
        }

        $duration = round((microtime(true) - $start) * 1000, 2);

        Log::notice('Ficha ECC atualizada com sucesso', array_merge($context, [
            'ficha_id' => $ficha->idt_ficha,
            'duration_ms' => $duration,
        ]));

        return redirect()->route('ecc.index')->with('success', 'Ficha ECC atualizada com sucesso.');
    }

    public function approve($id)
    {
        $start = microtime(true);
        $context = $this->getLogContext(request());

        Log::warning('Tentativa de atualização de aprovação de ficha', array_merge($context, [
            'ficha_id' => $id,
        ]));

        $this->fichaService::atualizarAprovacaoFicha($id);

        $duration = round((microtime(true) - $start) * 1000, 2);

        Log::notice('Aprovação de ficha atualizada com sucesso', array_merge($context, [
            'ficha_id' => $id,
            'duration_ms' => $duration,
        ]));

        return redirect()->route('ecc.index')->with('success', 'Aprovação atualizada com sucesso!');
    }

    /**
     * Remover ficha.
     */
    public function destroy($id)
    {
        $start = microtime(true);
        $context = $this->getLogContext(request());

        Log::warning('Tentativa de exclusão de ficha ECC', array_merge($context, [
            'ficha_id' => $id,
        ]));

        try {
            Ficha::find($id)->delete();

            $duration = round((microtime(true) - $start) * 1000, 2);

            Log::notice('Ficha ECC excluída com sucesso', array_merge($context, [
                'ficha_id' => $id,
                'duration_ms' => $duration,
            ]));

            return redirect()
                ->route('ecc.index')
                ->with('success', 'Ficha excluída com sucesso!');
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return redirect()
                    ->route('ecc.index')
                    ->with('error', 'Não é possível excluir esta ficha. È preciso apagar os dados associados.');
            }

            $duration = round((microtime(true) - $start) * 1000, 2);

            Log::error('Erro de Query ao excluir ficha ECC', array_merge($context, [
                'ficha_id' => $id,
                'sql_state' => $e->getCode(),
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'duration_ms' => $duration,
            ]));

            return redirect()
                ->route('ecc.index')
                ->with('error', 'Erro ao tentar excluir a ficha.');
        }
    }
}
