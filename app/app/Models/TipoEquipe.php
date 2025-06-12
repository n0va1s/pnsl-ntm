<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TipoEquipe extends Model
{
    use HasFactory;

    protected $table = 'tipo_equipe';
    protected $primaryKey = 'idt_equipe';
    public $timestamps = false;

    protected $fillable = ['des_grupo'];

    public function trabalhadores()
    {
        return $this->hasMany(Trabalhador::class, 'idt_equipe');
    }
}

