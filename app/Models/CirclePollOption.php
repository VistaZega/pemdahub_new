<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CirclePollOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'circle_poll_id',
        'option_text',
        'votes_count',
    ];

    protected $casts = [
        'votes_count' => 'integer',
    ];

    public function poll()
    {
        return $this->belongsTo(CirclePoll::class, 'circle_poll_id');
    }

    public function votes()
    {
        return $this->hasMany(CirclePollVote::class, 'circle_poll_option_id');
    }

    /**
     * Hitung persentase suara
     */
    public function percentage($totalVotes = null): int
    {
        if ($totalVotes === null) {
            $totalVotes = $this->poll->totalVotes();
        }

        if ($totalVotes <= 0) {
            return 0;
        }

        return (int) round(($this->votes_count / $totalVotes) * 100);
    }
}
