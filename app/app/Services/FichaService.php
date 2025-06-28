<?php

namespace App\Services;

use App\Models\Ficha;
use App\Models\TipoMovimento;
use App\Models\TipoResponsavel;
use App\Models\TipoRestricao;
use App\Models\TipoSituacao;
use Illuminate\Support\Facades\Cache;

class FichaService
{
    public static function dadosFixosFicha(Ficha $ficha): array
    {
        $cacheKey = "dados_ficha_form_{$ficha->idt_ficha}";

        return Cache::remember($cacheKey, 60 * 60, function () use ($ficha) {
            $ultimaAnalise = $ficha->analises()->with('situacao')->orderByDesc('created_at')->first();

            return [
                'situacoes' => TipoSituacao::select('idt_situacao', 'nom_situacao')->get(),
                'movimentos' => TipoMovimento::select('idt_movimento', 'sig_movimento', 'nom_movimento')->get(),
                'responsaveis' => TipoResponsavel::select('idt_responsavel', 'des_responsavel')->get(),
                'restricoes' => TipoRestricao::select('idt_restricao', 'tip_restricao', 'des_restricao')->get(),
                'ultimaSituacao' => $ultimaAnalise?->situacao ?? TipoSituacao::CADASTRADA,
                'ultimaAnalise' => $ultimaAnalise,
            ];
        });
    }
}
