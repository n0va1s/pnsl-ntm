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

        return Pessoa::with([
            'voluntarios' => function ($query) use ($idt_evento) {
                $query->where('idt_evento', $idt_evento)
                    ->whereNull('idt_trabalhador')
                    ->with('equipe')
                    ->select('idt_pessoa', 'idt_equipe', 'txt_habilidade', 'idt_voluntario');
            }
        ])
            ->whereHas('voluntarios', function ($query) use ($idt_evento) {
                $query->where('idt_evento', $idt_evento)
                    ->whereNull('idt_trabalhador');
            })
            ->get()
            ->map(function ($pessoa) {
                // Transforma o objeto Pessoa para o formato desejado, agrupando as equipes
                return (object) [
                    'idt_voluntario' => $pessoa->voluntarios->first()->idt_voluntario ?? null, // Pega o ID do primeiro voluntário para a pessoa
                    'pessoa' => $pessoa,
                    'equipes' => $pessoa->voluntarios->map(function ($voluntario) {
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
