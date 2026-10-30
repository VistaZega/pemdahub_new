<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CirclePost extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'post_type',
        'tab_category',
        'title',
        'slug',
        'content',
        'mood_emoji',
        'mood_text',
        'location_tag',
        'tagged_user_ids',
        'school_id',
        'major_code',
        'visibility',
        'is_pinned',
        'expires_at',
        'likes_count',
        'comments_count',
        'views_count',
    ];

    protected $casts = [
        'tagged_user_ids' => 'array',
        'is_pinned' => 'boolean',
        'expires_at' => 'datetime',
        'likes_count' => 'integer',
        'comments_count' => 'integer',
        'views_count' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($post) {
            if (empty($post->slug) && !empty($post->title)) {
                $post->slug = Str::slug($post->title) . '-' . Str::random(6);
            } elseif (empty($post->slug)) {
                $post->slug = 'post-' . Str::random(10);
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function media()
    {
        return $this->hasMany(CirclePostMedia::class)->orderBy('order', 'asc');
    }

    public function reactions()
    {
        return $this->hasMany(CircleReaction::class);
    }

    public function comments()
    {
        return $this->hasMany(CircleComment::class)->whereNull('parent_id')->orderBy('created_at', 'asc');
    }

    public function skillEndorsements()
    {
        return $this->hasMany(CircleSkillEndorsement::class);
    }

    /**
     * Dapatkan daftar User yang di-tag
     */
    public function taggedUsers()
    {
        if (empty($this->tagged_user_ids)) {
            return collect();
        }
        return User::whereIn('id', $this->tagged_user_ids)->get();
    }

    /**
     * Scope untuk post aktif (Story yang belum expired atau post biasa)
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope untuk publik atau circle
     */
    public function scopePublicVisible($query)
    {
        return $query->where('visibility', 'public');
    }
}
