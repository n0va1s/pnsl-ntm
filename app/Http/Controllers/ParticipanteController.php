<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\Participante;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ParticipanteController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->get('search');
        $eventoId = $request->get('evento');

        $evento = null;
        if ($eventoId) {
            $evento = Evento::find($eventoId);
        }

        $participantes = Participante::with(['pessoa', 'evento'])
            ->when($search, function ($query, $search) {
                return $query->where('nom_pessoa', 'like', "%{$search}%")
                    ->orWhere('nom_apelido', 'like', "%{$search}%");
            })->when($eventoId, function ($query, $eventoId) {
                return $query->where('idt_evento', $eventoId);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('evento.participante', compact('participantes', 'search', 'evento'));
    }

    public function change(Request $request)
    {
        foreach ($request->input('trocas', []) as $participanteId => $novaCor) {
            Participante::where('idt_participante', $participanteId)->update(['tip_cor_troca' => $novaCor]);
        }

        return redirect()->back()->with('success', 'Trocas atualizadas com sucesso.');
    }
}
