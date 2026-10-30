<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CirclePollVote extends Model
{
    use HasFactory;

    protected $fillable = [
        'circle_poll_id',
        'circle_poll_option_id',
        'user_id',
        'guest_ip',
    ];

    public function poll()
    {
        return $this->belongsTo(CirclePoll::class, 'circle_poll_id');
    }

    public function option()
    {
        return $this->belongsTo(CirclePollOption::class, 'circle_poll_option_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
