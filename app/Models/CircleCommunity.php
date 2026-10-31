<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CircleCommunity extends Model
{
    use HasFactory;

    protected $table = 'circle_communities';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'type', // 'class', 'extracurricular', 'vocation', 'squad'
        'school_id',
        'classroom_id',
        'extracurricular_id',
        'avatar_path',
        'banner_path',
        'theme_color',
        'icon',
        'created_by_user_id',
        'is_official',
        'is_private',
        'members_count',
        'posts_count',
    ];

    protected $casts = [
        'is_official' => 'boolean',
        'is_private' => 'boolean',
        'members_count' => 'integer',
        'posts_count' => 'integer',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function extracurricular()
    {
        return $this->belongsTo(Extracurricular::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function members()
    {
        return $this->hasMany(CircleCommunityMember::class, 'community_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'circle_community_members', 'community_id', 'user_id')
            ->withPivot('role', 'joined_at')
            ->withTimestamps();
    }

    public function posts()
    {
        return $this->hasMany(CirclePost::class, 'community_id')->latest();
    }

    /**
     * Label Kategori & Icon
     */
    public function getTypeInfoAttribute(): array
    {
        return match ($this->type) {
            'class' => ['label' => 'Circle Kelas', 'icon' => '🏫', 'badge' => 'bg-sky-100 text-sky-800 border-sky-200'],
            'extracurricular' => ['label' => 'Circle Ekskul', 'icon' => '🏆', 'badge' => 'bg-amber-100 text-amber-800 border-amber-200'],
            'vocation' => ['label' => 'Rumpun Kejuruan', 'icon' => '🛠️', 'badge' => 'bg-purple-100 text-purple-800 border-purple-200'],
            default => ['label' => 'Squad & Komunitas', 'icon' => '🌟', 'badge' => 'bg-pink-100 text-pink-800 border-pink-200'],
        };
    }

    /**
     * URL Avatar Komunitas
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar_path) {
            return asset('storage/' . $this->avatar_path);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=fce7f3&color=be185d&bold=true&rounded=true';
    }
}
