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
        'idt_tipo_responsavel', 'nom_responsavel', 'tel_responsavel',
        'nom_candidato', 'des_telefone', 'des_endereco',
        'dat_nascimento', 'des_onde_estuda', 'des_mora_quem',
        'tam_camiseta', 'num_satisfacao', 'ind_toca_instrumento',
        'ind_aprovado'
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
}

