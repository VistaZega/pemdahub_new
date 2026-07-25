<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LmsDiscussionReply extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'lms_discussion_replies';

    protected $fillable = [
        'discussion_id',
        'user_id',
        'parent_id',
        'content',
        'is_best_answer',
        'is_teacher_verified',
        'attachment_path',
    ];

    protected $casts = [
        'is_best_answer' => 'boolean',
        'is_teacher_verified' => 'boolean',
    ];

    public function discussion()
    {
        return $this->belongsTo(LmsDiscussion::class, 'discussion_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function parent()
    {
        return $this->belongsTo(LmsDiscussionReply::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(LmsDiscussionReply::class, 'parent_id');
    }

    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function isAuthor($userId): bool
    {
        return $this->user_id === $userId;
    }

    public function markAsBestAnswer()
    {
        // Unmark previous best answers in same discussion
        self::where('discussion_id', $this->discussion_id)
            ->where('is_best_answer', true)
            ->update(['is_best_answer' => false]);

        $this->update(['is_best_answer' => true]);
        $this->discussion->update(['is_resolved' => true]);
    }
}
