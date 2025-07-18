<?php

namespace App\Http\Controllers;

use App\Http\Requests\FichaRequest;
use App\Http\Requests\FichaVemRequest;
use App\Models\Evento;
use App\Models\Ficha;
use App\Models\TipoMovimento;
use App\Services\FichaService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class FichaVemController extends Controller
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

        return view('ficha.listVEM', compact('fichas', 'search', 'evento'));
    }


    /**
     * Formulário de criação.
     */
    public function create()
    {
        $ficha = new Ficha();
        return view('ficha.formVEM', array_merge(FichaService::dadosFixosFicha($ficha), [
            'ficha' => $ficha,
            'eventos' => Evento::where('idt_movimento', TipoMovimento::VEM)->get(),
            'movimentopadrao' => TipoMovimento::VEM,
        ]));
    }

    /**
     * Armazenar nova ficha (com dados opcionais de vem/ecc).
     */
    public function store(
        FichaRequest  $fichaRequest,
        FichaVemRequest $vemRequest
    ) {
        $data = $fichaRequest->validated();
        $ficha = Ficha::create($data);

        // Cria FichaVem se enviado
        if ($fichaRequest->filled('nom_mae')) {
            $vemData = $vemRequest->validated();
            $ficha->fichaVem()->create($vemData);
        }

        if ($fichaRequest->filled('restricoes')) {
            foreach ($fichaRequest->restricoes as $idt_restricao => $value) {
                if ($value) {
                    $ficha->fichaSaude()->create([
                        'idt_restricao' => $idt_restricao,
                        'txt_complemento' => $fichaRequest->input("complementos.$idt_restricao"),
                    ]);
                }
            }
        }

        return redirect()->route('fichas-vem.index')->with('success', 'Ficha cadastrada com sucesso!');
    }

    /**
     * Exibir ficha individual.
     */
    public function show($id)
    {
        $ficha = Ficha::with(['fichaVem', 'fichaSaude', 'analises.situacao'])->find($id);

        return view('ficha.formVEM', array_merge(FichaService::dadosFixosFicha($ficha), [
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
        $ficha = Ficha::with(['fichaVem', 'fichaSaude', 'analises.situacao'])->find($id);

        return view('ficha.formVEM', array_merge(FichaService::dadosFixosFicha($ficha), [
            'ficha' => $ficha,
            'eventos' => Evento::where('idt_movimento', TipoMovimento::VEM)->get(),
            'movimentopadrao' => TipoMovimento::VEM,
        ]));
    }

    /**
     * Atualizar ficha do VEM.
     */
    public function update(
        FichaRequest $fichaRequest,
        FichaVemRequest $vemRequest,
        $id
    ) {
        $ficha = Ficha::with(['fichaVem', 'fichaSaude', 'analises'])->findOrFail($id);

        $fichaData = $fichaRequest->validated();
        $ficha->update($fichaData);

        // Nao usei o UpdateOrCreate porque a chave e composta
        // Verificamos se o registro existe para decidir a operacao (update or create)
        if ($fichaRequest->filled('nom_mae') || $fichaRequest->filled('nom_pai')) {
            $vemData = $vemRequest->validated();
            $vemData['idt_ficha'] = $ficha->idt_ficha;

            if ($ficha->fichaVem) {
                $ficha->fichaVem()->update($vemData);
            } else {
                $ficha->fichaVem()->create($vemData);
            }
        }

        if ($fichaRequest->filled('idt_situacao')) {
            $situacao = $fichaRequest->input('idt_situacao');
            $analise = $ficha->analises()->where('idt_situacao', $situacao)->first();
            // A ficha ja tem a situacao
            if ($analise) {
                $analise->update([
                    'txt_analise' => $fichaRequest->input('txt_analise')
                ]);
            } else {
                $ficha->analises()->create([
                    'idt_situacao' => $situacao,
                    'txt_analise' => $fichaRequest->input('txt_analise')
                ]);
            }
        }

        $ficha->fichaSaude()->delete();
        // filled() avalia se o campo existe no request e nao se foi marcado ou desmarcado
        // por isso estou testando diretamente o campo
        if ($fichaRequest->input('ind_restricao') == 1) {
            foreach ($fichaRequest->input('restricoes', []) as $idt_restricao => $value) {
                if ($value) {
                    $ficha->fichaSaude()->create([
                        'idt_restricao' => $idt_restricao,
                        'txt_complemento' => $fichaRequest->input("complementos.$idt_restricao"),
                    ]);
                }
            }
        }

        return redirect()->route('fichas-vem.index')->with('success', 'Ficha atualizada com sucesso!');
    }

    public function approve($id)
    {
        FichaService::atualizarAprovacaoFicha($id);

        return redirect()->route('fichas-vem.index')->with('success', 'Aprovação atualizada com sucesso!');
    }

    /**
     * Remover ficha.
     */
    public function destroy($id)
    {
        try {
            // FichaVem, FichaSaude e FichaAnalise são deletadas por cascata
            // Soft delete
            Ficha::find($id)->delete();

            return redirect()
                ->route('fichas-vem.index')
                ->with('success', 'Ficha excluída com sucesso!');
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return redirect()
                    ->route('fichas-vem.index')
                    ->with('error', 'Não é possível excluir esta ficha. È preciso apagar os dados associados.');
            }

            // Se for outro erro de banco
            return redirect()
                ->route('fichas-vem.index')
                ->with('error', 'Erro ao tentar excluir a ficha.');
        }
    }
}
