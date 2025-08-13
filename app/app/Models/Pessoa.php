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
        'idt_parceiro',
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

    protected $casts = [
        'dat_nascimento' => 'date',
        'ind_toca_violao' => 'boolean',
        'ind_consentimento' => 'boolean',
        'ind_restricao' => 'boolean',
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

    public function voluntarios()
    {
        return $this->hasMany(Voluntario::class, 'idt_pessoa');
    }

    // Relationship to partner
    public function parceiro()
    {
        // One person has one partner (self-referencing one-to-one)
        return $this->belongsTo(Pessoa::class, 'idt_parceiro', 'idt_pessoa');
    }

    public function setParceiro(Pessoa $umaSoCarne)
    {
        if ($umaSoCarne && $this->idt_pessoa === $umaSoCarne->idt_pessoa) {
            throw new \InvalidArgumentException("Uma pessoa não pode ser parceira de si mesma.");
        }

        $this->idt_parceiro = $umaSoCarne ? $umaSoCarne->idt_pessoa : null;
        $this->save();

        if ($umaSoCarne) {
            $umaSoCarne->idt_parceiro = $this->idt_pessoa;
            $umaSoCarne->save();
        }
    }

    public function removeParceiro()
    {
        if ($this->parceiro) {
            $umaSoCarne = $this->parceiro;
            $umaSoCarne->idt_parceiro = null;
            $umaSoCarne->save();
        }
        $this->idt_parceiro = null;
        $this->save();
    }

    public function getDataNascimentoFormatada()
    {
        return $this->dat_nascimento
            ? $this->dat_nascimento->format('Y-m-d')
            : null;
    }
}
