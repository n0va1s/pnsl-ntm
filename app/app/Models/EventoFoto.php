<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EventoFoto extends Model
{
    use HasFactory;

    protected $table = 'evento_foto';
    protected $primaryKey = 'idt_evento';
    public $timestamps = false;

    protected $fillable = [
        'idt_evento',
        'med_foto',
    ];

    public function evento()
    {
        return $this->belongsTo(Evento::class, 'idt_evento');
    }
}
