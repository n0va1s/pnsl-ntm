<?php

namespace Modules\FitnessChallenge\Services;

use Illuminate\Support\Collection;
use Modules\FitnessChallenge\Models\FitnessChallenge;

class LeaderboardService
{
    public function individual(FitnessChallenge $challenge): Collection
    {
        return $challenge->participants()
            ->with('user')
            ->orderByDesc('total_score')
            ->orderByDesc('total_check_ins')
            ->get()
            ->values()
            ->map(function ($participant, int $index) {
                return [
                    'position' => $index + 1,
                    'user_id' => $participant->user_id,
                    'name' => $participant->user?->name,
                    'total_score' => (float) $participant->total_score,
                    'total_check_ins' => $participant->total_check_ins,
                ];
            });
    }

    public function teams(FitnessChallenge $challenge): Collection
    {
        return $challenge->teams()
            ->withCount('participants')
            ->orderByDesc('total_score')
            ->get()
            ->values()
            ->map(function ($team, int $index) {
                return [
                    'position' => $index + 1,
                    'team_id' => $team->id,
                    'name' => $team->name,
                    'total_score' => (float) $team->total_score,
                    'participants_count' => $team->participants_count,
                ];
            });
    }
}
