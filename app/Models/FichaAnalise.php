<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FichaAnalise extends Model
{
    use HasFactory;

    protected $table = 'ficha_analise';

    public $timestamps = false;

    protected $fillable = [
        'idt_ficha',
        'idt_situacao',
        'txt_analise',
    ];

    public function ficha()
    {
        return $this->belongsTo(Ficha::class, 'idt_ficha');
    }

    public function situacao()
    {
        return $this->belongsTo(TipoSituacao::class, 'idt_situacao');
    }
}
