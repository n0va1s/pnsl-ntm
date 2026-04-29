<?php

namespace App\Services;

use App\Models\Ficha;
use App\Models\Participante;
use App\Models\Pessoa;
use App\Models\PessoaSaude;
use App\Models\TipoMovimento;
use App\Models\TipoResponsavel;
use App\Models\TipoRestricao;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class FichaService
{
    public static function dadosFixosFicha(Ficha $ficha): array
    {
        $cacheKey = "dados_ficha_form_{$ficha->idt_ficha}";

        return Cache::remember($cacheKey, 60 * 60, function () {
            return [
                'movimentos' => TipoMovimento::select('idt_movimento', 'des_sigla', 'nom_movimento')->get(),
                'responsaveis' => TipoResponsavel::select('idt_responsavel', 'des_responsavel')->get(),
                'restricoes' => TipoRestricao::select('idt_restricao', 'tip_restricao', 'des_restricao')->get(),
            ];
        });
    }

    public static function atualizarAprovacaoFicha(int $id): Ficha
    {
        // Carregamos a ficha com a relação de saúde
        $ficha = Ficha::with('fichaSaude')->findOrFail($id);

        return DB::transaction(function () use ($ficha) {
            // 1. Inverte o status
            $ficha->ind_aprovado = ! $ficha->ind_aprovado;
            $ficha->save();

            if ($ficha->ind_aprovado) {
                // 2. Se aprovou: Cria Pessoa -> Saúde -> Participante
                $pessoa = self::criarOuAtualizarPessoaAPartirDaFicha($ficha);
                
                // Vincula a pessoa à ficha
                $ficha->update(['idt_pessoa' => $pessoa->idt_pessoa]);

                self::criarPessoaSaude(
                    $pessoa->idt_pessoa, 
                    $ficha->fichaSaude->toArray() 
                );

                self::criarParticipante($pessoa->idt_pessoa, $ficha->idt_evento);
            } else {
                self::removerParticipante($ficha->idt_pessoa, $ficha->idt_evento);
            }

            return $ficha;
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
            $usuario = UserService::getUsuarioByEmail($ficha->eml_candidato);
            
            if ($usuario) {
                $dados['idt_usuario'] = $usuario->id; 
            }
        }

        return Pessoa::updateOrCreate(
            ['eml_pessoa' => $ficha->eml_candidato],
            $dados
        );
    }

    private static function criarPessoaSaude(int $idt_pessoa, array $restricoes): void
    {
        // Limpa restrições antigas antes de inserir para evitar duplicidade em re-aprovações
        PessoaSaude::where('idt_pessoa', $idt_pessoa)->delete();

        foreach ($restricoes as $restricao) {
            PessoaSaude::create([
                'idt_pessoa' => $idt_pessoa,
                'idt_restricao' => $restricao['idt_restricao'],
                'txt_complemento' => $restricao['txt_complemento'] ?? null,
            ]);
        }
    }

    private static function criarParticipante(int $idt_pessoa, int $idt_evento): void
    {
        Participante::updateOrCreate(
            [
                'idt_pessoa' => $idt_pessoa, 
                'idt_evento' => $idt_evento
            ]
        );
    }

    private static function removerParticipante(?int $idt_pessoa, int $idt_evento): void
    {
        if ($idt_pessoa) {
            Participante::where('idt_pessoa', $idt_pessoa)
                ->where('idt_evento', $idt_evento)
                ->delete();
        }
    }
}
