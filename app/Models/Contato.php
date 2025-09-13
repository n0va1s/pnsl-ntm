<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contato extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'contato';
    protected $primaryKey = 'idt_contato';
    public $timestamps = true;

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
     * Scope para realizar a busca por nome de contato.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where('nom_contato', 'like', "%{$search}%");
    }

    /**
     * Relacionamento: Contato pertence a um Movimento
     */
    public function movimento()
    {
        return $this->belongsTo(TipoMovimento::class, 'idt_movimento');
    }
}
