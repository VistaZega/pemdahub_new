<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class AlumniDirectory extends Model
{
    use HasFactory;

    protected $table = 'alumni_directories';

    protected $fillable = [
        'user_id',
        'full_name',
        'alias_name',
        'gender',
        'marital_status',
        'children_count',
        'address',
        'phone',
        'email',
        'occupation',
        'company_name',
        'school_id',
        'jurusan',
        'graduation_year',
        'last_class',
        'message',
        'photo_path',
        'is_approved',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'graduation_year' => 'integer',
    ];

    /**
     * Relationship: Directory entry belongs to a User (Login Account)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: Directory entry belongs to a School (Unit)
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Accessor for full photo URL.
     */
    public function getPhotoUrlAttribute()
    {
        if ($this->photo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->photo_path)) {
            return asset('storage/' . $this->photo_path);
        }

        if ($this->user && $this->user->avatar_url) {
            return $this->user->avatar_url;
        }
        
        // Default avatar fallback
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->full_name) . '&color=ffffff&background=6366f1';
    }
}
