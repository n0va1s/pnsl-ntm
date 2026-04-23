<?php

namespace App\Enums;

// Primeiro enum do projeto — estabelece padrão: valores snake_case, labels pt_BR
// D-05: values em snake_case; D-05: labels em pt_BR
enum PapelEquipe: string
{
    case CoordGeral   = 'coord_geral';
    case CoordEquipeH = 'coord_equipe_h';
    case CoordEquipeM = 'coord_equipe_m';
    case MembroEquipe = 'membro_equipe';

    public function label(): string
    {
        return match ($this) {
            self::CoordGeral   => 'Coordenador Geral',
            self::CoordEquipeH => 'Coordenador de Equipe H',
            self::CoordEquipeM => 'Coordenador de Equipe M',
            self::MembroEquipe => 'Membro de Equipe',
        };
    }

    /** @return array<string, string> Para uso em <flux:select> e afins */
    public static function opcoes(): array
    {
        return array_column(
            array_map(fn ($case) => ['value' => $case->value, 'label' => $case->label()], self::cases()),
            'label',
            'value'
        );
    }

    public function isCoordenador(): bool
    {
        return $this !== self::MembroEquipe;
    }

    public function requerSexo(): ?string
    {
        return match ($this) {
            self::CoordEquipeH => 'M',
            self::CoordEquipeM => 'F',
            default            => null,
        };
    }
}
