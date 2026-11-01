<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CircleRiddleGuess extends Model
{
    use HasFactory;

    protected $table = 'circle_riddle_guesses';

    protected $fillable = [
        'circle_post_id',
        'user_id',
        'guess_text',
        'is_correct',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(CirclePost::class, 'circle_post_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
