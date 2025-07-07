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

    public function fichaVem()
    {
        return $this->hasOne(FichaVem::class, 'idt_ficha');
    }

    public function fichaEcc()
    {
        return $this->hasOne(FichaEcc::class, 'idt_ficha');
    }

    public function fichaSaude()
    {
        return $this->hasMany(FichaSaude::class, 'idt_ficha');
    }

    public function analises()
    {
        return $this->hasMany(FichaAnalise::class, 'idt_ficha');
    }

    public function aprovar()
    {
        $pessoa = \App\Models\Pessoa::create([
            'nom_pessoa'           => $this->nom_candidato,
            'des_telefone'         => $this->tel_candidato,
            'des_endereco'         => $this->des_endereco,
            'dat_nascimento'       => $this->dat_nascimento,
            'tam_camiseta'         => $this->tam_camiseta,
            'ind_toca_instrumento' => $this->ind_toca_instrumento,
        ]);

        $evento = $this->idt_evento;

        if ($evento) {
            \App\Models\Participante::create([
                'idt_pessoa'    => $pessoa->idt_pessoa,
                'idt_evento'    => $evento->idt_evento,
                'tip_cor_troca' => null,
            ]);
        }

        $this->update(['ind_aprovado' => true]);
    }

    public function getDataNascimentoFormatada()
    {
        return $this->dat_nascimento ? $this->dat_nascimento->format('Y-m-d') : null;
    }
}
