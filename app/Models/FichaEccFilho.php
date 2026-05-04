<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FichaEccFilho extends Model
{
    use HasFactory;

    protected $table = 'ficha_ecc_filho';

    protected $primaryKey = 'idt_filho';

    protected $fillable = [
        'idt_ficha',
        'idt_pessoa',
        'num_cpf_filho',
        'nom_filho',
        'dat_nascimento_filho',
        'eml_filho',
        'tel_filho',
    ];

    protected $casts = [
        'dat_nascimento_filho' => 'date:Y-m-d',
    ];

    public function fichaEcc()
    {
        return $this->belongsTo(FichaEcc::class, 'idt_ficha');
    }

    public function getDataNascimentoFormatada()
    {
        return $this->dat_nascimento_filho
            ? $this->dat_nascimento_filho->format('Y-m-d')
            : null;
    }
}
