<?php

namespace Modules\FitnessChallenge\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Modules\FitnessChallenge\Enums\ModerationStatus;
use Modules\FitnessChallenge\Models\FitnessCheckIn;
use Modules\FitnessChallenge\Services\CheckInAwardService;
use Modules\FitnessChallenge\Services\ScoringService;

class CheckInModerationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorizeModerator($request->user());

        $checkIns = FitnessCheckIn::query()
            ->where('moderation_status', ModerationStatus::Pending->value)
            ->with(['challenge:id,name', 'user:id,name,email'])
            ->latest()
            ->cursorPaginate(20);

        return response()->json($checkIns);
    }

    public function approve(
        Request $request,
        FitnessCheckIn $checkIn,
        CheckInAwardService $awards,
        ScoringService $scoring
    ): JsonResponse {
        $this->authorizeModerator($request->user());

        $checkIn = DB::transaction(function () use ($request, $checkIn, $awards, $scoring) {
            $checkIn->moderation_status = ModerationStatus::Approved->value;
            $checkIn->moderation_reason = null;
            $checkIn->moderated_by = $request->user()->id;
            $checkIn->moderated_at = now();
            $checkIn->save();

            return $awards->award($checkIn, $scoring)->refresh();
        });

        return response()->json(['data' => $checkIn]);
    }

    public function reject(Request $request, FitnessCheckIn $checkIn, CheckInAwardService $awards): JsonResponse
    {
        $this->authorizeModerator($request->user());

        $validated = $request->validate([
            'moderation_reason' => ['nullable', 'string', 'max:1000'],
            'status' => ['nullable', Rule::in([ModerationStatus::Rejected->value])],
        ]);

        $checkIn = DB::transaction(function () use ($request, $checkIn, $awards, $validated) {
            $awards->revoke($checkIn);

            $checkIn->moderation_status = ModerationStatus::Rejected->value;
            $checkIn->moderation_reason = $validated['moderation_reason'] ?? 'Prova recusada pela moderação.';
            $checkIn->moderated_by = $request->user()->id;
            $checkIn->moderated_at = now();
            $checkIn->save();

            return $checkIn->refresh();
        });

        return response()->json(['data' => $checkIn]);
    }

    private function authorizeModerator(User $user): void
    {
        abort_unless($user->isAdmin() || $user->isCoordenador(), 403);
    }
}
