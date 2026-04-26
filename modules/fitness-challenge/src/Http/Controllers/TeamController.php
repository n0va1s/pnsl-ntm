<?php

namespace Modules\FitnessChallenge\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\FitnessChallenge\Models\FitnessChallenge;
use Modules\FitnessChallenge\Models\FitnessTeam;

class TeamController extends Controller
{
    public function store(Request $request, FitnessChallenge $challenge): JsonResponse
    {
        $this->ensureParticipant($request, $challenge);
        abort_unless($challenge->is_team_challenge, 422, 'Este desafio nao usa times.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'avatar_path' => ['nullable', 'string', 'max:2048'],
        ]);

        $team = $challenge->teams()->create($validated);

        return response()->json(['data' => $team], 201);
    }

    public function join(Request $request, FitnessChallenge $challenge, FitnessTeam $team): JsonResponse
    {
        $participant = $this->ensureParticipant($request, $challenge);

        abort_unless($team->fitness_challenge_id === $challenge->id, 404);

        $participant->update(['fitness_team_id' => $team->id]);

        return response()->json(['data' => $participant->refresh()]);
    }

    private function ensureParticipant(Request $request, FitnessChallenge $challenge)
    {
        $participant = $challenge->participants()
            ->where('user_id', $request->user()->id)
            ->first();

        abort_unless($participant, 403);

        return $participant;
    }
}
