<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TipoRestricao extends Model
{
    use HasFactory;

    protected $table = 'tipo_restricao';
    protected $primaryKey = 'idt_restricao';
    public $timestamps = false;

    protected $fillable = [
        'des_restricao',
        'tip_restrição'
    ];

    public function fichaSaude()
    {
        return $this->hasMany(FichaSaude::class, 'idt_restricao');
    }

    public function pessoaSaude()
    {
        return $this->hasMany(PessoaSaude::class, 'idt_restricao');
    }
}

