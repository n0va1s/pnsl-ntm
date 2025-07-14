<?php

namespace App\Services;

use App\Models\Pessoa;
use App\Models\User;

class PessoaService
{
    public static function criarPessoaAPartirDoUsuario(User $user): ?Pessoa
    {
        $pessoa = Pessoa::where('eml_pessoa', $user->email)->first();

        if (!$pessoa) {
            $dados = [
                'idt_usuario' => $user->id,
                'nom_pessoa' => $user->name,
                'dat_nascimento' => '1900-01-01',
                'eml_pessoa' => $user->email,
            ];

            $pessoa = Pessoa::updateOrCreate($dados);
        }
        return $pessoa;
    }
}
