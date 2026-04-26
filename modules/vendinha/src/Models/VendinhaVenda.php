<?php

namespace Modules\Vendinha\Models;

use App\Models\Equipe;
use App\Models\Pessoa;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VendinhaVenda extends Model
{
    public const STATUS_PAGO = 'pago';

    public const STATUS_PENDENTE = 'pendente';

    protected $table = 'vendinha_vendas';

    protected $fillable = [
        'idt_pessoa',
        'idt_equipe',
        'vendedor_id',
        'nom_comprador',
        'status',
        'vlr_custo_total',
        'vlr_total',
        'vlr_lucro_total',
        'dat_pagamento',
        'observacao',
    ];

    protected $casts = [
        'vlr_custo_total' => 'decimal:2',
        'vlr_total' => 'decimal:2',
        'vlr_lucro_total' => 'decimal:2',
        'dat_pagamento' => 'datetime',
    ];

    public function pessoa(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class, 'idt_pessoa', 'idt_pessoa');
    }

    public function equipe(): BelongsTo
    {
        return $this->belongsTo(Equipe::class, 'idt_equipe', 'idt_equipe');
    }

    public function vendedor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendedor_id');
    }

    public function itens(): HasMany
    {
        return $this->hasMany(VendinhaVendaItem::class, 'vendinha_venda_id');
    }

    public function estaPendente(): bool
    {
        return $this->status === self::STATUS_PENDENTE;
    }
}
