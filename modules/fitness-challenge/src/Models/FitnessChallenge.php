<?php

namespace Modules\FitnessChallenge\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\FitnessChallenge\Enums\ScoringType;

class FitnessChallenge extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'fitness_challenges';

    protected $fillable = [
        'created_by',
        'name',
        'description',
        'starts_at',
        'ends_at',
        'scoring_type',
        'hustle_points',
        'is_team_challenge',
        'cover_image_path',
        'invite_code',
        'status',
        'max_participants',
    ];

    protected $casts = [
        'starts_at' => 'date',
        'ends_at' => 'date',
        'hustle_points' => 'array',
        'is_team_challenge' => 'boolean',
        'scoring_type' => ScoringType::class,
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(FitnessParticipant::class, 'fitness_challenge_id');
    }

    public function teams(): HasMany
    {
        return $this->hasMany(FitnessTeam::class, 'fitness_challenge_id');
    }

    public function checkIns(): HasMany
    {
        return $this->hasMany(FitnessCheckIn::class, 'fitness_challenge_id');
    }
}
