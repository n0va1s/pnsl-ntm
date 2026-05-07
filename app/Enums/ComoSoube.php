<?php

namespace App\Enums;

enum ComoSoube: string
{
    case INDICACAO = 'IND';
    case PADRE = 'PAD';
    case OUTRO = 'OUT';

    public function label(): string
    {
        return match ($this) {
            self::INDICACAO => 'Indicação',
            self::PADRE => 'Padre',
            self::OUTRO => 'Outro',
        };
    }
}
