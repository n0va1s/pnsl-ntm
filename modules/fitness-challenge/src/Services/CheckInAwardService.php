<?php

namespace Modules\FitnessChallenge\Services;

use Modules\FitnessChallenge\Enums\ModerationStatus;
use Modules\FitnessChallenge\Models\FitnessCheckIn;

class CheckInAwardService
{
    public function award(FitnessCheckIn $checkIn, ScoringService $scoring): FitnessCheckIn
    {
        if ($checkIn->score_awarded_at !== null) {
            return $checkIn;
        }

        $checkIn->score = $scoring->calculate($checkIn, $checkIn->challenge);
        $checkIn->moderation_status = ModerationStatus::Approved->value;
        $checkIn->score_awarded_at = now();
        $checkIn->save();

        $participant = $checkIn->challenge->participants()
            ->where('user_id', $checkIn->user_id)
            ->first();

        if ($participant) {
            $participant->increment('total_check_ins');
            $participant->increment('total_score', $checkIn->score);
        }

        if ($checkIn->team) {
            $checkIn->team->increment('total_score', $checkIn->score);
        }

        return $checkIn;
    }

    public function revoke(FitnessCheckIn $checkIn): void
    {
        if ($checkIn->score_awarded_at === null) {
            return;
        }

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

        $checkIn->score = 0;
        $checkIn->score_awarded_at = null;
        $checkIn->save();
    }
}
