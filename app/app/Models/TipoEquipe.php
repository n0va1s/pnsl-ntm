<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TipoEquipe extends Model
{
    use HasFactory;

    protected $table = 'tipo_equipe';
    protected $primaryKey = 'idt_equipe';
    public $timestamps = true;

    protected $fillable = [
        'des_grupo',
        'txt_documento',
    ];

    public function trabalhadores()
    {
        return $this->hasMany(Trabalhador::class, 'idt_equipe');
    }

    public function voluntarios()
    {
        return $this->hasMany(Voluntario::class, 'idt_equipe');
    }
}
