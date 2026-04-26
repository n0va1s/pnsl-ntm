<?php

namespace Modules\Vendinha\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Vendinha\Models\VendinhaProduto;
use Modules\Vendinha\Models\VendinhaVenda;

class VendaService
{
    /**
     * @param  array<int, array{produto_id:int|null, quantidade:int|null}>  $itens
     * @param  array<string, mixed>  $dados
     */
    public function registrar(array $dados, array $itens, User $vendedor): VendinhaVenda
    {
        $itensValidos = collect($itens)
            ->filter(fn (array $item) => filled($item['produto_id'] ?? null) && (int) ($item['quantidade'] ?? 0) > 0)
            ->values();

        if ($itensValidos->isEmpty()) {
            throw ValidationException::withMessages([
                'itens' => 'Informe pelo menos um produto vendido.',
            ]);
        }

        return DB::transaction(function () use ($dados, $itensValidos, $vendedor) {
            $venda = VendinhaVenda::create([
                'idt_pessoa' => $dados['idt_pessoa'] ?? null,
                'idt_equipe' => $dados['idt_equipe'] ?? null,
                'vendedor_id' => $vendedor->id,
                'nom_comprador' => $dados['nom_comprador'] ?? null,
                'status' => $dados['status'],
                'observacao' => $dados['observacao'] ?? null,
                'dat_pagamento' => $dados['status'] === VendinhaVenda::STATUS_PAGO ? now() : null,
            ]);

            $totais = [
                'vlr_custo_total' => 0,
                'vlr_total' => 0,
                'vlr_lucro_total' => 0,
            ];

            foreach ($itensValidos as $item) {
                $produto = VendinhaProduto::query()
                    ->lockForUpdate()
                    ->findOrFail($item['produto_id']);

                $quantidade = (int) $item['quantidade'];

                if ($produto->qtd_estoque !== null && $produto->qtd_estoque < $quantidade) {
                    throw ValidationException::withMessages([
                        'itens' => "Estoque insuficiente para {$produto->nom_produto}.",
                    ]);
                }

                $subtotalCusto = $produto->vlr_custo * $quantidade;
                $subtotalVenda = $produto->vlr_venda * $quantidade;
                $subtotalLucro = $subtotalVenda - $subtotalCusto;

                $venda->itens()->create([
                    'vendinha_produto_id' => $produto->id,
                    'nom_produto' => $produto->nom_produto,
                    'qtd_item' => $quantidade,
                    'vlr_custo_unitario' => $produto->vlr_custo,
                    'vlr_venda_unitario' => $produto->vlr_venda,
                    'vlr_custo_total' => $subtotalCusto,
                    'vlr_total' => $subtotalVenda,
                    'vlr_lucro_total' => $subtotalLucro,
                ]);

                if ($produto->qtd_estoque !== null) {
                    $produto->decrement('qtd_estoque', $quantidade);
                }

                $totais['vlr_custo_total'] += $subtotalCusto;
                $totais['vlr_total'] += $subtotalVenda;
                $totais['vlr_lucro_total'] += $subtotalLucro;
            }

            $venda->update($totais);

            return $venda->refresh();
        });
    }

    public function marcarComoPago(VendinhaVenda $venda): void
    {
        $venda->update([
            'status' => VendinhaVenda::STATUS_PAGO,
            'dat_pagamento' => now(),
        ]);
    }
}
