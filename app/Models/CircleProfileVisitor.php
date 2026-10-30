<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CircleProfileVisitor extends Model
{
    use HasFactory;

    protected $fillable = [
        'circle_profile_id',
        'visitor_user_id',
        'visit_count',
        'last_visited_at',
    ];

    protected $casts = [
        'visit_count' => 'integer',
        'last_visited_at' => 'datetime',
    ];

    public function circleProfile()
    {
        return $this->belongsTo(CircleProfile::class, 'circle_profile_id');
    }

    public function visitor()
    {
        return $this->belongsTo(User::class, 'visitor_user_id');
    }
}
