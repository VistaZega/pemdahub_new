<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CircleCommunityMember extends Model
{
    use HasFactory;

    protected $table = 'circle_community_members';

    protected $fillable = [
        'community_id',
        'user_id',
        'role', // 'leader', 'moderator', 'member'
        'joined_at',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
    ];

    public function community()
    {
        return $this->belongsTo(CircleCommunity::class, 'community_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
