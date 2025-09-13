<?php

namespace App\Http\Controllers;

use App\Http\Requests\FichaRequest;
use App\Http\Requests\FichaSGMRequest;
use App\Models\Evento;
use App\Models\Ficha;
use App\Models\TipoMovimento;
use App\Services\FichaService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class FichaSGMController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $eventoId = $request->get('evento');
        $evento = null;

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

        return view('ficha.listSGM', compact('fichas', 'search', 'evento'));
    }

    public function create() {
        $ficha = new Ficha();
        return view('ficha.formSGM', array_merge(FichaService::dadosFixosFicha($ficha), [
            'ficha' => $ficha,
            'eventos' => Evento::where('idt_movimento', TipoMovimento::SegueMe)->get(),
            'movimentopadrao' => TipoMovimento::SegueMe,
        ]));
    }

    public function store(
        FichaRequest $fichaRequest,
        FichaSGMRequest $sgmRequest
    ) {
        $data = $fichaRequest->validated();
        $ficha = Ficha::create($data);

        // Cria FichaSgm se enviado

        if($fichaRequest->filled('nom_mae')) {
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

        return redirect()->route('ficha.listSGM', ['evento' => $ficha->idt_evento]);
    }

    public function show($id){
        $ficha = Ficha::with(['fichaSGM', 'fichaSaude', 'analises.situacao'])->find($id);

        return view('ficha.formSGM', array_merge(FichaService::dadosFixosFicha($ficha), [
            'ficha' => $ficha,
            'eventos' => Evento::where('idt_movimento', TipoMovimento::VEM)->get(),
            'movimentopadrao' => TipoMovimento::SegueMe,
        ]));
    }

    public function edit($id) {
        $ficha = Ficha::with(['fichaSGM', 'fichaSaude', 'analises.situacao'])->find($id);

        return view('ficha.formSGM', array_merge(FichaService::dadosFixosFicha($ficha), [
            'ficha' => $ficha,
            'eventos' => Evento::where('idt_movimento', TipoMovimento::VEM)->get(),
            'movimentopadrao' => TipoMovimento::SegueMe,
        ]));
    }

    public function update(
        FichaRequest  $fichaRequest,
        FichaSGMRequest $sgmRequest,
        $id
    ) {
        $ficha = Ficha::with(['fichaSGM', 'fichaSaude', 'analises'])->findOrFail($id);

        $fichaData = $fichaRequest->validated();
        $ficha->update($fichaData);


        if($fichaRequest->filled('nom_mae') || $fichaRequest->filled('nom_pai')) {
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
            if($analise) {
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

        return redirect()->route('sgm.index')->with('success', 'Ficha atualizada com sucesso!');
    }

    public function approve($id)
    {
        FichaService::atualizarAprovacaoFicha($id);

        return redirect()->route('sgm.index')->with('success', 'Aprovação atualizada com sucesso!');
    }

    public function destroy($id)
    {
        try {
            Ficha::find($id)->delete();

            return redirect()
               ->route('sgm.index')
               ->with('success', 'Ficha excluída com sucesso!');
        } catch (QueryException $e) {
            if ($e->getCode() === '23000'){
                return redirect()
                   ->route('sgm.index')
                   ->with('error', 'Não é possível excluir esta ficha. É preciso apagar os dados associados.');
            }

            return redirect()
                ->route('sgm.index')
                ->with('error', 'Erro ao tentar excluir a ficha.');
        }
    }
 }


