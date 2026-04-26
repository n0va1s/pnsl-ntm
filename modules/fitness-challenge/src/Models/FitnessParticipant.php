<?php

namespace Modules\FitnessChallenge\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FitnessParticipant extends Model
{
    use HasFactory;

    protected $table = 'fitness_participants';

    protected $fillable = [
        'fitness_challenge_id',
        'user_id',
        'fitness_team_id',
        'total_score',
        'total_check_ins',
        'joined_at',
    ];

    protected $casts = [
        'total_score' => 'float',
        'joined_at' => 'datetime',
    ];

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(FitnessChallenge::class, 'fitness_challenge_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(FitnessTeam::class, 'fitness_team_id');
    }
}
