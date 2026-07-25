<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LmsDiscussion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'lms_discussions';

    protected $fillable = [
        'course_id',
        'user_id',
        'title',
        'content',
        'type',
        'is_pinned',
        'is_locked',
        'is_resolved',
        'replies_count',
        'last_reply_at',
        'attachment_path',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'is_locked' => 'boolean',
        'is_resolved' => 'boolean',
        'last_reply_at' => 'datetime',
    ];

    protected const TYPES = [
        'discussion' => 'Diskusi',
        'question' => 'Pertanyaan',
        'announcement' => 'Pengumuman',
    ];

    public function course()
    {
        return $this->belongsTo(LmsCourse::class, 'course_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function replies()
    {
        return $this->hasMany(LmsDiscussionReply::class, 'discussion_id');
    }

    public function topLevelReplies()
    {
        return $this->hasMany(LmsDiscussionReply::class, 'discussion_id')->whereNull('parent_id');
    }

    public function latestReply()
    {
        return $this->hasOne(LmsDiscussionReply::class, 'discussion_id')->latestOfMany();
    }

    public function bestAnswer()
    {
        return $this->hasOne(LmsDiscussionReply::class, 'discussion_id')->where('is_best_answer', true);
    }

    public function getTypeLabelAttribute()
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getTypeIconAttribute()
    {
        return match ($this->type) {
            'discussion' => 'fa-comments',
            'question' => 'fa-question-circle',
            'announcement' => 'fa-bullhorn',
            default => 'fa-comment',
        };
    }

    public function getTypeColorAttribute()
    {
        return match ($this->type) {
            'discussion' => 'blue',
            'question' => 'purple',
            'announcement' => 'orange',
            default => 'gray',
        };
    }

    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    public function scopeNotLocked($query)
    {
        return $query->where('is_locked', false);
    }

    public function incrementRepliesCount()
    {
        $this->increment('replies_count');
        $this->update(['last_reply_at' => now()]);
    }

    public function decrementRepliesCount()
    {
        $this->decrement('replies_count');
    }
}
