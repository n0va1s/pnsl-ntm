<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ficha extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ficha';
    protected $primaryKey = 'idt_ficha';
    public $timestamps = true;

    protected $fillable = [
        'idt_evento',
        'idt_pessoa',
        'tip_genero',
        'nom_candidato',
        'nom_apelido',
        'dat_nascimento',
        'tel_candidato',
        'eml_candidato',
        'des_endereco',
        'tam_camiseta',
        'tip_como_soube',
        'ind_catolico',
        'ind_toca_instrumento',
        'ind_consentimento',
        'ind_aprovado',
        'ind_restricao',
        'txt_observacao',
    ];

    protected $casts = [
        'dat_nascimento' => 'date',
        'ind_catolico' => 'boolean',
        'ind_toca_instrumento' => 'boolean',
        'ind_consentimento' => 'boolean',
        'ind_aprovado' => 'boolean',
        'ind_restricao' => 'boolean',
    ];

    public function getRouteKeyName()
    {
        return 'idt_ficha';
    }

    // RELACIONAMENTOS

    public function evento()
    {
        return $this->belongsTo(Evento::class, 'idt_evento');
    }

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class, 'idt_pessoa');
    }

    public function fichaVem()
    {
        return $this->hasOne(FichaVem::class, 'idt_ficha');
    }

    public function fichaEcc()
    {
        return $this->hasOne(FichaEcc::class, 'idt_ficha');
    }

    public function fichaSGM()
    {
        return $this->hasOne(FichaSGM::class, 'idt_ficha');
    }

    public function fichaSaude()
    {
        return $this->hasMany(FichaSaude::class, 'idt_ficha');
    }

    public function analises()
    {
        return $this->hasMany(FichaAnalise::class, 'idt_ficha');
    }

    public function getDataNascimentoFormatada()
    {
        return $this->dat_nascimento
            ? $this->dat_nascimento->format('Y-m-d')
            : null;
    }
}
