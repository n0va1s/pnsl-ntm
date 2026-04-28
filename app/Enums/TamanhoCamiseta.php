<?php

namespace App\Enums;

enum TamanhoCamiseta: string
{
    case PP = 'PP';
    case P = 'P';
    case M = 'M';
    case G = 'G';
    case GG = 'GG';
    case EG = 'EG';

    public function label(): string
    {
        return match ($this) {
            self::PP => 'Extra Pequeno',
            self::P => 'Pequeno',
            self::M => 'Médio',
            self::G => 'Grande',
            self::GG => 'Extra Grande',
            self::EG => 'Extra G (Especial)',
        };
    }
}
