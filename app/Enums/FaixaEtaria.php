<?php

namespace App\Enums;

enum FaixaEtaria: string
{
    case ADOLESCENTE = '12 a 15';
    case JOVEM = '16 a 23';
    case LIVRE = 'Não há';

    public function label(): string
    {
        return match ($this) {
            self::ADOLESCENTE => '12 a 15',
            self::JOVEM => '16 a 23',
            self::LIVRE => 'Não há',
        };
    }
}
