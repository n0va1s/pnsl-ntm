<?php

namespace App\Models;

use Brick\Math\BigInteger;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class Evento extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'evento';
    protected $primaryKey = 'idt_evento';
    public $timestamps = true;

    protected $fillable = [
        'idt_movimento',
        'des_evento',
        'num_evento',
        'dat_inicio',
        'dat_termino',
        'val_camiseta',
        'val_trabalhador',
        'val_venista',
        'val_entrada',
        'tip_evento',
        'txt_informacao',
    ];

    protected $casts = [
        'idt_movimento' => 'integer',
        'dat_inicio' => 'date',
        'dat_termino' => 'date',
    ];

    /**
     * Define o relacionamento de um evento com um tipo de movimento.
     */
    public function movimento()
    {
        return $this->belongsTo(TipoMovimento::class, 'idt_movimento');
    }

    /**
     * Define o relacionamento de um evento com as suas fichas.
     */
    public function fichas()
    {
        return $this->hasMany(Ficha::class, 'idt_evento');
    }

    /**
     * Define o relacionamento de um evento com os seus participantes.
     */
    public function participantes()
    {
        return $this->hasMany(Participante::class, 'idt_evento');
    }

    /**
     * Define o relacionamento de um evento com os seus voluntários.
     */
    public function voluntarios()
    {
        return $this->hasMany(Voluntario::class, 'idt_evento');
    }

    /**
     * Define o relacionamento de um evento com os seus trabalhadores.
     */
    public function trabalhadores()
    {
        return $this->hasMany(Trabalhador::class, 'idt_evento');
    }

    /**
     * Define o relacionamento de um evento com a sua foto.
     */
    public function foto()
    {
        return $this->hasOne(EventoFoto::class, 'idt_evento');
    }

    /**
     * Scope para busca insensível a maiúsculas e minúsculas nos campos des_evento e num_evento.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string|null  $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch(Builder $query, ?string $search)
    {
        if ($search) {
            $lowerSearch = strtolower($search);
            return $query->whereRaw('LOWER(des_evento) LIKE ?', ["%{$lowerSearch}%"])
                ->orWhereRaw('LOWER(num_evento) LIKE ?', ["%{$lowerSearch}%"]);
        }
        return $query;
    }

    public function getDataInicioFormatada()
    {
        return $this->dat_inicio
            ? $this->dat_inicio->format('Y-m-d')
            : null;
    }

    public function getDataTerminoFormatada()
    {
        return $this->dat_termino
            ? $this->dat_termino->format('Y-m-d')
            : null;
    }
}
