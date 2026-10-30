<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CircleComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'circle_post_id',
        'parent_id',
        'user_id',
        'author_name',
        'is_guest',
        'comment_text',
        'is_flagged',
    ];

    protected $casts = [
        'is_guest' => 'boolean',
        'is_flagged' => 'boolean',
    ];

    public function post()
    {
        return $this->belongsTo(CirclePost::class, 'circle_post_id');
    }

    public function parent()
    {
        return $this->belongsTo(CircleComment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(CircleComment::class, 'parent_id')->orderBy('created_at', 'asc');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
