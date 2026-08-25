<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Extracurricular extends Model
{
    use HasFactory;

    protected $table = 'extracurriculars';

    protected $fillable = [
        'school_id',
        'scope',
        'name',
        'slug',
        'category',
        'description',
        'icon',
        'color',
        'cover_image',
        'schedule_day_time',
        'location',
        'manager_name',
        'advisor_teacher_id',
        'advisor_name',
        'leader_student_id',
        'secretary_student_id',
        'treasurer_student_id',
        'available_sections',
        'forum_group_id',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'available_sections' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name) . '-' . Str::random(5);
            }
        });
    }

    /**
     * Relasi ke Unit Sekolah
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Pembina Ekstrakurikuler (Guru / Pegawai)
     */
    public function advisor()
    {
        return $this->belongsTo(Teacher::class, 'advisor_teacher_id');
    }

    /**
     * Ketua / Koordinator Siswa
     */
    public function leader()
    {
        return $this->belongsTo(Student::class, 'leader_student_id');
    }

    /**
     * Sekretaris Siswa
     */
    public function secretary()
    {
        return $this->belongsTo(Student::class, 'secretary_student_id');
    }

    /**
     * Bendahara Siswa
     */
    public function treasurer()
    {
        return $this->belongsTo(Student::class, 'treasurer_student_id');
    }

    /**
     * Seluruh Anggota Ekskul
     */
    public function members()
    {
        return $this->hasMany(ExtracurricularMember::class, 'extracurricular_id');
    }

    /**
     * Anggota Aktif & Disetujui
     */
    public function activeMembers()
    {
        return $this->hasMany(ExtracurricularMember::class, 'extracurricular_id')
            ->where('status', 'approved');
    }

    /**
     * Siswa yang Terdaftar
     */
    public function students()
    {
        return $this->belongsToMany(Student::class, 'extracurricular_members', 'extracurricular_id', 'student_id')
            ->withPivot(['role', 'status', 'joined_date', 'notes', 'points_awarded'])
            ->withTimestamps();
    }

    /**
     * Log Kegiatan Ekskul
     */
    public function activities()
    {
        return $this->hasMany(ExtracurricularActivity::class, 'extracurricular_id')
            ->orderByDesc('activity_date');
    }

    /**
     * Kanal / Grup Pembda Space
     */
    public function forumGroup()
    {
        return $this->belongsTo(ForumGroup::class, 'forum_group_id');
    }

    public function isFoundationLevel(): bool
    {
        return $this->scope === 'yayasan' || empty($this->school_id);
    }

    /**
     * Get section list (default for marching band or custom)
     */
    public function getSectionListAttribute(): array
    {
        if (!empty($this->available_sections) && is_array($this->available_sections)) {
            return $this->available_sections;
        }

        if ($this->category === 'marching_band' || str_contains(strtolower($this->name), 'marching')) {
            return [
                'Mayoret' => '👑 Mayoret / Gitapati',
                'Tenor' => '🥁 Tenor / Triotom',
                'Brass' => '🎺 Brass (Terompet/Trombone/Tuba)',
                'Colour Guard' => '🚩 Colour Guard (Bendera & Visual)',
                'Bass' => '🥁 Bass Drum',
                'Bellyra' => '🔔 Bellyra',
                'Marching Bells' => '🎶 Marching Bells / Pit',
            ];
        }

        return [];
    }

    /**
     * Category Label & Badge Helper
     */
    public function getCategoryLabelAttribute(): string
    {
        return match($this->category) {
            'marching_band' => 'Marching Band Yayasan',
            'pramuka' => 'Gerakan Pramuka',
            'paskibraka' => 'Paskibraka',
            'seni_budaya' => 'Seni & Budaya',
            'olahraga' => 'Olahraga & Atletik',
            'sains_it' => 'Sains & Teknologi IT',
            'keagamaan' => 'Rohkris / Rohis',
            'jurnalistik' => 'Jurnalistik & Literasi',
            default => 'Pengembangan Diri'
        };
    }

    /**
     * Default Icon Helper
     */
    public function getDisplayIconAttribute(): string
    {
        if (!empty($this->icon)) {
            return $this->icon;
        }

        return match($this->category) {
            'marching_band' => '🥁',
            'pramuka' => '⚜️',
            'paskibraka' => '🇮🇩',
            'seni_budaya' => '🎭',
            'olahraga' => '⚽',
            'sains_it' => '💻',
            'keagamaan' => '✝️',
            'jurnalistik' => '📰',
            default => '🎨'
        };
    }
}
