<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CircleReaction extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'circle_post_id',
        'user_id',
        'guest_ip',
        'guest_name',
        'reaction_type',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function post()
    {
        return $this->belongsTo(CirclePost::class, 'circle_post_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
