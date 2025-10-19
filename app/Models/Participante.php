<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Participante extends Model
{
    use HasFactory;

    protected $table = 'participante';

    protected $primaryKey = 'idt_participante';

    public $timestamps = true;

    protected $fillable = [
        'idt_pessoa',
        'idt_evento',
        'tip_cor_troca',
    ];

    public function evento()
    {
        return $this->belongsTo(Evento::class, 'idt_evento');
    }

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class, 'idt_pessoa');
    }
}
