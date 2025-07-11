<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Presenca extends Model
{
    use HasFactory;

    protected $table = 'presenca';
    public $timestamps = false;
    protected $fillable = [
        'idt_pessoa',
        'idt_evento',
        'dat_presenca',
        'ind_presente'
    ];
    public function participante()
    {
        return $this->belongsTo(Participante::class, 'idt_pessoa', 'idt_pessoa');
    }
    public function evento()
    {
        return $this->belongsTo(Evento::class, 'idt_evento', 'idt_evento');
    }
}
