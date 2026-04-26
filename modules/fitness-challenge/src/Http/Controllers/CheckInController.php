<?php

namespace Modules\FitnessChallenge\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Modules\FitnessChallenge\Enums\ModerationStatus;
use Modules\FitnessChallenge\Models\FitnessChallenge;
use Modules\FitnessChallenge\Models\FitnessCheckIn;
use Modules\FitnessChallenge\Models\FitnessComment;
use Modules\FitnessChallenge\Services\CheckInAwardService;
use Modules\FitnessChallenge\Services\MediaSafetyService;
use Modules\FitnessChallenge\Services\ScoringService;

class CheckInController extends Controller
{
    public function index(Request $request, FitnessChallenge $challenge): JsonResponse
    {
        $this->ensureParticipant($request, $challenge);

        $checkIns = $challenge->checkIns()
            ->where('moderation_status', ModerationStatus::Approved->value)
            ->with(['user:id,name,email', 'comments.user:id,name'])
            ->withCount('likes')
            ->latest()
            ->cursorPaginate(20);

        return response()->json($checkIns);
    }

    public function store(
        Request $request,
        FitnessChallenge $challenge,
        MediaSafetyService $mediaSafety,
        CheckInAwardService $awards,
        ScoringService $scoring
    ): JsonResponse {
        $participant = $this->ensureParticipant($request, $challenge);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'media' => ['nullable', 'file'],
            'media_path' => ['required_without:media', 'string', 'max:2048'],
            'media_type' => ['required_with:media_path', Rule::in(['image', 'video'])],
            'duration_minutes' => ['nullable', 'integer', 'min:0'],
            'distance_km' => ['nullable', 'numeric', 'min:0'],
            'calories' => ['nullable', 'integer', 'min:0'],
            'steps' => ['nullable', 'integer', 'min:0'],
            'activity_type' => ['nullable', 'string', 'max:80'],
        ]);

        $media = $mediaSafety->prepare($validated, $request->user()->id, $request->file('media'));

        $checkIn = DB::transaction(function () use ($request, $challenge, $participant, $validated, $media, $awards, $scoring) {
            $checkIn = new FitnessCheckIn([
                ...collect($validated)->except(['media', 'media_path', 'media_type'])->all(),
                ...$media,
                'fitness_challenge_id' => $challenge->id,
                'user_id' => $request->user()->id,
                'fitness_team_id' => $participant->fitness_team_id,
                'score' => 0,
            ]);

            $checkIn->save();

            if ($checkIn->moderation_status === ModerationStatus::Approved->value) {
                $awards->award($checkIn, $scoring);
            }

            return $checkIn->refresh();
        });

        return response()->json(['data' => $checkIn], 201);
    }

    public function destroy(Request $request, FitnessCheckIn $checkIn): JsonResponse
    {
        abort_unless($checkIn->user_id === $request->user()->id, 403);

        DB::transaction(function () use ($checkIn) {
            app(CheckInAwardService::class)->revoke($checkIn);

            $checkIn->delete();
        });

        return response()->json(status: 204);
    }

    public function like(Request $request, FitnessCheckIn $checkIn): JsonResponse
    {
        $this->ensureParticipant($request, $checkIn->challenge);
        $this->ensureApproved($checkIn);

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
        $this->ensureApproved($checkIn);

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

    private function ensureApproved(FitnessCheckIn $checkIn): void
    {
        abort_unless($checkIn->moderation_status === ModerationStatus::Approved->value, 404);
    }
}
