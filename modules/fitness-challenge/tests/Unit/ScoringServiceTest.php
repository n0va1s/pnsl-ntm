<?php

use Modules\FitnessChallenge\Enums\ScoringType;
use Modules\FitnessChallenge\Services\ScoringService;

it('calcula todos os modos basicos de pontuacao', function (ScoringType $type, array $checkIn, float $expected) {
    $score = app(ScoringService::class)->calculate($checkIn, [
        'scoring_type' => $type->value,
    ]);

    expect($score)->toBe($expected);
})->with([
    'total_workouts' => [ScoringType::TotalWorkouts, [], 1.0],
    'total_minutes' => [ScoringType::TotalMinutes, ['duration_minutes' => 45], 45.0],
    'total_calories' => [ScoringType::TotalCalories, ['calories' => 320], 320.0],
    'total_distance' => [ScoringType::TotalDistance, ['distance_km' => 5.7], 5.7],
    'total_steps' => [ScoringType::TotalSteps, ['steps' => 8700], 8700.0],
]);

it('calcula hustle points por sessao minutos e quilometros', function () {
    $service = app(ScoringService::class);

    $challenge = [
        'scoring_type' => ScoringType::HustlePoints->value,
        'hustle_points' => [
            ['activity_type' => 'yoga', 'points_per_unit' => 12, 'unit_type' => 'session'],
            ['activity_type' => 'musculacao', 'points_per_unit' => 10, 'unit_type' => 'minutes'],
            ['activity_type' => 'corrida', 'points_per_unit' => 8, 'unit_type' => 'km'],
        ],
    ];

    expect($service->calculate(['activity_type' => 'yoga'], $challenge))->toBe(12.0)
        ->and($service->calculate(['activity_type' => 'musculacao', 'duration_minutes' => 75], $challenge))->toBe(20.0)
        ->and($service->calculate(['activity_type' => 'corrida', 'distance_km' => 4.8], $challenge))->toBe(32.0)
        ->and($service->calculate(['activity_type' => 'bike'], $challenge))->toBe(0.0);
});
