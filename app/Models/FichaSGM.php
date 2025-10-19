<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FichaSGM extends Model
{
    use HasFactory;

    protected $table = 'ficha_sgm';

    public $timestamps = false;

    protected $fillable = [
        'idt_ficha',
        'idt_falar_com',
        'des_mora_quem',
        'nom_pai',
        'tel_pai',
        'nom_mae',
        'tel_mae',
    ];

    public function ficha()
    {
        return $this->belongsTo(Ficha::class, 'idt_ficha');
    }

    public function tipoResponsavel()
    {
        return $this->belongsTo(TipoResponsavel::class, 'idt_falar_com');
    }
}
