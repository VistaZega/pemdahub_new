<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeachingAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'subject_id',
        'classroom_id',
        'academic_year_id',
        'semester_id',
        'hours_per_week',
        'is_main_teacher',
        'is_active',
        'teaching_load_type',
        'hourly_rate',
        'teaching_allowance',
        'sk_reference',
        'group_code',
        'block_type',
    ];

    protected $casts = [
        'hours_per_week' => 'integer',
        'is_main_teacher' => 'boolean',
        'is_active' => 'boolean',
        'hourly_rate' => 'decimal:2',
        'teaching_allowance' => 'decimal:2',
    ];

    public const LOAD_TYPES = [
        'wajib' => 'Wajib',
        'tambahan' => 'Tambahan',
        'pengganti' => 'Pengganti',
    ];

    public const BLOCK_TYPES = [
        'none' => 'Reguler (Semua Siswa)',
        'parallel' => 'Paralel / Agama (Multi Mapel Concurrent)',
        'all' => 'Sistem Blok SMK - Kelompok A',
        'split' => 'Sistem Blok SMK - Kelompok B',
    ];

    // Relationships
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     * Relationship: Jadwal yang mereferensikan penugasan ini
     */
    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    // Helpers
    public function isWajib(): bool
    {
        return $this->teaching_load_type === 'wajib';
    }

    public function getLoadTypeLabelAttribute(): string
    {
        return self::LOAD_TYPES[$this->teaching_load_type] ?? $this->teaching_load_type;
    }

    public function getBlockTypeLabelAttribute(): string
    {
        return self::BLOCK_TYPES[$this->block_type] ?? $this->block_type;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    public function scopeByAcademicYear($query, $yearId)
    {
        return $query->where('academic_year_id', $yearId);
    }

    public function scopeBySemester($query, $semesterId)
    {
        return $query->where('semester_id', $semesterId);
    }

    public function scopeBlockAll($query)
    {
        return $query->where('block_type', 'all');
    }

    public function scopeBlockSplit($query)
    {
        return $query->where('block_type', 'split');
    }
}
