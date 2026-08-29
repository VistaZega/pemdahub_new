<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class News extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'slug', 'excerpt', 'content', 'category',
        'image', 'icon', 'gradient_from', 'gradient_to',
        'is_published', 'published_at', 'author_id',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    // ── Relationships ──
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    // ── Scopes ──
    public function scopePublished($query)
    {
        return $query->where('is_published', true)
                     ->where(function($q) {
                         $q->whereNull('published_at')
                           ->orWhere('published_at', '<=', now());
                     });
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    // ── Accessors ──
    public function getFormattedDateAttribute()
    {
        $date = $this->published_at ?? $this->created_at;
        return $date ? $date->translatedFormat('d F Y') : '-';
    }

    public function getImageUrlAttribute()
    {
        if ($this->image) {
            if (\Illuminate\Support\Str::startsWith($this->image, 'images/')) {
                return asset($this->image);
            }
            return asset('storage/' . $this->image);
        }
        return null;
    }

    public function getCategoryLabelAttribute()
    {
        return match ($this->category) {
            'prestasi'    => 'Prestasi',
            'kegiatan'    => 'Kegiatan',
            'kerjasama'   => 'Kerjasama',
            'pengumuman'  => 'Pengumuman',
            default       => ucfirst($this->category),
        };
    }

    public function getCategoryColorAttribute()
    {
        return match ($this->category) {
            'prestasi'    => 'amber',
            'kegiatan'    => 'emerald',
            'kerjasama'   => 'blue',
            'pengumuman'  => 'violet',
            default       => 'gray',
        };
    }

    // ── Boot ──
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($news) {
            if (empty($news->slug)) {
                $news->slug = Str::slug($news->title);
                // Ensure unique
                $count = static::where('slug', 'like', $news->slug . '%')->count();
                if ($count > 0) {
                    $news->slug .= '-' . ($count + 1);
                }
            }
        });
    }
}
