<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ficha extends Model
{
    use HasFactory;

    protected $table = 'ficha';
    protected $primaryKey = 'idt_ficha';
    public $timestamps = false;

    protected $fillable = [
        'idt_tipo_responsavel',
        'nom_responsavel',
        'tel_responsavel',
        'nom_candidato',
        'des_telefone',
        'des_endereco',
        'dat_nascimento',
        'des_onde_estuda',
        'des_mora_quem',
        'tam_camiseta',
        'num_satisfacao',
        'ind_toca_instrumento',
        'ind_aprovado'
    ];

    protected $casts = [
        'ind_aprovado' => 'boolean',
        'dat_nascimento' => 'date',
    ];

    public function tipoResponsavel()
    {
        return $this->belongsTo(TipoResponsavel::class, 'idt_tipo_responsavel');
    }

    public function analises()
    {
        return $this->hasMany(FichaAnalise::class, 'idt_ficha');
    }

    public function restricoes()
    {
        return $this->hasMany(FichaSaude::class, 'idt_ficha');
    }

    public function aprovar()
    {

        $pessoa = \App\Models\Pessoa::create([
            'nom_pessoa'        => $this->nom_candidato,
            'des_telefone'      => $this->des_telefone,
            'des_endereco'      => $this->des_endereco,
            'dat_nascimento'    => $this->dat_nascimento,
            'tam_camiseta'      => $this->tam_camiseta,
            'ind_toca_instrumento' => $this->ind_toca_instrumento,
        ]);

        // Associar com último evento existente
        $evento = \App\Models\Evento::latest()->first();
        if ($evento) {
            \App\Models\Participante::create([
                'idt_pessoa'    => $pessoa->idt_pessoa,
                'idt_evento'    => $evento->idt_evento,
                'tip_cor_troca' => null,
            ]);
        }

        $this->update(['ind_aprovado' => true]);
    }
}
