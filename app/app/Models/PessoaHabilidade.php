<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PessoaHabilidade extends Model
{
    use HasFactory;

    protected $table = 'pessoa_habilidade';
    public $timestamps = true;

    protected $fillable = [
        'idt_pessoa',
        'idt_habilidade',
        'num_escala',
        'txt_complemento',
    ];

    protected $casts = [
        'num_escala' => 'integer',
    ];

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class, 'idt_pessoa');
    }

    public function habilidade()
    {
        return $this->belongsTo(Habilidade::class, 'idt_habilidade');
    }
}
