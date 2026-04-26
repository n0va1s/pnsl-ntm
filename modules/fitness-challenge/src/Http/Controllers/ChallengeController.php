<?php

namespace Modules\FitnessChallenge\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Modules\FitnessChallenge\Enums\ScoringType;
use Modules\FitnessChallenge\Models\FitnessChallenge;
use Modules\FitnessChallenge\Models\FitnessParticipant;

class ChallengeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $challenges = FitnessChallenge::query()
            ->where('created_by', $userId)
            ->orWhereHas('participants', fn ($query) => $query->where('user_id', $userId))
            ->withCount(['participants', 'checkIns'])
            ->latest()
            ->get();

        return response()->json(['data' => $challenges]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after_or_equal:starts_at'],
            'scoring_type' => ['required', Rule::in(array_keys(ScoringType::options()))],
            'hustle_points' => ['nullable', 'array'],
            'is_team_challenge' => ['boolean'],
            'cover_image_path' => ['nullable', 'string', 'max:2048'],
            'max_participants' => ['nullable', 'integer', 'min:1'],
        ]);

        $challenge = DB::transaction(function () use ($request, $validated) {
            $challenge = FitnessChallenge::create([
                ...$validated,
                'created_by' => $request->user()->id,
                'invite_code' => $this->generateInviteCode(),
                'status' => $this->statusForDates($validated['starts_at'], $validated['ends_at']),
                'is_team_challenge' => (bool) ($validated['is_team_challenge'] ?? false),
            ]);

            FitnessParticipant::create([
                'fitness_challenge_id' => $challenge->id,
                'user_id' => $request->user()->id,
                'joined_at' => now(),
            ]);

            return $challenge;
        });

        return response()->json(['data' => $challenge->loadCount('participants')], 201);
    }

    public function show(Request $request, FitnessChallenge $challenge): JsonResponse
    {
        $this->ensureParticipantOrCreator($request, $challenge);

        return response()->json([
            'data' => $challenge->loadCount(['participants', 'checkIns'])->load('creator:id,name,email'),
        ]);
    }

    public function update(Request $request, FitnessChallenge $challenge): JsonResponse
    {
        $this->ensureCreator($request, $challenge);

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'starts_at' => ['sometimes', 'required', 'date'],
            'ends_at' => ['sometimes', 'required', 'date', 'after_or_equal:starts_at'],
            'scoring_type' => ['sometimes', 'required', Rule::in(array_keys(ScoringType::options()))],
            'hustle_points' => ['nullable', 'array'],
            'is_team_challenge' => ['boolean'],
            'cover_image_path' => ['nullable', 'string', 'max:2048'],
            'max_participants' => ['nullable', 'integer', 'min:1'],
        ]);

        $challenge->update($validated);

        return response()->json(['data' => $challenge->refresh()]);
    }

    public function destroy(Request $request, FitnessChallenge $challenge): JsonResponse
    {
        $this->ensureCreator($request, $challenge);
        $challenge->delete();

        return response()->json(status: 204);
    }

    public function join(Request $request, string $inviteCode): JsonResponse
    {
        $challenge = FitnessChallenge::where('invite_code', Str::upper($inviteCode))->firstOrFail();

        if ($challenge->max_participants && $challenge->participants()->count() >= $challenge->max_participants) {
            return response()->json(['message' => 'Desafio lotado.'], 422);
        }

        $participant = FitnessParticipant::firstOrCreate([
            'fitness_challenge_id' => $challenge->id,
            'user_id' => $request->user()->id,
        ], [
            'joined_at' => now(),
        ]);

        return response()->json(['data' => $participant], $participant->wasRecentlyCreated ? 201 : 200);
    }

    public function leave(Request $request, FitnessChallenge $challenge): JsonResponse
    {
        if ($challenge->created_by === $request->user()->id) {
            return response()->json(['message' => 'O criador nao pode sair do proprio desafio.'], 422);
        }

        $challenge->participants()
            ->where('user_id', $request->user()->id)
            ->delete();

        return response()->json(status: 204);
    }

    private function generateInviteCode(): string
    {
        do {
            $code = Str::upper(Str::random(8));
        } while (FitnessChallenge::where('invite_code', $code)->exists());

        return $code;
    }

    private function statusForDates(string $startsAt, string $endsAt): string
    {
        $today = now()->toDateString();

        if ($startsAt > $today) {
            return 'upcoming';
        }

        if ($endsAt < $today) {
            return 'finished';
        }

        return 'active';
    }

    private function ensureCreator(Request $request, FitnessChallenge $challenge): void
    {
        abort_unless($challenge->created_by === $request->user()->id, 403);
    }

    private function ensureParticipantOrCreator(Request $request, FitnessChallenge $challenge): void
    {
        abort_unless(
            $challenge->created_by === $request->user()->id
                || $challenge->participants()->where('user_id', $request->user()->id)->exists(),
            403
        );
    }
}
