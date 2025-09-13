<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\Ficha;
use App\Models\Participante;
use App\Models\Trabalhador;

class DashboardController extends Controller
{
    public function index()
    {
        $proximoseventos = Evento::with(['movimento'])
            ->where('dat_inicio', '>=', now())
            ->orderBy('dat_inicio', 'asc')
            ->take(5)
            ->select('idt_evento', 'des_evento', 'dat_inicio', 'idt_movimento')
            ->get();

        $fichasrecentes = Ficha::with(['evento'])
            ->orderBy('created_at', 'asc')
            ->take(5)
            ->select('idt_ficha', 'idt_evento', 'nom_candidato', 'dat_nascimento', 'idt_movimento')
            ->get();

        $qtdEventosAtivos = Evento::where('dat_termino', '>=', today())->count();

        $qtdFichasCadastradas = Ficha::count();

        $qtdParticipantesCadastrados = Participante::distinct('idt_pessoa')->count('idt_pessoa');

        $qtdTrabalhadoresCadastrados = Trabalhador::distinct('idt_pessoa')->count('idt_pessoa');

        return view('dashboard', compact(
            'proximoseventos',
            'fichasrecentes',
            'qtdEventosAtivos',
            'qtdFichasCadastradas',
            'qtdParticipantesCadastrados',
            'qtdTrabalhadoresCadastrados'
        ));
    }
}
