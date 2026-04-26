<?php

namespace Modules\FitnessChallenge\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FitnessCheckIn extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'fitness_check_ins';

    protected $fillable = [
        'fitness_challenge_id',
        'user_id',
        'fitness_team_id',
        'title',
        'description',
        'media_path',
        'media_type',
        'duration_minutes',
        'distance_km',
        'calories',
        'steps',
        'activity_type',
        'score',
        'moderation_status',
        'moderation_reason',
        'moderated_by',
        'moderated_at',
        'score_awarded_at',
    ];

    protected $casts = [
        'distance_km' => 'float',
        'score' => 'float',
        'moderated_at' => 'datetime',
        'score_awarded_at' => 'datetime',
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

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(FitnessComment::class, 'fitness_check_in_id');
    }

    public function likes(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'fitness_check_in_likes', 'fitness_check_in_id', 'user_id')
            ->withTimestamps();
    }
}
