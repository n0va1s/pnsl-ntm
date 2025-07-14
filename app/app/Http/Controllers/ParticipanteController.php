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

        return view('participante.list', compact('participantes', 'search', 'evento'));
    }
}
