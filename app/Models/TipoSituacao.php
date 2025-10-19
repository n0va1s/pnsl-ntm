<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoSituacao extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tipo_situacao';

    protected $primaryKey = 'idt_situacao';

    public $timestamps = true;

    const CADASTRADA = 1;

    protected $fillable = [
        'des_situacao',
    ];

    public function fichasAnalises()
    {
        return $this->hasMany(FichaAnalise::class, 'idt_situacao');
    }
}
