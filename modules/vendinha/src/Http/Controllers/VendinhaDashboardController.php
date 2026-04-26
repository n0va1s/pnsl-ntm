<?php

namespace Modules\Vendinha\Http\Controllers;

use App\Models\Equipe;
use Illuminate\Routing\Controller;
use Modules\Vendinha\Models\VendinhaProduto;
use Modules\Vendinha\Models\VendinhaVenda;

class VendinhaDashboardController extends Controller
{
    public function __invoke()
    {
        $produtos = VendinhaProduto::query()
            ->orderBy('nom_produto')
            ->get();

        $vendas = VendinhaVenda::query()
            ->with(['pessoa', 'equipe', 'itens'])
            ->latest()
            ->limit(30)
            ->get();

        $pendentes = VendinhaVenda::query()
            ->with(['pessoa', 'equipe'])
            ->where('status', VendinhaVenda::STATUS_PENDENTE)
            ->latest()
            ->get();

        $totais = [
            'faturamento' => VendinhaVenda::sum('vlr_total'),
            'custo' => VendinhaVenda::sum('vlr_custo_total'),
            'lucro' => VendinhaVenda::sum('vlr_lucro_total'),
            'aberto' => VendinhaVenda::where('status', VendinhaVenda::STATUS_PENDENTE)->sum('vlr_total'),
            'quantidade' => (int) VendinhaVenda::query()->join('vendinha_venda_itens', 'vendinha_vendas.id', '=', 'vendinha_venda_itens.vendinha_venda_id')->sum('qtd_item'),
        ];

        $porEquipe = Equipe::query()
            ->select('equipes.nom_equipe')
            ->selectRaw('COALESCE(SUM(vendinha_vendas.vlr_total), 0) as total_vendido')
            ->selectRaw('COALESCE(SUM(vendinha_vendas.vlr_lucro_total), 0) as lucro')
            ->join('vendinha_vendas', 'vendinha_vendas.idt_equipe', '=', 'equipes.idt_equipe')
            ->groupBy('equipes.idt_equipe', 'equipes.nom_equipe')
            ->orderByDesc('total_vendido')
            ->get();

        return view('vendinha::dashboard', compact('produtos', 'vendas', 'pendentes', 'totais', 'porEquipe'));
    }
}
