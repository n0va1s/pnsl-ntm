<?php

namespace Modules\Vendinha\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VendinhaProduto extends Model
{
    protected $table = 'vendinha_produtos';

    protected $fillable = [
        'nom_produto',
        'des_produto',
        'vlr_custo',
        'vlr_venda',
        'qtd_estoque',
        'ind_ativo',
    ];

    protected $casts = [
        'vlr_custo' => 'decimal:2',
        'vlr_venda' => 'decimal:2',
        'qtd_estoque' => 'integer',
        'ind_ativo' => 'boolean',
    ];

    public function itens(): HasMany
    {
        return $this->hasMany(VendinhaVendaItem::class, 'vendinha_produto_id');
    }
}
