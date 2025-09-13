<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Presenca extends Model
{
    use HasFactory;

    protected $table = 'presenca';
    protected $primaryKey = 'idt_presenca';
    public $timestamps = true;

    protected $fillable = [
        'idt_participante',
        'dat_presenca',
        'ind_presente'
    ];
    public function participante()
    {
        return $this->belongsTo(Participante::class, 'idt_participante', 'idt_participante');
    }
}
