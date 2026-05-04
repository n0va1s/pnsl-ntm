<?php

namespace App\Services;

use App\Models\Ficha;
use App\Models\FichaEcc;
use App\Models\FichaEccFilho;
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
        // Carrega a ficha com todas as relações necessárias
        $ficha = Ficha::with(['fichaSaude', 'fichaEcc.filhos'])->findOrFail($id);

        return DB::transaction(function () use ($ficha) {
            // 1. Inverte o status
            $ficha->ind_aprovado = ! $ficha->ind_aprovado;
            $ficha->save();

            if ($ficha->ind_aprovado) {
                self::aprovarFicha($ficha);
            } else {
                self::desaprovarFicha($ficha);
            }

            return $ficha->fresh();
        });
    }

    /**
     * Cria Pessoa(s), Saúde e Participante(s) de acordo com o tipo de ficha.
     *
     * - VEM / SGM: apenas o candidato torna-se Pessoa.
     * - ECC: o candidato, o cônjuge e cada filho tornam-se Pessoas independentes.
     */
    private static function aprovarFicha(Ficha $ficha): void
    {
        $fichaSaude = $ficha->fichaSaude->toArray();

        // ── Candidato (comum a todos os tipos) ───────────────────────────────
        if ($ficha->num_cpf_candidato) {
            $pessoa = self::criarPessoaCandidato($ficha);
            $ficha->update(['idt_pessoa' => $pessoa->idt_pessoa]);
            self::criarPessoaSaude($pessoa->idt_pessoa, $fichaSaude);
            self::criarParticipante($pessoa->idt_pessoa, $ficha->idt_evento);
        }

        // ── Dados exclusivos do ECC (cônjuge + filhos) ───────────────────────
        $fichaEcc = $ficha->fichaEcc;

        if (!$fichaEcc) {
            return; // Ficha VEM ou SGM: encerra aqui
        }

        // Cônjuge
        if ($fichaEcc->num_cpf_conjuge) {
            $pessoaConjuge = self::criarPessoaConjuge($fichaEcc);
            $fichaEcc->update(['idt_pessoa' => $pessoaConjuge->idt_pessoa]);
            self::criarPessoaSaude($pessoaConjuge->idt_pessoa, $fichaSaude);
            self::criarParticipante($pessoaConjuge->idt_pessoa, $ficha->idt_evento);
        }

        // Cada filho
        foreach ($fichaEcc->filhos as $filho) {
            if ($filho->num_cpf_filho) {
                $pessoaFilho = self::criarPessoaFilho($filho);
                $filho->update(['idt_pessoa' => $pessoaFilho->idt_pessoa]);
                self::criarParticipante($pessoaFilho->idt_pessoa, $ficha->idt_evento);
            }
        }
    }

    /**
     * Remove todos os participantes vinculados a esta ficha:
     * candidato, cônjuge e filhos (quando ECC).
     */
    private static function desaprovarFicha(Ficha $ficha): void
    {
        // Candidato
        self::removerParticipante($ficha->idt_pessoa, $ficha->idt_evento);

        $fichaEcc = $ficha->fichaEcc;

        if (!$fichaEcc) {
            return;
        }

        // Cônjuge
        self::removerParticipante($fichaEcc->idt_pessoa, $ficha->idt_evento);

        // Cada filho
        foreach ($fichaEcc->filhos as $filho) {
            self::removerParticipante($filho->idt_pessoa, $ficha->idt_evento);
        }
    }

    private static function criarPessoaCandidato(Ficha $ficha): Pessoa
    {
        $dados = [
            'num_cpf_pessoa' => $ficha->num_cpf_candidato,
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

    private static function criarPessoaConjuge(FichaEcc $fichaEcc): Pessoa
    {
        $dados = [
            'num_cpf_pessoa' => $fichaEcc->num_cpf_conjuge,
            'nom_pessoa'     => $fichaEcc->nom_conjuge,
            'nom_apelido'    => $fichaEcc->nom_apelido_conjuge,
            'tel_pessoa'     => $fichaEcc->tel_conjuge,
            'dat_nascimento' => $fichaEcc->dat_nascimento_conjuge,
            'eml_pessoa'     => $fichaEcc->eml_conjuge,
            'tam_camiseta'   => $fichaEcc->tam_camiseta_conjuge,
            'tip_genero'     => $fichaEcc->tip_genero_conjuge,
        ];

        if ($fichaEcc->eml_conjuge) {
            $usuario = UserService::getUsuarioByEmail($fichaEcc->eml_conjuge);

            if ($usuario) {
                $dados['idt_usuario'] = $usuario->id;
            }
        }

        return Pessoa::updateOrCreate(
            ['num_cpf_pessoa' => $fichaEcc->num_cpf_conjuge],
            $dados
        );
    }

    private static function criarPessoaFilho(FichaEccFilho $filho): Pessoa
    {
        $dados = [
            'num_cpf_pessoa' => $filho->num_cpf_filho,
            'nom_pessoa'     => $filho->nom_filho,
            'dat_nascimento' => $filho->dat_nascimento_filho,
            'eml_pessoa'     => $filho->eml_filho,
            'tel_pessoa'     => $filho->tel_filho,
        ];

        if ($filho->eml_filho) {
            $usuario = UserService::getUsuarioByEmail($filho->eml_filho);

            if ($usuario) {
                $dados['idt_usuario'] = $usuario->id;
            }
        }

        return Pessoa::updateOrCreate(
            ['num_cpf_pessoa' => $filho->num_cpf_filho],
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
