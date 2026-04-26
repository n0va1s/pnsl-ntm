<?php

namespace Modules\FitnessChallenge\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FitnessComment extends Model
{
    use HasFactory;

    protected $table = 'fitness_comments';

    protected $fillable = [
        'fitness_check_in_id',
        'user_id',
        'body',
    ];

    public function checkIn(): BelongsTo
    {
        return $this->belongsTo(FitnessCheckIn::class, 'fitness_check_in_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
