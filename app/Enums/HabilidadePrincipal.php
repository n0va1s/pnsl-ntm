<?php

namespace App\Enums;

enum HabilidadePrincipal: string
{
    case VIOLAO = 'V';
    case INSTRUMENTO = 'S';
    case CANTO = 'C';
    case MIDIAS_SOCIAIS = 'M';
    case AUDIOVISUAL = 'A';
    case DESENVOLVIMENTO = 'T';
    case FOTOGRAFIA = 'F';
    case OUTRA = 'O';

    public function label(): string
    {
        return match ($this) {
            self::VIOLAO => 'Toco violão',
            self::INSTRUMENTO => 'Toco outro instrumento',
            self::CANTO => 'Sei cantar',
            self::MIDIAS_SOCIAIS => 'Trabalho com mídias sociais',
            self::AUDIOVISUAL => 'Crio material audiovisual',
            self::DESENVOLVIMENTO => 'Desenvolvo APPs ou Sites',
            self::FOTOGRAFIA => 'Fotografo',
            self::OUTRA => 'Outra habilidade',
        };
    }
}
