<?php

namespace Modules\FitnessChallenge\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\FitnessChallenge\Models\FitnessChallenge;
use Modules\FitnessChallenge\Services\LeaderboardService;

class LeaderboardController extends Controller
{
    public function individual(Request $request, FitnessChallenge $challenge, LeaderboardService $leaderboard): JsonResponse
    {
        $this->ensureParticipant($request, $challenge);

        return response()->json(['data' => $leaderboard->individual($challenge)]);
    }

    public function teams(Request $request, FitnessChallenge $challenge, LeaderboardService $leaderboard): JsonResponse
    {
        $this->ensureParticipant($request, $challenge);

        return response()->json(['data' => $leaderboard->teams($challenge)]);
    }

    private function ensureParticipant(Request $request, FitnessChallenge $challenge): void
    {
        abort_unless(
            $challenge->participants()->where('user_id', $request->user()->id)->exists(),
            403
        );
    }
}
