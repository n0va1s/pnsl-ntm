<?php

namespace Modules\Vendinha\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendinhaVendaItem extends Model
{
    protected $table = 'vendinha_venda_itens';

    protected $fillable = [
        'vendinha_venda_id',
        'vendinha_produto_id',
        'nom_produto',
        'qtd_item',
        'vlr_custo_unitario',
        'vlr_venda_unitario',
        'vlr_custo_total',
        'vlr_total',
        'vlr_lucro_total',
    ];

    protected $casts = [
        'qtd_item' => 'integer',
        'vlr_custo_unitario' => 'decimal:2',
        'vlr_venda_unitario' => 'decimal:2',
        'vlr_custo_total' => 'decimal:2',
        'vlr_total' => 'decimal:2',
        'vlr_lucro_total' => 'decimal:2',
    ];

    public function venda(): BelongsTo
    {
        return $this->belongsTo(VendinhaVenda::class, 'vendinha_venda_id');
    }

    public function produto(): BelongsTo
    {
        return $this->belongsTo(VendinhaProduto::class, 'vendinha_produto_id');
    }
}
