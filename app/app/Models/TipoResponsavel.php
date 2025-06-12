<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TipoResponsavel extends Model
{
    use HasFactory;

    protected $table = 'tipo_responsavel';
    protected $primaryKey = 'idt_responsavel';
    public $timestamps = false;

    protected $fillable = ['des_responsavel'];

    public function fichas()
    {
        return $this->hasMany(Ficha::class, 'idt_tipo_responsavel');
    }
}

