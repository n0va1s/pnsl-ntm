<?php

namespace App\Enums;

enum Genero: string
{
    case MASCULINO = 'M';
    case FEMININO = 'F';

    public function label(): string
    {
        return match ($this) {
            self::MASCULINO => 'Masculino',
            self::FEMININO => 'Feminino',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::MASCULINO => 'heroicon-m-user',
            self::FEMININO => 'heroicon-m-user-circle',
        };
    }
}
