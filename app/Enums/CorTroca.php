<?php

namespace App\Enums;

enum CorTroca: string
{
    case VERMELHA = 'vermelha';
    case AZUL = 'azul';
    case VERDE = 'verde';
    case AMARELA = 'amarela';
    case LARANJA = 'laranja';

    public function label(): string
    {
        return match ($this) {
            self::VERMELHA => 'Vermelha',
            self::AZUL => 'Azul',
            self::VERDE => 'Verde',
            self::AMARELA => 'Amarela',
            self::LARANJA => 'Laranja',
        };
    }

    public function cor(): string
    {
        return match ($this) {
            self::VERMELHA => '#FF0000',
            self::AZUL => '#0000FF',
            self::VERDE => '#008000',
            self::AMARELA => '#FFFF00',
            self::LARANJA => '#FFA500',
        };
    }
}
