<?php

namespace App\Services;

use App\Models\Ficha;
use App\Models\Participante;
use App\Models\Pessoa;
use App\Models\PessoaSaude;
use App\Models\TipoMovimento;
use App\Models\TipoResponsavel;
use App\Models\TipoRestricao;
use App\Models\TipoSituacao;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class FichaService
{
    public static function dadosFixosFicha(Ficha $ficha): array
    {
        $cacheKey = "dados_ficha_form_{$ficha->idt_ficha}";

        return Cache::remember($cacheKey, 60 * 60, function () use ($ficha) {
            $ultimaAnalise = $ficha->analises()->with('situacao')->orderByDesc('created_at')->first();

            return [
                'situacoes' => TipoSituacao::select('idt_situacao', 'des_situacao')->get(),
                'movimentos' => TipoMovimento::select('idt_movimento', 'des_sigla', 'nom_movimento')->get(),
                'responsaveis' => TipoResponsavel::select('idt_responsavel', 'des_responsavel')->get(),
                'restricoes' => TipoRestricao::select('idt_restricao', 'tip_restricao', 'des_restricao')->get(),
                'ultimaSituacao' => $ultimaAnalise?->situacao ?? TipoSituacao::CADASTRADA,
                'ultimaAnalise' => $ultimaAnalise,

            ];
        });
    }

    private static function processarAprovacao(Ficha $ficha): void
    {
        $pessoa = self::criarOuAtualizarPessoaAPartirDaFicha($ficha);

        $ficha->update(['idt_pessoa' => $pessoa->idt_pessoa]);

        self::criarPessoaSaude(
            $pessoa->idt_pessoa,
            $ficha->fichaSaude->map(fn($r) => [
                'idt_restricao' => $r->idt_restricao,
                'txt_complemento' => $r->txt_complemento,
            ])->toArray()
        );

        self::criarParticipante($pessoa->idt_pessoa, $ficha->idt_evento);
    }

    private static function processarReprovacao(Ficha $ficha): void
    {
        optional(Pessoa::find($ficha->idt_pessoa))->delete();
    }


    public static function atualizarAprovacaoFicha($id): bool
    {
        $ficha = Ficha::with('fichaSaude')->findOrFail($id);

        // Alterna o valor de aprovado
        $ficha->ind_aprovado = ! $ficha->ind_aprovado;

        return DB::transaction(function () use ($ficha) {
            if ($ficha->ind_aprovado) {
                self::processarAprovacao($ficha);
            } else {
                self::processarReprovacao($ficha);
            }

            return true;
        });
    }

    private static function criarOuAtualizarPessoaAPartirDaFicha(Ficha $ficha): Pessoa
    {
        $dados = [
            'nom_pessoa' => $ficha->nom_candidato,
            'nom_apelido' => $ficha->nom_apelido,
            'tel_pessoa' => $ficha->tel_candidato,
            'dat_nascimento' => $ficha->dat_nascimento,
            'des_endereco' => $ficha->des_endereco,
            'eml_pessoa' => $ficha->eml_candidato,
            'tam_camiseta' => $ficha->tam_camiseta,
            'tip_genero' => $ficha->tip_genero,
            'ind_toca_violao' => $ficha->ind_toca_instrumento,
            'ind_consentimento' => $ficha->ind_consentimento,
            'ind_restricao' => $ficha->ind_restricao,
        ];

        if ($ficha->eml_candidato) {
            $dados['idt_usuario'] = UserService::getUsuarioByEmail($ficha->eml_candidato);
        }

        return Pessoa::updateOrCreate(
            ['eml_pessoa' => $ficha->eml_candidato],
            $dados
        );
    }

    private static function criarPessoaSaude($idt_pessoa, array $restricoes): void
    {
        foreach ($restricoes as $restricao) {
            PessoaSaude::create([
                'idt_pessoa' => $idt_pessoa,
                'idt_restricao' => $restricao['idt_restricao'],
                'txt_complemento' => $restricao['txt_complemento'] ?? null,
            ]);
        }
    }

    private static function criarParticipante($idt_pessoa, $idt_evento): void
    {
        Participante::create([
            'idt_pessoa' => $idt_pessoa,
            'idt_evento' => $idt_evento,
        ]);
    }
}
