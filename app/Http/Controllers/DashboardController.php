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
    public function index(Request $request): View
    {
        $start = microtime(true);
        $context = $this->getLogContext($request);
        Log::info('Acesso ao Dashboard iniciado', $context);

        $proximoseventos = Evento::with(['movimento'])
            ->where('dat_inicio', '>=', now())
            ->orderBy('dat_inicio', 'asc')
            ->take(5)
            ->select('idt_evento', 'des_evento', 'dat_inicio', 'idt_movimento')
            ->get();

        $fichasrecentes = Ficha::with(['evento'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->select('idt_ficha', 'idt_evento', 'nom_candidato', 'dat_nascimento')
            ->get();

        $qtdEventosAtivos = Evento::where('dat_termino', '>=', today())->count();
        $qtdFichasCadastradas = Ficha::count();
        $qtdParticipantesCadastrados = Participante::distinct('idt_pessoa')->count('idt_pessoa');
        $qtdTrabalhadoresCadastrados = Trabalhador::distinct('idt_pessoa')->count('idt_pessoa');

        $duration = round((microtime(true) - $start) * 1000, 2);

        Log::notice('Dashboard carregado com sucesso e contadores obtidos', array_merge($context, [
            'total_eventos_futuros' => $proximoseventos->count(),
            'total_fichas_recentes' => $fichasrecentes->count(),
            'eventos_ativos' => $qtdEventosAtivos,
            'fichas_total' => $qtdFichasCadastradas,
            'participantes_total' => $qtdParticipantesCadastrados,
            'trabalhadores_total' => $qtdTrabalhadoresCadastrados,
            'duration_ms' => $duration,
        ]));

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
