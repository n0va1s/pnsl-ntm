<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Gamificacao extends Model
{
    use HasFactory;

    // Definimos o nome da tabela conforme a migração planejada
    protected $table = 'gamificacao';

    protected $primaryKey = 'idt_gamificacao';

    protected $fillable = [
        'idt_pessoa',
        'qtd_pontos',
        'des_motivo',
        'origem_id',
        'origem_type',
    ];

    /**
     * Pessoa que recebeu os pontos.
     */
    public function pessoa(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class, 'idt_pessoa', 'idt_pessoa');
    }

    /**
     * Identificar se o ponto veio de um Trabalhador, Participante ou outra fonte futura.
     */
    public function origem(): MorphTo
    {
        return $this->morphTo();
    }
}
