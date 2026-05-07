<?php

namespace App\Enums;

enum EstadoCivil: string
{
    case SOLTEIRO = 'S';
    case CASADO = 'C';
    case SEGUNDA_UNIAO = 'E';
    case UNIAO_ESTAVEL = 'U';
    case CASADO_SOLO = 'M';
    case DIVORCIADO = 'D';
    case VIUVO = 'V';

    public function label(): string
    {
        return match ($this) {
            self::SOLTEIRO => 'Solteiro(a)',
            self::CASADO => 'Casado(a)',
            self::SEGUNDA_UNIAO => 'Casado(a) em 2ª União',
            self::UNIAO_ESTAVEL => 'União Estável',
            self::CASADO_SOLO => 'Casado(a) (Somente 1 participará)',
            self::DIVORCIADO => 'Divorciado(a)',
            self::VIUVO => 'Viúvo(a)',
        };
    }

    public function precisaDeConjuge(): bool
    {
        return match ($this) {
            self::CASADO,
            self::SEGUNDA_UNIAO,
            self::UNIAO_ESTAVEL => true,
            default => false,
        };
    }
}
