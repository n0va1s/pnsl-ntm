<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contato extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'contato';
    protected $primaryKey = 'idt_contato';

    protected $fillable = [
        'nom_contato',
        'dat_contato',
        'eml_contato',
        'tel_contato',
        'txt_mensagem',
        'idt_movimento',
    ];

    protected $casts = [
        'idt_contato' => 'integer',
        'dat_contato' => 'date',
    ];

    /**
     * Relacionamento: Contato pertence a um Movimento
     */
    public function movimento()
    {
        return $this->belongsTo(TipoMovimento::class, 'idt_movimento');
    }
}
