<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CirclePostMedia extends Model
{
    use HasFactory;

    protected $fillable = [
        'circle_post_id',
        'media_type',
        'file_path',
        'thumbnail_path',
        'secondary_path',
        'aspect_ratio',
        'metadata',
        'order',
    ];

    protected $casts = [
        'metadata' => 'array',
        'order' => 'integer',
    ];

    public function post()
    {
        return $this->belongsTo(CirclePost::class, 'circle_post_id');
    }

    /**
     * Dapatkan URL lengkap file
     */
    public function getUrlAttribute(): string
    {
        if (str_starts_with($this->file_path, 'http://') || str_starts_with($this->file_path, 'https://')) {
            return $this->file_path;
        }
        return asset('storage/' . ltrim($this->file_path, '/'));
    }

    public function getSecondaryUrlAttribute(): ?string
    {
        if (!$this->secondary_path) {
            return null;
        }
        if (str_starts_with($this->secondary_path, 'http://') || str_starts_with($this->secondary_path, 'https://')) {
            return $this->secondary_path;
        }
        return asset('storage/' . ltrim($this->secondary_path, '/'));
    }

    public function getThumbnailUrlAttribute(): string
    {
        if ($this->thumbnail_path) {
            if (str_starts_with($this->thumbnail_path, 'http://') || str_starts_with($this->thumbnail_path, 'https://')) {
                return $this->thumbnail_path;
            }
            return asset('storage/' . ltrim($this->thumbnail_path, '/'));
        }
        return $this->url;
    }
}
