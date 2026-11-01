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
        'type', // 'class', 'sanggar', 'extracurricular', 'vocation', 'cohort', 'alumni', 'teachers', 'squad'
        'school_id',
        'classroom_id',
        'extracurricular_id',
        'cohort_year',
        'major_code',
        'avatar_path',
        'banner_path',
        'theme_color',
        'icon',
        'created_by_user_id',
        'is_official',
        'is_private',
        'only_admin_can_post',
        'members_count',
        'posts_count',
        'metadata',
    ];

    protected $casts = [
        'cohort_year' => 'integer',
        'is_official' => 'boolean',
        'is_private' => 'boolean',
        'only_admin_can_post' => 'boolean',
        'members_count' => 'integer',
        'posts_count' => 'integer',
        'metadata' => 'array',
    ];

    public const TYPES = [
        'class' => [
            'label' => 'Grup Kelas',
            'short' => 'Kelas',
            'icon' => '🏫',
            'fa' => 'fas fa-school',
            'badge' => 'bg-sky-100 text-sky-900 border-sky-300',
            'gradient' => 'from-sky-50 via-blue-50 to-indigo-50',
            'accent' => 'sky',
        ],
        'sanggar' => [
            'label' => 'Grup Sanggar & Seni',
            'short' => 'Sanggar',
            'icon' => '🎭',
            'fa' => 'fas fa-masks-theater',
            'badge' => 'bg-pink-100 text-pink-900 border-pink-300',
            'gradient' => 'from-pink-50 via-rose-50 to-amber-50',
            'accent' => 'pink',
        ],
        'extracurricular' => [
            'label' => 'Grup Ekstrakurikuler',
            'short' => 'Ekskul',
            'icon' => '🏆',
            'fa' => 'fas fa-trophy',
            'badge' => 'bg-amber-100 text-amber-900 border-amber-300',
            'gradient' => 'from-amber-50 via-yellow-50 to-orange-50',
            'accent' => 'amber',
        ],
        'vocation' => [
            'label' => 'Rumpun Kejuruan SMK',
            'short' => 'Kejuruan',
            'icon' => '🛠️',
            'fa' => 'fas fa-screwdriver-wrench',
            'badge' => 'bg-purple-100 text-purple-900 border-purple-300',
            'gradient' => 'from-purple-50 via-fuchsia-50 to-indigo-50',
            'accent' => 'purple',
        ],
        'cohort' => [
            'label' => 'Grup Angkatan',
            'short' => 'Angkatan',
            'icon' => '📅',
            'fa' => 'fas fa-calendar-check',
            'badge' => 'bg-emerald-100 text-emerald-900 border-emerald-300',
            'gradient' => 'from-emerald-50 via-teal-50 to-cyan-50',
            'accent' => 'emerald',
        ],
        'alumni' => [
            'label' => 'Ikatan Alumni (IKA)',
            'short' => 'Alumni',
            'icon' => '🎓',
            'fa' => 'fas fa-user-graduate',
            'badge' => 'bg-indigo-100 text-indigo-900 border-indigo-300',
            'gradient' => 'from-indigo-50 via-sky-50 to-purple-50',
            'accent' => 'indigo',
        ],
        'teachers' => [
            'label' => 'Ruang Guru & Mentor',
            'short' => 'Guru',
            'icon' => '👨‍🏫',
            'fa' => 'fas fa-chalkboard-user',
            'badge' => 'bg-teal-100 text-teal-900 border-teal-300',
            'gradient' => 'from-teal-50 via-emerald-50 to-sky-50',
            'accent' => 'teal',
        ],
        'squad' => [
            'label' => 'Squad & Komunitas',
            'short' => 'Squad',
            'icon' => '🌟',
            'fa' => 'fas fa-users-rays',
            'badge' => 'bg-rose-100 text-rose-900 border-rose-300',
            'gradient' => 'from-rose-50 via-pink-50 to-amber-50',
            'accent' => 'rose',
        ],
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
            ->withPivot('role', 'title_badge', 'joined_at')
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
        return self::TYPES[$this->type] ?? self::TYPES['squad'];
    }

    /**
     * Display Icon (Custom atau Default per Tipe)
     */
    public function getDisplayIconAttribute(): string
    {
        if (!empty($this->icon) && !str_starts_with(trim($this->icon), 'fa-')) {
            return $this->icon;
        }
        return $this->type_info['icon'] ?? '🌟';
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
