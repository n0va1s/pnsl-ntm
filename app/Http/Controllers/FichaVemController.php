<?php

namespace App\Http\Controllers;

use App\Http\Requests\FichaVemRequest;
use App\Models\Evento;
use App\Models\Ficha;
use App\Models\TipoMovimento;
use App\Services\FichaService;
use App\Traits\LogContext;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FichaVemController extends Controller
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

        Log::info('Requisição de listagem de fichas VEM iniciada', array_merge($context, [
            'search_term' => $search,
            'evento_filtro' => $eventoId,
        ]));

        if ($eventoId) {
            $evento = Evento::find($eventoId);
        }

        $fichas = Ficha::with(['fichaVem', 'fichaSaude', 'analises.situacao'])
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
                $query->where('idt_movimento', TipoMovimento::VEM);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        $duration = round((microtime(true) - $start) * 1000, 2);

        Log::notice('Listagem de fichas VEM concluída com sucesso', array_merge($context, [
            'total_fichas' => $fichas->total(),
            'duration_ms' => $duration,
        ]));

        return view('ficha.listVEM', compact('fichas', 'search', 'evento'));
    }


    /**
     * Formulário de criação.
     */
    public function create()
    {
        $context = $this->getLogContext(request());
        Log::info('Acesso ao formulário de criação de ficha VEM', $context);

        $ficha = new Ficha();
        $eventos = Evento::getByTipo(TipoMovimento::VEM, 'E', 3);

        return view('ficha.formVEM', array_merge($this->fichaService::dadosFixosFicha($ficha), [
            'ficha' => $ficha,
            'eventos' => $eventos,
            'movimentopadrao' => TipoMovimento::VEM,
        ]));
    }

    /**
     * Armazenar nova ficha (com dados opcionais de vem/ecc).
     */
    public function store(
        FichaVemRequest $vemRequest
    ) {

        $start = microtime(true);
        $context = $this->getLogContext($vemRequest);

        Log::info('Tentativa de criação de ficha VEM', array_merge($context, [
            'candidato' => $vemRequest->input('nom_candidato'),
            'evento_id' => $vemRequest->input('idt_evento'),
        ]));

        $data = $vemRequest->only([
            'idt_evento',
            'nom_candidato',
            'nom_apelido',
            'dat_nascimento',
            'des_endereco',
        ]);

        // Map request para os nomes corretos do schema
        $data['tip_genero'] = $vemRequest->input('tip_genero', 'M');
        $data['tel_candidato'] = $vemRequest->input('tel_candidato');
        $data['eml_candidato'] = $vemRequest->input('eml_candidato', null);

        // Campos obrigatórios com defaults
        $data['tam_camiseta'] = $vemRequest->input('tam_camiseta', 'M');
        $data['tip_como_soube'] = $vemRequest->input('tip_como_soube', null);
        $data['ind_catolico'] = false;
        $data['ind_toca_instrumento'] = false;
        $data['ind_consentimento'] = false;
        $data['ind_aprovado'] = false;
        $data['ind_restricao'] = false;
        $data['txt_observacao'] = null;

        $ficha = Ficha::create($data);

        if ($vemRequest->filled('nom_mae') || $vemRequest->filled('nom_pai')) {

            $vemData = $vemRequest->validated();
            $vemData = $vemRequest->only([
                'idt_falar_com',
                'des_onde_estuda',
                'des_mora_quem',
                'nom_pai',
                'tel_pai',
                'nom_mae',
                'tel_mae'
            ]);

            $ficha->fichaVem()->create($vemData);
        }

        if ($vemRequest->filled('restricoes')) {
            foreach ($vemRequest->restricoes as $idt_restricao => $value) {
                if ($value) {
                    $ficha->fichaSaude()->create([
                        'idt_restricao' => $idt_restricao,
                        'txt_complemento' => $vemRequest->input("complementos.$idt_restricao"),
                    ]);
                }
            }
        }

        $duration = round((microtime(true) - $start) * 1000, 2);
        Log::notice('Ficha VEM criada com sucesso', array_merge($context, [
            'ficha_id' => $ficha->idt_ficha,
            'duration_ms' => $duration,
        ]));

        return redirect()->route('home')->with('success', 'Ficha cadastrada com sucesso!');
    }

    /**
     * Exibir ficha individual.
     */
    public function show($id)
    {
        $context = $this->getLogContext(request());
        Log::info('Visualização de ficha VEM', array_merge($context, ['ficha_id' => $id]));

        $ficha = Ficha::with(['fichaVem', 'fichaSaude', 'analises.situacao'])->find($id);

        return view('ficha.formVEM', array_merge($this->fichaService::dadosFixosFicha($ficha), [
            'ficha' => $ficha,
            'eventos' => Evento::where('idt_movimento', TipoMovimento::VEM)->get(),
            'movimentopadrao' => TipoMovimento::VEM,
        ]));
    }

    /**
     * Formulário de edição.
     */
    public function edit($id)
    {
        $context = $this->getLogContext(request());
        Log::info('Acesso ao formulário de edição de ficha VEM', array_merge($context, ['ficha_id' => $id]));

        $ficha = Ficha::with(['fichaVem', 'fichaSaude', 'analises.situacao'])->find($id);

        return view('ficha.formVEM', array_merge($this->fichaService::dadosFixosFicha($ficha), [
            'ficha' => $ficha,
            'eventos' => Evento::where('idt_movimento', TipoMovimento::VEM)->get(),
            'movimentopadrao' => TipoMovimento::VEM,
        ]));
    }

    /**
     * Atualizar ficha do VEM.
     */
    public function update(
        FichaVemRequest $vemRequest,
        $id
    ) {
        $start = microtime(true);
        $context = $this->getLogContext($vemRequest);

        Log::info('Tentativa de atualização de ficha VEM', array_merge($context, [
            'ficha_id' => $id,
            'candidato' => $vemRequest->input('nom_candidato'),
        ]));

        $ficha = Ficha::with(['fichaVem', 'fichaSaude', 'analises'])->findOrFail($id);

        $vemData = $vemRequest->validated();
        $ficha->update($vemData);

        // Nao usei o UpdateOrCreate porque a chave e composta
        // Verificamos se o registro existe para decidir a operacao (update or create)
        if ($vemRequest->filled('nom_mae') || $vemRequest->filled('nom_pai')) {
            $vemData = $vemRequest->validated();
            $vemData['idt_ficha'] = $ficha->idt_ficha;

            if ($ficha->fichaVem) {
                $ficha->fichaVem->update($vemData);
            } else {
                $ficha->fichaVem()->create($vemData);
            }
        }

        if ($vemRequest->filled('idt_situacao')) {
            $situacao = $vemRequest->input('idt_situacao');
            $analise = $ficha->analises()->where('idt_situacao', $situacao)->first();
            // A ficha ja tem a situacao
            if ($analise) {
                $analise->update([
                    'txt_analise' => $vemRequest->input('txt_analise')
                ]);
            } else {
                $ficha->analises()->create([
                    'idt_situacao' => $situacao,
                    'txt_analise' => $vemRequest->input('txt_analise')
                ]);
            }
        }

        $ficha->fichaSaude()->delete();
        // filled() avalia se o campo existe no request e nao se foi marcado ou desmarcado
        // por isso estou testando diretamente o campo
        if ($vemRequest->input('ind_restricao') == 1) {
            foreach ($vemRequest->input('restricoes', []) as $idt_restricao => $value) {
                if ($value) {
                    $ficha->fichaSaude()->create([
                        'idt_restricao' => $idt_restricao,
                        'txt_complemento' => $vemRequest->input("complementos.$idt_restricao"),
                    ]);
                }
            }
        }

        $duration = round((microtime(true) - $start) * 1000, 2);
        Log::notice('Ficha VEM atualizada com sucesso', array_merge($context, [
            'ficha_id' => $id,
            'duration_ms' => $duration,
        ]));

        return redirect()->route('vem.index')->with('success', 'Ficha atualizada com sucesso!');
    }

    public function approve($id)
    {
        $start = microtime(true);
        $context = $this->getLogContext(request());

        Log::warning('Tentativa de atualização de aprovação de ficha VEM', array_merge($context, [
            'ficha_id' => $id,
        ]));

        $this->fichaService::atualizarAprovacaoFicha($id);

        $duration = round((microtime(true) - $start) * 1000, 2);
        Log::notice('Aprovação de ficha VEM atualizada com sucesso', array_merge($context, [
            'ficha_id' => $id,
            'duration_ms' => $duration,
        ]));

        return redirect()->route('vem.index')->with('success', 'Aprovação atualizada com sucesso!');
    }

    /**
     * Remover ficha.
     */
    public function destroy($id)
    {
        $start = microtime(true);
        $context = $this->getLogContext(request());

        Log::warning('Tentativa de exclusão de ficha VEM', array_merge($context, [
            'ficha_id' => $id,
        ]));

        try {
            // FichaVem, FichaSaude e FichaAnalise são deletadas por cascata
            // Soft delete
            Ficha::find($id)->delete();

            $duration = round((microtime(true) - $start) * 1000, 2);
            Log::notice('Ficha VEM excluída com sucesso', array_merge($context, [
                'ficha_id' => $id,
                'duration_ms' => $duration,
            ]));

            return redirect()
                ->route('vem.index')
                ->with('success', 'Ficha excluída com sucesso!');
        } catch (QueryException $e) {

            $duration = round((microtime(true) - $start) * 1000, 2);
            Log::error('Erro de Query ao excluir ficha VEM', array_merge($context, [
                'ficha_id' => $id,
                'sql_state' => $e->getCode(),
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'duration_ms' => $duration,
            ]));

            if ($e->getCode() === '23000') {
                return redirect()
                    ->route('vem.index')
                    ->with('error', 'Não é possível excluir esta ficha. È preciso apagar os dados associados.');
            }

            // Se for outro erro de banco
            return redirect()
                ->route('vem.index')
                ->with('error', 'Erro ao tentar excluir a ficha.');
        }
    }
}
