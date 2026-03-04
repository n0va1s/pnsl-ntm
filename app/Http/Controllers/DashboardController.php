<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\Ficha;
use App\Models\Participante;
use App\Models\Trabalhador;
use App\Traits\LogContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View; // Adicionado para type hint

class DashboardController extends Controller
{
    use LogContext;

    /**
     * Exibe o dashboard principal com contadores e listas de itens recentes.
     */
    // DashboardController.php
    public function index(Request $request): View
    {
        $start = microtime(true);

        // Otimização 1: Eager Loading com colunas específicas para reduzir memória
        $proximoseventos = Evento::with(['movimento:idt_movimento,des_sigla'])
            ->where('dat_inicio', '>=', now())
            ->orderBy('dat_inicio', 'asc')
            ->take(5)
            ->select('idt_evento', 'des_evento', 'dat_inicio', 'idt_movimento')
            ->get();

        // Otimização 2: Carregamento aninhado (Nested Eager Loading) para evitar N+1 na sigla do movimento
        $fichasrecentes = Ficha::with(['evento.movimento:idt_movimento,des_sigla'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->select('idt_ficha', 'idt_evento', 'nom_candidato', 'dat_nascimento')
            ->get();

        // Otimização 3: Queries de contagem simples
        $qtdEventosAtivos = Evento::where('dat_termino', '>=', today())->count();
        $qtdFichasCadastradas = Ficha::count();

        // Otimização 4: Se o banco crescer muito, considere Cache::remember nestes contadores de distinct
        $qtdParticipantesCadastrados = Participante::distinct('idt_pessoa')->count('idt_pessoa');
        $qtdTrabalhadoresCadastrados = Trabalhador::distinct('idt_pessoa')->count('idt_pessoa');

        $duration = round((microtime(true) - $start) * 1000, 2);
        Log::notice('Dashboard carregado', ['duration_ms' => $duration]);

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
