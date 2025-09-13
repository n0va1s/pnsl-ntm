<?php

namespace App\Services;

use App\Models\Ficha;
use App\Models\Pessoa;
use App\Models\PessoaSaude;
use App\Models\Participante;
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

    public static function atualizarAprovacaoFicha($id): bool
    {
        $ficha = Ficha::with('fichaSaude')->findOrFail($id);

        // Alterna o valor de aprovado
        $ficha->ind_aprovado = !$ficha->ind_aprovado;

        return DB::transaction(function () use ($ficha) {
            if ($ficha->ind_aprovado) {
                // Cria Pessoa a partir da ficha
                $pessoa = self::criarOuAtualizarPessoaAPartirDaFicha($ficha);

                // Atualiza a ficha com o id da pessoa criada
                $ficha->idt_pessoa = $pessoa->idt_pessoa;
                $ficha->save();

                // Restrições de saúde
                $restricoes = $ficha->fichaSaude->map(function ($item) {
                    return [
                        'idt_restricao'   => $item->idt_restricao,
                        'txt_complemento' => $item->txt_complemento,
                    ];
                })->toArray();

                self::criarPessoaSaude($pessoa->idt_pessoa, $restricoes);
                self::criarParticipante($pessoa->idt_pessoa, $ficha->idt_evento);
            } else {
                // Reprovação: remover dados relacionados e desvincular pessoa da ficha
                $pessoa = Pessoa::find($ficha->idt_pessoa);

                if ($pessoa) {
                    // Cascade
                    $pessoa->delete();
                }

                //$ficha->idt_pessoa = null;
                $ficha->save();
            }

            return true;
        });
    }

    private static function criarOuAtualizarPessoaAPartirDaFicha(Ficha $ficha): Pessoa
    {
        $dados = [
            'nom_pessoa'       => $ficha->nom_candidato,
            'nom_apelido'      => $ficha->nom_apelido,
            'tel_pessoa'       => $ficha->tel_candidato,
            'dat_nascimento'   => $ficha->dat_nascimento,
            'des_endereco'     => $ficha->des_endereco,
            'eml_pessoa'       => $ficha->eml_candidato,
            'tam_camiseta'     => $ficha->tam_camiseta,
            'tip_genero'       => $ficha->tip_genero,
            'ind_toca_violao'  => $ficha->ind_toca_instrumento,
            'ind_consentimento' => $ficha->ind_consentimento,
            'ind_restricao'    => $ficha->ind_restricao,
        ];

        if ($ficha->eml_candidato) {
            $dados['idt_usuario'] = UserService::getUsuarioByEmail($ficha->eml_candidato);
        }

        return Pessoa::updateOrCreate($dados);
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
