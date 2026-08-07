<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlumniForum extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'school_id',
        'category',
        'title',
        'content',
        'image_path',
        'views_count',
    ];

    public const CATEGORIES = [
        'umum' => '💬 Cuap-cuap & Ruang Umum',
        'angkatan' => '🎓 Diskusi Per Angkatan',
        'ide_kreatif' => '💡 Ide & Gagasan Alumni',
        'kontribusi' => '🤝 Kontribusi & Alumni Care',
        'lowongan' => '📢 Lapangan Kerja & Karir',
        'kenangan' => '🕰️ Kenangan & Nostalgia',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function replies()
    {
        return $this->hasMany(AlumniForumReply::class);
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }
}
