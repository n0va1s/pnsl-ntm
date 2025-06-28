<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TipoSituacao extends Model
{
    use HasFactory;

    protected $table = 'tipo_situacao';
    protected $primaryKey = 'idt_situacao';
    public $timestamps = false;

    const CADASTRADA = 1;

    protected $fillable = ['des_situacao'];

    public function fichasAnalises()
    {
        return $this->hasMany(FichaAnalise::class, 'idt_situacao');
    }
}
