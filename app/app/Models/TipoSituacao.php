<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoSituacao extends Model
{
    use HasFactory;

    protected $table = 'tipo_situacao';

    protected $primaryKey = 'idt_situacao';

    public $timestamps = false;

    const CADASTRADA = 1;
    const CONFIRMADA = 7;
    const DESISTENTE = 8;
    const CANCELADA = 9;

    protected $fillable = ['des_situacao'];

    public function fichasAnalises()
    {
        return $this->hasMany(FichaAnalise::class, 'idt_situacao');
    }
}
