<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LmsMaterial extends Model
{
    use HasFactory, SoftDeletes;

    public $timestamps = true;

    protected $table = 'lms_materials';

    protected $fillable = [
        'course_id',
        'module_id',
        'title',
        'content',
        'material_type',
        'file_path',
        'file_url',
        'file_size',
        'order_number',
        'is_published',
        'prerequisite_material_id',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'order_number' => 'integer',
        'prerequisite_material_id' => 'integer',
    ];

    protected const CONTENT_TYPES = [
        'pdf' => 'PDF',
        'document' => 'Dokumen',
        'video' => 'Video',
        'text' => 'Teks',
        'image' => 'Gambar',
        'link' => 'Link Eksternal',
        'interactive' => 'Interaktif / Game (Embed)',
    ];

    /**
     * Relationship: Material belongs to Course (direct in real DB)
     */
    public function course()
    {
        return $this->belongsTo(LmsCourse::class, 'course_id');
    }

    /**
     * Relationship: Material belongs to Module (optional)
     */
    public function module()
    {
        return $this->belongsTo(LmsModule::class, 'module_id');
    }

    /**
     * Accessor: content_type returns material_type for backward compat
     */
    public function getContentTypeAttribute()
    {
        return $this->material_type;
    }

    /**
     * Accessor: content_url returns file_url for backward compat
     */
    public function getContentUrlAttribute()
    {
        return $this->file_url;
    }

    /**
     * Accessor: sequence returns order_number for backward compat
     */
    public function getSequenceAttribute()
    {
        return $this->order_number;
    }

    /**
     * Accessor: is_active returns is_published for backward compat
     */
    public function getIsActiveAttribute()
    {
        return $this->is_published;
    }

    /**
     * Get content type label
     */
    public function getContentTypeLabel()
    {
        return self::CONTENT_TYPES[$this->material_type] ?? $this->material_type;
    }

    /**
     * Get accessible URL for file (Asset / Public Storage or Direct Stream Route)
     */
    public function getFileAssetUrl()
    {
        if (\Illuminate\Support\Facades\Route::has('mobile.lms.material.stream') && !empty($this->file_path)) {
            return route('mobile.lms.material.stream', $this->id);
        }

        $path = $this->file_path ?: $this->file_url;
        if (!$path) return '#';

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, 'storage/')) {
            return asset($path);
        }

        return asset('storage/' . ltrim($path, '/'));
    }

    /**
     * Get direct download URL for file
     */
    public function getFileDownloadUrl()
    {
        if (\Illuminate\Support\Facades\Route::has('mobile.lms.material.download') && !empty($this->file_path)) {
            return route('mobile.lms.material.download', $this->id);
        }

        return $this->getFileAssetUrl();
    }

    /**
     * Check if the physical file exists in storage disk
     */
    public function fileExists(): bool
    {
        if (empty($this->file_path)) {
            return false;
        }

        $cleanPath = ltrim(str_replace('storage/', '', $this->file_path), '/');

        return \Illuminate\Support\Facades\Storage::disk('public')->exists($cleanPath)
            || \Illuminate\Support\Facades\Storage::exists($cleanPath);
    }

    /**
     * Scope: Get active/published materials
     */
    public function scopeActive($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope: Order by sequence
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order_number', 'asc');
    }

    public function progress()
    {
        return $this->hasMany(LmsMaterialProgress::class, 'material_id');
    }

    public function getProgressForStudent($studentId)
    {
        return $this->progress()->where('student_id', $studentId)->first();
    }

    /**
     * Check if the material is a YouTube video.
     */
    public function isYouTubeVideo()
    {
        $url = $this->file_url ?: ($this->file_path ?: $this->content);
        if (!$url) {
            return false;
        }
        return preg_match('/(youtube\.com|youtu\.be)/i', $url) === 1;
    }

    /**
     * Get the YouTube video embed URL.
     */
    public function getVideoEmbedUrl()
    {
        $url = $this->file_url ?: ($this->file_path ?: $this->content);
        if (!$url) {
            return '';
        }

        // Handle youtu.be/VIDEO_ID
        if (preg_match('/youtu\.be\/([a-zA-Z0-9_-]+)/i', $url, $matches)) {
            return 'https://www.youtube.com/embed/' . $matches[1];
        }

        // Handle youtube.com/watch?v=VIDEO_ID or youtube.com/embed/VIDEO_ID
        if (preg_match('/(?:v=|\/embed\/|&v=)([a-zA-Z0-9_-]+)/i', $url, $matches)) {
            return 'https://www.youtube.com/embed/' . $matches[1];
        }

        return $url;
    }

    /**
     * Check if the material is a direct video link or file.
     */
    public function isDirectVideo()
    {
        $url = $this->file_url ?? $this->file_path;
        if (!$url) {
            return false;
        }
        return preg_match('/\.(mp4|webm|ogg|avi|mov|mkv)$/i', $url) === 1;
    }

    public function prerequisite()
    {
        return $this->belongsTo(LmsMaterial::class, 'prerequisite_material_id');
    }

    public function notes()
    {
        return $this->hasMany(LmsMaterialNote::class, 'material_id');
    }
}
