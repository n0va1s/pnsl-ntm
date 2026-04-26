<?php

namespace Modules\FitnessChallenge\Services;

use Modules\FitnessChallenge\Enums\ScoringType;
use Modules\FitnessChallenge\Models\FitnessChallenge;
use Modules\FitnessChallenge\Models\FitnessCheckIn;

class ScoringService
{
    public function calculate(FitnessCheckIn|array $checkIn, FitnessChallenge|array $challenge): float
    {
        $type = $this->value($challenge, 'scoring_type');
        $type = $type instanceof ScoringType ? $type : ScoringType::from($type);

        return match ($type) {
            ScoringType::TotalWorkouts => 1,
            ScoringType::TotalMinutes => (float) ($this->value($checkIn, 'duration_minutes') ?? 0),
            ScoringType::TotalCalories => (float) ($this->value($checkIn, 'calories') ?? 0),
            ScoringType::TotalDistance => (float) ($this->value($checkIn, 'distance_km') ?? 0),
            ScoringType::TotalSteps => (float) ($this->value($checkIn, 'steps') ?? 0),
            ScoringType::HustlePoints => $this->calculateHustlePoints(
                $checkIn,
                $this->value($challenge, 'hustle_points') ?? []
            ),
        };
    }

    /**
     * @param  array<int, array{activity_type?: string, activityType?: string, points_per_unit?: int|float, pointsPerUnit?: int|float, unit_type?: string, unitType?: string}>  $config
     */
    private function calculateHustlePoints(FitnessCheckIn|array $checkIn, array $config): float
    {
        $activityType = $this->value($checkIn, 'activity_type');

        foreach ($config as $rule) {
            $ruleActivity = $rule['activity_type'] ?? $rule['activityType'] ?? null;

            if ($ruleActivity !== $activityType) {
                continue;
            }

            $pointsPerUnit = (float) ($rule['points_per_unit'] ?? $rule['pointsPerUnit'] ?? 0);
            $unitType = $rule['unit_type'] ?? $rule['unitType'] ?? 'session';

            return match ($unitType) {
                'session' => $pointsPerUnit,
                'minutes' => floor((float) ($this->value($checkIn, 'duration_minutes') ?? 0) / 30) * $pointsPerUnit,
                'km' => floor((float) ($this->value($checkIn, 'distance_km') ?? 0)) * $pointsPerUnit,
                default => 0,
            };
        }

        return 0;
    }

    private function value(FitnessCheckIn|FitnessChallenge|array $source, string $key): mixed
    {
        return is_array($source) ? ($source[$key] ?? null) : $source->{$key};
    }
}
