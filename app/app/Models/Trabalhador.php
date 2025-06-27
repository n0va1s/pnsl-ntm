<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Trabalhador extends Model
{
    use HasFactory;

    protected $table = 'trabalhador';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'idt_pessoa', 'idt_evento', 'idt_equipe',
        'ind_recomendado', 'ind_lideranca',
        'ind_destaque', 'ind_coordenador', 'des_habilidades',
        'bol_primeira_vez',
    ];

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class, 'idt_pessoa');
    }

    public function evento()
    {
        return $this->belongsTo(Evento::class, 'idt_evento');
    }

    public function equipe()
    {
        return $this->belongsTo(TipoEquipe::class, 'idt_equipe');
    }
}

