<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Evento extends Model
{
    use HasFactory;

    protected $table = 'evento';
    protected $primaryKey = 'idt_evento';
    public $timestamps = false;

    protected $fillable = [
        'des_evento', 'num_evento', 'dat_inicio', 'dat_termino', 'ind_pos_encontro'
    ];

    public function participantes()
    {
        return $this->hasMany(Participante::class, 'idt_evento');
    }

    public function trabalhadores()
    {
        return $this->hasMany(Trabalhador::class, 'idt_evento');
    }
}

