<?php

namespace Modules\FitnessChallenge\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FitnessTeam extends Model
{
    use HasFactory;

    protected $table = 'fitness_teams';

    protected $fillable = [
        'fitness_challenge_id',
        'name',
        'avatar_path',
        'total_score',
    ];

    protected $casts = [
        'total_score' => 'float',
    ];

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(FitnessChallenge::class, 'fitness_challenge_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(FitnessParticipant::class, 'fitness_team_id');
    }
}
