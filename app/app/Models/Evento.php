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
        'des_evento',
        'num_evento',
        'dat_inicio',
        'dat_termino',
        'ind_pos_encontro',
    ];

    protected $casts = [
        'dat_inicio' => 'date',
        'dat_termino' => 'date',
        'ind_pos_encontro' => 'boolean',
    ];

    /**
     * Accessor para formatar a data de início
     */
    public function getDataInicioFormatadaAttribute()
    {
        return $this->dat_inicio ? $this->dat_inicio->format('d/m/Y') : null;
    }

    /**
     * Accessor para formatar a data de término
     */
    public function getDataTerminoFormatadaAttribute()
    {
        return $this->dat_termino ? $this->dat_termino->format('d/m/Y') : null;
    }

    /**
     * Accessor para o status de pós encontro
     */
    public function getPosEncontroTextoAttribute()
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