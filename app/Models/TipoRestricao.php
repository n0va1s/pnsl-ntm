<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoRestricao extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tipo_restricao';
    protected $primaryKey = 'idt_restricao';
    public $timestamps = true;

    protected $fillable = [
        'des_restricao',
        'tip_restricao'
    ];

    public function fichas()
    {
        return $this->hasMany(FichaSaude::class, 'idt_restricao');
    }

    public function pessoas()
    {
        return $this->hasMany(PessoaSaude::class, 'idt_restricao');
    }
}
