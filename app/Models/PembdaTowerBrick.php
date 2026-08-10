<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembdaTowerBrick extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'school_id',
        'message',
        'color',
        'brick_number',
        'likes_count',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function likes()
    {
        return $this->hasMany(PembdaTowerBrickLike::class, 'brick_id');
    }

    public function isLikedBy(?User $user)
    {
        if (!$user) return false;
        return $this->likes()->where('user_id', $user->id)->exists();
    }
}
