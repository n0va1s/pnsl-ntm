<?php

namespace App\Enums;

enum TipoEvento: string
{
    case ENCONTRO = 'E';
    case POS_ENCONTRO = 'P';
    case DESAFIO = 'D';

    public function label(): string
    {
        return match ($this) {
            self::ENCONTRO => 'Encontro Anual',
            self::POS_ENCONTRO => 'Pós-Encontro',
            self::DESAFIO => 'Desafio',
        };
    }
}
