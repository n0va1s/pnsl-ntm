<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TipoMovimento extends Model
{
    use HasFactory;

    protected $table = 'tipo_movimento';
    protected $primaryKey = 'idt_movimento';
    public $timestamps = true;

    protected $fillable = [
        'nom_movimento',
        'des_sigla',
        'dat_inicio',
    ];

    protected $casts = [
        'dat_inicio' => 'date',
    ];
}
