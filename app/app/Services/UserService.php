<?php

namespace App\Services;

use App\Models\Pessoa;
use App\Models\User;

class UserService
{
    public static function getUsuarioByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public static function createPessoaFromLoggedUser(): Pessoa
    {
        $pessoa = Pessoa::where('idt_usuario', auth()->user()->id)->first();

        if (!$pessoa) {
            $pessoa = new Pessoa([
                'idt_usuario' => auth()->user()->id,
                'nom_pessoa' => auth()->user()->name,
                'eml_pessoa' => auth()->user()->email,
                'dat_nascimento' => '1900-01-01',
            ]);
            $pessoa->save();
        }

        return $pessoa;
    }
}
