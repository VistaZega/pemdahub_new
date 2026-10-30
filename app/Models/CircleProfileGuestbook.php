<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CircleProfileGuestbook extends Model
{
    use HasFactory;

    protected $fillable = [
        'circle_profile_id',
        'sender_user_id',
        'sticker_code',
        'sticker_emoji',
        'sticker_label',
        'message',
        'is_pinned',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
    ];

    public function circleProfile()
    {
        return $this->belongsTo(CircleProfile::class, 'circle_profile_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }
}
