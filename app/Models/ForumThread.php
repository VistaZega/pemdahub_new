<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ForumThread extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'content',
        'category',
        'image_path',
        'attachment_path',
        'attachment_name',
        'reference_type',
        'reference_id',
        'status',
        'game_name',
        'game_room_code',
        'file_category',
        'charity_target_amount',
        'charity_current_amount',
        'charity_target_volunteers',
        'views_count',
        'is_pinned',
        'is_locked',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'is_locked' => 'boolean',
        'charity_target_amount' => 'decimal:2',
        'charity_current_amount' => 'decimal:2',
        'charity_target_volunteers' => 'integer',
        'views_count' => 'integer',
    ];

    public const CATEGORIES = [
        'diskusi' => '💬 Wall Diskusi Publik',
        'info' => '📢 Wall Pengumuman',
        'ekskul_showcase' => '🏆 Ekskul & Prestasi',
        'ekskul_recruit' => '📢 Open Recruitment Ekskul',
        'tanya_jawab' => '❓ Tanya Jawab',
        'sharing' => '📁 Bank File',
        'art_gallery' => '🎨 Karya Seni',
        'talent' => '🎵 Musik & Bakat',
        'performance' => '🏆 Hall of Fame',
        'gaming' => '🎮 Gaming & Mabar',
        'trending' => '🔥 Trending & Viral',
        'project_idea' => '💡 Ide Proyek',
        'committee' => '👥 Rekrut Panitia',
        'charity' => '❤️ Aksi Sosial',
    ];

    public const CHANNEL_GROUPS = [
        '🧱 WALL OBROLAN' => ['diskusi', 'info'],
        '🏆 EKSTRAKURIKULER' => ['ekskul_showcase', 'ekskul_recruit'],
        '📚 AKADEMIK' => ['tanya_jawab', 'sharing'],
        '🎨 SHOWCASE' => ['art_gallery', 'talent', 'performance'],
        '🎮 HANGOUT' => ['gaming', 'trending'],
        '🤝 KOLABORASI' => ['project_idea', 'committee', 'charity'],
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function group()
    {
        return $this->belongsTo(ForumGroup::class, 'group_id');
    }

    public function replies()
    {
        return $this->hasMany(ForumReply::class)->orderBy('created_at', 'asc');
    }

    public function likes()
    {
        return $this->hasMany(ForumLike::class);
    }

    public function members()
    {
        return $this->hasMany(ForumMember::class);
    }

    public function approvedMembers()
    {
        return $this->hasMany(ForumMember::class)->where('status', 'approved');
    }

    public function isLikedBy(User $user): bool
    {
        return $this->likes()->where('user_id', $user->id)->exists();
    }

    public function reactions()
    {
        return $this->hasMany(ForumReaction::class);
    }

    public function poll()
    {
        return $this->hasOne(ForumPoll::class);
    }

    public function getReactionCounts(): array
    {
        $raw = $this->reactions()->select('emoji', DB::raw('count(*) as count'))
            ->groupBy('emoji')->pluck('count', 'emoji')->toArray();

        $mapped = [];
        foreach ($raw as $alias => $count) {
            $emoji = ForumReaction::getEmoji($alias);
            $mapped[$emoji] = $count;
        }
        return $mapped;
    }

    public function hasReacted(User $user, string $emoji): bool
    {
        $alias = ForumReaction::getAlias($emoji);
        return $this->reactions()->where('user_id', $user->id)->where('emoji', $alias)->exists();
    }

    public function reference()
    {
        return $this->morphTo('reference', 'reference_type', 'reference_id');
    }

    // Scopes
    public function scopePinnedFirst($query)
    {
        return $query->orderBy('is_pinned', 'desc')->orderBy('created_at', 'desc');
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('content', 'like', "%{$search}%");
        });
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    public function hasAcceptedReply(): bool
    {
        return $this->replies()->where('is_accepted', true)->exists();
    }

    public function getFileExtensionAttribute(): string
    {
        if (!$this->attachment_path && !$this->attachment_name) return '';
        $filename = $this->attachment_name ?: $this->attachment_path;
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }
}
