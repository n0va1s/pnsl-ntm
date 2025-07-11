<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class PessoaFoto extends Model
{
    use HasFactory;

    protected $table = 'pessoa_foto';
    protected $primaryKey = 'idt_pessoa';
    public $timestamps = true;

    protected $fillable = [
        'idt_pessoa',
        'med_foto',
    ];

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class, 'idt_pessoa');
    }
}
