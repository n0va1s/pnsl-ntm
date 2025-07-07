<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pessoa extends Model
{
    use HasFactory;

    protected $table = 'pessoa';
    protected $primaryKey = 'idt_pessoa';
    public $timestamps = true;

    protected $fillable = [
        'idt_usuario',
        'nom_pessoa',
        'nom_apelido',
        'tel_pessoa',
        'dat_nascimento',
        'des_endereco',
        'eml_pessoa',
        'tam_camiseta',
        'tip_genero',
        'ind_toca_violao',
        'ind_consentimento',
        'ind_restricao',
    ];

    public function usuario()
    {
        return $this->hasOne(User::class, 'id', 'idt_usuario');
    }

    public function foto()
    {
        return $this->hasOne(PessoaFoto::class, 'idt_pessoa');
    }

    public function saude()
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
