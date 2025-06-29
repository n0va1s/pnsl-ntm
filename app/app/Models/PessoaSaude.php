<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class PessoaSaude extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pessoa_saude';
    public $timestamps = true;

    protected $fillable = [
        'idt_pessoa',
        'idt_restricao',
        'ind_remedio_regular',
        'txt_complemento',
    ];

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class, 'idt_pessoa');
    }

    public function restricao()
    {
        return $this->belongsTo(TipoRestricao::class, 'idt_restricao');
    }
}
