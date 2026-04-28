<?php

namespace App\Enums;

enum Perfil: string
{
    case ADMIN = 'admin';
    case COORD = 'coord';
    case USER = 'user';
    case AMARELA = 'amarela';
    case LARANJA = 'laranja';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrador',
            self::COORD => 'Coordenador',
            self::USER => 'Usuário',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::ADMIN => 'shield-exclamation',
            self::COORD => 'users',
            self::USER => 'user',
        };
    }
}
