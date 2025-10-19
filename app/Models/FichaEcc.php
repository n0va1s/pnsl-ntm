<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FichaEcc extends Model
{
    use HasFactory;

    protected $table = 'ficha_ecc';

    public $timestamps = false;

    protected $fillable = [
        'idt_ficha',
        'nom_conjuge',
        'nom_apelido_conjuge',
        'tel_conjuge',
        'dat_nascimento_conjuge',
        'tam_camiseta_conjuge',
    ];

    protected $casts = [
        'dat_nascimento_conjuge' => 'date',
    ];

    public function ficha()
    {
        return $this->belongsTo(Ficha::class, 'idt_ficha');
    }
}
