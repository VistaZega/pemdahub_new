<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForumGroupMember extends Model
{
    use HasFactory;

    protected $table = 'forum_group_members';

    protected $fillable = [
        'group_id',
        'user_id',
        'role',
        'joined_at',
        'last_read_at',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'last_read_at' => 'datetime',
    ];

    public function group()
    {
        return $this->belongsTo(ForumGroup::class, 'group_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
