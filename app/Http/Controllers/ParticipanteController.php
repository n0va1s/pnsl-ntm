<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\Participante;
use App\Traits\LogContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ParticipanteController extends Controller
{
    use LogContext;

    public function index(Request $request): View
    {
        $start = microtime(true);
        $context = $this->getLogContext($request);

        $search = $request->get('search');
        $eventoId = $request->get('evento');
        $evento = null;

        Log::info('Requisição de listagem de participantes iniciada', array_merge($context, [
            'search_term' => $search,
            'evento_filtro' => $eventoId,
        ]));

        if ($eventoId) {
            $evento = Evento::find($eventoId);
        }

        $participantes = Participante::with(['pessoa', 'evento'])
            ->when($search, function ($query, $search) {
                return $query->whereHas('pessoa', function ($q) use ($search) {
                    $q->where('nom_pessoa', 'like', "%{$search}%")
                      ->orWhere('nom_apelido', 'like', "%{$search}%");
                });
            })->when($eventoId, function ($query, $eventoId) {
                return $query->where('idt_evento', $eventoId);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        $duration = round((microtime(true) - $start) * 1000, 2);
        Log::notice('Listagem de participantes concluída com sucesso', array_merge($context, [
            'total_participantes' => $participantes->total(),
            'duration_ms' => $duration,
        ]));

        return view('evento.participante', compact('participantes', 'search', 'evento'));
    }

    public function change(Request $request)
    {
        $start = microtime(true);
        $context = $this->getLogContext($request);
        $trocas = $request->input('trocas', []);

        Log::info('Tentativa de atualização de trocas de participantes (tip_cor_troca)', array_merge($context, [
            'total_trocas_enviadas' => count($trocas),
            'primeiro_id_troca' => key($trocas), // ID do primeiro participante
        ]));

        foreach ($request->input('trocas', []) as $participanteId => $novaCor) {
            Participante::where('idt_participante', $participanteId)->update(['tip_cor_troca' => $novaCor]);
        }

        $duration = round((microtime(true) - $start) * 1000, 2);
        Log::notice('Trocas de participantes atualizadas com sucesso', array_merge($context, [
            'total_trocas_aplicadas' => count($trocas),
            'duration_ms' => $duration,
        ]));

        return redirect()->back()->with('success', 'Trocas atualizadas com sucesso.');
    }
}
