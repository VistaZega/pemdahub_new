<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CirclePoll extends Model
{
    use HasFactory;

    protected $fillable = [
        'circle_post_id',
        'question',
        'closes_at',
    ];

    protected $casts = [
        'closes_at' => 'datetime',
    ];

    public function post()
    {
        return $this->belongsTo(CirclePost::class, 'circle_post_id');
    }

    public function options()
    {
        return $this->hasMany(CirclePollOption::class, 'circle_poll_id');
    }

    public function votes()
    {
        return $this->hasMany(CirclePollVote::class, 'circle_poll_id');
    }

    public function totalVotes(): int
    {
        return (int) $this->options->sum('votes_count');
    }

    /**
     * Cek apakah user atau IP saat ini sudah memilih
     */
    public function hasVoted($userId = null, $ip = null): ?int
    {
        if ($userId) {
            $vote = $this->votes->where('user_id', $userId)->first();
            return $vote ? $vote->circle_poll_option_id : null;
        }

        if ($ip) {
            $vote = $this->votes->where('guest_ip', $ip)->first();
            return $vote ? $vote->circle_poll_option_id : null;
        }

        return null;
    }
}
