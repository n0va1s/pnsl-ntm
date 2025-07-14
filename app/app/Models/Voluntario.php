<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Voluntario extends Model
{
    use HasFactory;

    protected $table = 'voluntario';
    protected $primaryKey = 'idt_voluntario';
    public $timestamps = false;

    protected $fillable = [
        'idt_pessoa',
        'idt_evento',
        'idt_equipe',
        'txt_candidatura',
    ];

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class, 'idt_pessoa');
    }

    public function evento()
    {
        return $this->belongsTo(Evento::class, 'idt_evento');
    }

    public function equipe()
    {
        return $this->belongsTo(TipoEquipe::class, 'idt_equipe');
    }

    public static function listarAgrupadoPorPessoa(int $idt_evento)
    {
        return self::with(['pessoa', 'equipe'])
            ->where('idt_evento', $idt_evento)
            ->get()
            ->groupBy('idt_pessoa')
            ->map(function ($registros) {
                $primeiro = $registros->first();

                return (object) [
                    'idt_voluntario' => $primeiro->idt_voluntario,
                    'pessoa' => $primeiro->pessoa,
                    'equipes' => $registros->map(fn($v) => $v->equipe)->unique('idt_equipe'),
                ];
            })
            ->values();
    }

    public static function listarEquipesSelecionadas(int $idt_evento, int $idt_pessoa)
    {
        return self::where('idt_pessoa', $idt_pessoa)
            ->where('idt_evento', $idt_evento)
            ->with('equipe')
            ->get()
            ->pluck('equipe');
    }
}
