<?php

namespace Modules\FitnessChallenge\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Modules\FitnessChallenge\Models\FitnessChallenge;
use Modules\FitnessChallenge\Models\FitnessCheckIn;
use Modules\FitnessChallenge\Models\FitnessComment;
use Modules\FitnessChallenge\Services\ScoringService;

class CheckInController extends Controller
{
    public function index(Request $request, FitnessChallenge $challenge): JsonResponse
    {
        $this->ensureParticipant($request, $challenge);

        $checkIns = $challenge->checkIns()
            ->with(['user:id,name,email', 'comments.user:id,name'])
            ->withCount('likes')
            ->latest()
            ->cursorPaginate(20);

        return response()->json($checkIns);
    }

    public function store(Request $request, FitnessChallenge $challenge, ScoringService $scoring): JsonResponse
    {
        $participant = $this->ensureParticipant($request, $challenge);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'media_path' => ['required', 'string', 'max:2048'],
            'media_type' => ['required', Rule::in(['image', 'video'])],
            'duration_minutes' => ['nullable', 'integer', 'min:0'],
            'distance_km' => ['nullable', 'numeric', 'min:0'],
            'calories' => ['nullable', 'integer', 'min:0'],
            'steps' => ['nullable', 'integer', 'min:0'],
            'activity_type' => ['nullable', 'string', 'max:80'],
        ]);

        $checkIn = DB::transaction(function () use ($request, $challenge, $participant, $validated, $scoring) {
            $checkIn = new FitnessCheckIn([
                ...$validated,
                'fitness_challenge_id' => $challenge->id,
                'user_id' => $request->user()->id,
                'fitness_team_id' => $participant->fitness_team_id,
            ]);

            $checkIn->score = $scoring->calculate($checkIn, $challenge);
            $checkIn->save();

            $participant->increment('total_check_ins');
            $participant->increment('total_score', $checkIn->score);

            if ($participant->team) {
                $participant->team->increment('total_score', $checkIn->score);
            }

            return $checkIn;
        });

        return response()->json(['data' => $checkIn], 201);
    }

    public function destroy(Request $request, FitnessCheckIn $checkIn): JsonResponse
    {
        abort_unless($checkIn->user_id === $request->user()->id, 403);

        DB::transaction(function () use ($checkIn) {
            $participant = $checkIn->challenge->participants()
                ->where('user_id', $checkIn->user_id)
                ->first();

            if ($participant) {
                $participant->decrement('total_check_ins');
                $participant->decrement('total_score', $checkIn->score);
            }

            if ($checkIn->team) {
                $checkIn->team->decrement('total_score', $checkIn->score);
            }

            $checkIn->delete();
        });

        return response()->json(status: 204);
    }

    public function like(Request $request, FitnessCheckIn $checkIn): JsonResponse
    {
        $this->ensureParticipant($request, $checkIn->challenge);

        $exists = $checkIn->likes()->where('user_id', $request->user()->id)->exists();

        if ($exists) {
            $checkIn->likes()->detach($request->user()->id);
        } else {
            $checkIn->likes()->attach($request->user()->id);
        }

        return response()->json([
            'liked' => ! $exists,
            'likes_count' => $checkIn->likes()->count(),
        ]);
    }

    public function comment(Request $request, FitnessCheckIn $checkIn): JsonResponse
    {
        $this->ensureParticipant($request, $checkIn->challenge);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:1000'],
        ]);

        $comment = FitnessComment::create([
            ...$validated,
            'fitness_check_in_id' => $checkIn->id,
            'user_id' => $request->user()->id,
        ]);

        return response()->json(['data' => $comment->load('user:id,name')], 201);
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
