<?php

namespace Modules\FitnessChallenge\Enums;

enum ScoringType: string
{
    case TotalWorkouts = 'total_workouts';
    case TotalMinutes = 'total_minutes';
    case TotalCalories = 'total_calories';
    case TotalDistance = 'total_distance';
    case TotalSteps = 'total_steps';
    case HustlePoints = 'hustle_points';

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $type) => [$type->value => $type->label()])
            ->all();
    }

    public function label(): string
    {
        return match ($this) {
            self::TotalWorkouts => 'Total de treinos',
            self::TotalMinutes => 'Total de minutos',
            self::TotalCalories => 'Total de calorias',
            self::TotalDistance => 'Total de distancia',
            self::TotalSteps => 'Total de passos',
            self::HustlePoints => 'Hustle points',
        };
    }
}
