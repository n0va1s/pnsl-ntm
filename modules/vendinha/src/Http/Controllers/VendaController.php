<?php

namespace Modules\Vendinha\Http\Controllers;

use App\Models\Equipe;
use App\Models\Pessoa;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Vendinha\Models\VendinhaProduto;
use Modules\Vendinha\Models\VendinhaVenda;
use Modules\Vendinha\Services\VendaService;

class VendaController extends Controller
{
    public function create()
    {
        return view('vendinha::vendas.create', [
            'produtos' => VendinhaProduto::query()->where('ind_ativo', true)->orderBy('nom_produto')->get(),
            'pessoas' => Pessoa::query()->orderBy('nom_pessoa')->limit(250)->get(),
            'equipes' => Equipe::query()->ativas()->orderBy('nom_equipe')->get(),
        ]);
    }

    public function store(Request $request, VendaService $service)
    {
        $validated = $request->validate([
            'idt_pessoa' => ['nullable', 'exists:pessoa,idt_pessoa'],
            'idt_equipe' => ['nullable', 'exists:equipes,idt_equipe'],
            'nom_comprador' => ['nullable', 'string', 'max:120'],
            'status' => ['required', 'in:'.VendinhaVenda::STATUS_PAGO.','.VendinhaVenda::STATUS_PENDENTE],
            'observacao' => ['nullable', 'string', 'max:1000'],
            'itens' => ['required', 'array'],
            'itens.*.produto_id' => ['nullable', 'exists:vendinha_produtos,id'],
            'itens.*.quantidade' => ['nullable', 'integer', 'min:1'],
        ]);

        if (blank($validated['idt_pessoa'] ?? null) && blank($validated['nom_comprador'] ?? null)) {
            return back()
                ->withErrors(['nom_comprador' => 'Informe a pessoa cadastrada ou o nome do comprador.'])
                ->withInput();
        }

        $service->registrar($validated, $validated['itens'], $request->user());

        return redirect()->route('vendinha.dashboard')->with('success', 'Venda registrada com sucesso.');
    }

    public function pagar(VendinhaVenda $venda, VendaService $service)
    {
        $service->marcarComoPago($venda);

        return redirect()->route('vendinha.dashboard')->with('success', 'Conta marcada como paga.');
    }
}
