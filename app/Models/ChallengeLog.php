<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChallengeLog extends Model
{
    use HasFactory;

    protected $table = 'challenge_logs';

    protected $fillable = [
        'user_id',
        'game_key',
        'moves',
        'lives_left',
        'stars',
        'points_awarded',
        'seconds_taken',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
