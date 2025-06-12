<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pessoa extends Model
{
    use HasFactory;

    protected $table = 'pessoa';
    protected $primaryKey = 'idt_pessoa';
    public $timestamps = false;

    protected $fillable = [
        'nom_pessoa', 'des_telefone', 'des_endereco',
        'dat_nascimento', 'tam_camiseta', 'ind_toca_instrumento'
    ];

    public function restricoes()
    {
        return $this->hasMany(PessoaSaude::class, 'idt_pessoa');
    }

    public function participante()
    {
        return $this->hasMany(Participante::class, 'idt_pessoa');
    }

    public function trabalhador()
    {
        return $this->hasMany(Trabalhador::class, 'idt_pessoa');
    }
}

