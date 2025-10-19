<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voluntario extends Model
{
    use HasFactory;

    protected $table = 'voluntario';

    protected $primaryKey = 'idt_voluntario';

    public $timestamps = true;

    protected $fillable = [
        'idt_pessoa',
        'idt_evento',
        'idt_equipe',
        'idt_trabalhador', // para saber quem ja foi confirmado como trabalhador
        'txt_habilidade',
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
        // A ideia é selecionar as pessoas que são voluntárias para o evento
        // e ainda não foram confirmadas como trabalhadores.
        // Depois, carregar as equipes e habilidades diretamente para cada pessoa.

        return self::where('idt_evento', $idt_evento)
            ->whereNull('idt_trabalhador')
            ->with(['pessoa', 'equipe'])
            ->get()
            ->groupBy('idt_pessoa') // agrupa todos os voluntários por pessoa
            ->map(function ($voluntarios) {
                $pessoa = $voluntarios->first()->pessoa;

                return (object) [
                    'idt_voluntario' => $voluntarios->first()->idt_voluntario, // pode ser qualquer um, já que todos são da mesma pessoa
                    'pessoa' => $pessoa,
                    'equipes' => $voluntarios->map(function ($voluntario) {
                        return (object) [
                            'idt_equipe' => $voluntario->equipe->idt_equipe,
                            'des_grupo' => $voluntario->equipe->des_grupo,
                            'txt_habilidade' => $voluntario->txt_habilidade,
                        ];
                    })->unique('idt_equipe')->values(),
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
