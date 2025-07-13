<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evento extends Model
{
    use HasFactory;

    protected $table = 'evento';
    protected $primaryKey = 'idt_evento';

    protected $fillable = [
        'idt_movimento',
        'des_evento',
        'num_evento',
        'dat_inicio',
        'dat_termino',
        'val_trabalhador',
        'val_venista',
        'val_camiseta',
        'ind_pos_encontro',
    ];

    protected $casts = [
        'idt_movimento' => 'integer',
        'dat_inicio' => 'date',
        'dat_termino' => 'date',
        'ind_pos_encontro' => 'boolean',
    ];

    public function movimento()
    {
        return $this->belongsTo(TipoMovimento::class, 'idt_movimento');
    }

    public function fichas()
    {
        return $this->hasMany(Ficha::class, 'idt_evento');
    }

    public function participantes()
    {
        return $this->hasMany(Participante::class, 'idt_evento');
    }

    public function voluntarios()
    {
        return $this->hasMany(Voluntario::class, 'idt_evento');
    }

    public function trabalhadores()
    {
        return $this->hasMany(Trabalhador::class, 'idt_evento');
    }

    /**
     * Accessor para formatar a data de início
     */
    public function getDataInicioFormatada()
    {
        return $this->dat_inicio ? $this->dat_inicio->format('d/m/Y') : null;
    }

    /**
     * Accessor para formatar a data de término
     */
    public function getDataTerminoFormatada()
    {
        return $this->dat_termino ? $this->dat_termino->format('d/m/Y') : null;
    }

    /**
     * Accessor para o status de pós encontro
     */
    public function getPosEncontroTexto()
    {
        return $this->ind_pos_encontro ? 'Sim' : 'Não';
    }

    /**
     * Scope para buscar eventos
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('des_evento', 'like', "%{$search}%")
                ->orWhere('num_evento', 'like', "%{$search}%");
        });
    }
}
