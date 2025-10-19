<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoResponsavel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tipo_responsavel';

    protected $primaryKey = 'idt_responsavel';

    public $timestamps = true;

    protected $fillable = [
        'des_responsavel',
    ];

    public function fichas()
    {
        return $this->hasMany(FichaVem::class, 'idt_falar_com');
    }
}
