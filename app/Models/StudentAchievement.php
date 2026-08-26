<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentAchievement extends Model
{
    use HasFactory;

    protected $table = 'student_achievements';

    protected $fillable = [
        'student_id',
        'academic_year_id',
        'title',
        'type',
        'level',
        'rank',
        'achievement_date',
        'description',
        'certificate_file',
        'status',
        'points',
        'verified_by',
        'verified_at',
        'verification_notes',
        'created_by',
    ];

    protected $casts = [
        'achievement_date' => 'date',
        'verified_at'      => 'datetime',
        'points'           => 'integer',
    ];

    /**
     * Relationship: Achievement belongs to Student
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Relationship: Achievement belongs to AcademicYear
     */
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Relationship: Achievement created by User
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship: Achievement verified by Homeroom Teacher / Admin
     */
    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Calculate Standard Reputation Points based on Level & Rank
     */
    public static function calculatePoints(string $level, ?string $rank = null): int
    {
        $levelPoints = [
            'international' => 500,
            'national'      => 400,
            'province'      => 300,
            'city'          => 200,
            'district'      => 100,
            'school'        => 50,
        ];

        $rankBonus = [
            'winner'      => 50,
            'runner_up'   => 30,
            'third_place' => 20,
            'participant' => 0,
        ];

        $base = $levelPoints[$level] ?? 50;
        $bonus = $rank ? ($rankBonus[$rank] ?? 0) : 0;

        return $base + $bonus;
    }

    /**
     * Get status label in Indonesian
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'verified' => 'Diakui oleh Wali Kelas',
            'rejected' => 'Tidak Diakui (Poin Ditarik)',
            default    => 'Menunggu Justifikasi',
        };
    }

    /**
     * Get status badge styling
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'verified' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'rejected' => 'bg-rose-100 text-rose-800 border-rose-300',
            default    => 'bg-amber-100 text-amber-800 border-amber-300',
        };
    }

    /**
     * Get type label
     */
    public function getTypeLabelAttribute()
    {
        return match($this->type) {
            'academic' => 'Akademik',
            'sport' => 'Olahraga',
            'art' => 'Seni',
            'competition' => 'Kompetisi',
            'other' => 'Lainnya',
            default => '-',
        };
    }

    /**
     * Get level label
     */
    public function getLevelLabelAttribute()
    {
        return match($this->level) {
            'school' => 'Sekolah',
            'district' => 'Kecamatan',
            'city' => 'Kota/Kabupaten',
            'province' => 'Provinsi',
            'national' => 'Nasional',
            'international' => 'Internasional',
            default => '-',
        };
    }

    /**
     * Get rank label
     */
    public function getRankLabelAttribute()
    {
        return match($this->rank) {
            'winner' => 'Juara 1',
            'runner_up' => 'Juara 2',
            'third_place' => 'Juara 3',
            'participant' => 'Peserta',
            default => '-',
        };
    }

    /**
     * Get level badge class
     */
    public function getLevelBadgeAttribute()
    {
        return match($this->level) {
            'international' => 'badge-danger',
            'national' => 'badge-warning',
            'province' => 'badge-info',
            'city' => 'badge-primary',
            'district', 'school' => 'badge-secondary',
            default => 'badge-light',
        };
    }

    /**
     * Scope: Filter by student
     */
    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope: Filter by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: Filter by academic year
     */
    public function scopeByAcademicYear($query, $academicYearId)
    {
        return $query->where('academic_year_id', $academicYearId);
    }

    /**
     * Scope: Filter pending verification
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Filter verified
     */
    public function scopeVerified($query)
    {
        return $query->where('status', 'verified');
    }

    /**
     * Scope: Filter rejected
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}
