<?php

namespace App\Http\Controllers;

use App\Http\Requests\FichaEccRequest;
use App\Http\Requests\FichaRequest;
use App\Models\Evento;
use App\Models\Ficha;
use App\Models\TipoMovimento;
use App\Services\FichaService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class FichaEccController extends Controller
{
    /**
     * Listagem das fichas.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $eventoId = $request->get('evento');
        $evento = null;

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

        return view('ficha.listECC', compact('fichas', 'search', 'evento'));
    }

    /**
     * Formulário de criação.
     */
    public function create()
    {
        $ficha = new Ficha();
        $eventos = Evento::getByTipo(TipoMovimento::ECC, 'E', 3);
        return view('ficha.formECC', array_merge(FichaService::dadosFixosFicha($ficha), [
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
                'tam_camiseta_conjuge'
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

        return redirect()->route('ecc.index')->with('success', 'Ficha cadastrada com sucesso!');
    }

    /**
     * Exibir ficha individual.
     */
    public function show($id)
    {
        $ficha = Ficha::with(['fichaEcc', 'fichaSaude', 'analises.situacao'])->find($id);
        $ultimaAnalise = $ficha->analises()->latest('created_at')->first();
        return view('ficha.formECC', array_merge(FichaService::dadosFixosFicha($ficha), [
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
        $ficha = Ficha::with(['fichaEcc', 'fichaSaude', 'analises.situacao'])->find($id);
        $ultimaAnalise = $ficha->analises()->latest('created_at')->first();
        return view('ficha.formECC', array_merge(FichaService::dadosFixosFicha($ficha), [
            'ficha' => $ficha,
            'eventos' => Evento::where('idt_movimento', TipoMovimento::ECC)->get(),
            'movimentopadrao' => TipoMovimento::ECC,
        ]));
    }

    public function update(FichaEccRequest $eccRequest, $id)
    {
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

        if (!empty($eccData)) {
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
                    'txt_analise' => $eccRequest->input('txt_analise')
                ]);
            }
        }
        return redirect()->route('ecc.index')->with('success', 'Ficha ECC atualizada com sucesso.');
    }

    public function approve($id)
    {
        FichaService::atualizarAprovacaoFicha($id);

        return redirect()->route('ecc.index')->with('success', 'Aprovação atualizada com sucesso!');
    }

    /**
     * Remover ficha.
     */
    public function destroy($id)
    {
        try {
            Ficha::find($id)->delete();

            return redirect()
                ->route('ecc.index')
                ->with('success', 'Ficha excluída com sucesso!');
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return redirect()
                    ->route('ecc.index')
                    ->with('error', 'Não é possível excluir esta ficha. È preciso apagar os dados associados.');
            }

            // Se for outro erro de banco
            return redirect()
                ->route('ecc.index')
                ->with('error', 'Erro ao tentar excluir a ficha.');
        }
    }
}
