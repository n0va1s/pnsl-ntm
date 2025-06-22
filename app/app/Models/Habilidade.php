<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Habilidade extends Model
{
    use HasFactory;

    protected $table = 'habilidade';
    protected $primaryKey = 'idt_habilidade';

    protected $fillable = [
        'idt_equipe',
        'des_habilidade',
    ];

    public function equipe()
    {
        return $this->belongsTo(TipoEquipe::class, 'idt_equipe');
    }
}
