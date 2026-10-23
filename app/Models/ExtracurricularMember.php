<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExtracurricularMember extends Model
{
    use HasFactory;

    protected $table = 'extracurricular_members';

    protected $fillable = [
        'extracurricular_id',
        'student_id',
        'academic_year_id',
        'role',
        'section',
        'status',
        'joined_date',
        'approved_by',
        'notes',
        'points_awarded',
        'avg_assignment_score',
        'avg_quiz_score',
        'attendance_score',
        'final_score',
        'predicate',
        'grade_description',
        'graded_by',
        'graded_at',
        'certificate_code',
        'certificate_issued_at',
    ];

    protected $casts = [
        'joined_date' => 'date',
        'points_awarded' => 'integer',
        'avg_assignment_score' => 'float',
        'avg_quiz_score' => 'float',
        'attendance_score' => 'float',
        'final_score' => 'float',
        'graded_at' => 'datetime',
        'certificate_issued_at' => 'datetime',
    ];

    public function extracurricular()
    {
        return $this->belongsTo(Extracurricular::class, 'extracurricular_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function approvedByUser()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function gradedByUser()
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    /**
     * Hitung Predikat Otomatis dari Nilai Akhir (0 - 100)
     */
    public static function calculatePredicate(?float $score): ?string
    {
        if ($score === null) {
            return null;
        }
        if ($score >= 85) {
            return 'Sangat Baik';
        }
        if ($score >= 75) {
            return 'Baik';
        }
        if ($score >= 60) {
            return 'Cukup';
        }
        return 'Kurang';
    }

    /**
     * Generate Deskripsi Otomatis Capaian Ekstrakurikuler
     */
    public static function generateDefaultDescription(string $ekskulName, string $predicate, ?string $topics = null): string
    {
        $topicText = $topics
            ? "khususnya pada materi/aktivitas {$topics}"
            : "dalam setiap sesi latihan, penugasan, dan evaluasi materi";

        return match ($predicate) {
            'Sangat Baik' => "Menunjukkan penguasaan kompetensi, keaktifan, dan antusiasme yang Sangat Baik pada kegiatan ekstrakurikuler {$ekskulName}, {$topicText}.",
            'Baik' => "Menunjukkan penguasaan materi, kedisiplinan, dan partisipasi yang Baik pada kegiatan ekstrakurikuler {$ekskulName}, {$topicText}.",
            'Cukup' => "Menunjukkan partisipasi yang Cukup pada kegiatan ekstrakurikuler {$ekskulName}, namun masih perlu meningkatkan konsistensi latihan dan penyelesaian tugas.",
            default => "Perlu bimbingan lebih lanjut serta peningkatan kehadiran dan keaktifan dalam mengikuti kegiatan ekstrakurikuler {$ekskulName}.",
        };
    }

    public function getPredicateBadgeColorAttribute(): string
    {
        return match ($this->predicate) {
            'Sangat Baik', 'A' => 'bg-emerald-100 text-emerald-900 border-emerald-300',
            'Baik', 'B' => 'bg-blue-100 text-blue-900 border-blue-300',
            'Cukup', 'C' => 'bg-amber-100 text-amber-900 border-amber-300',
            'Kurang', 'D' => 'bg-rose-100 text-rose-900 border-rose-300',
            default => 'bg-slate-100 text-slate-600 border-slate-200',
        };
    }

    public function getPredicateLetterAttribute(): string
    {
        return match ($this->predicate) {
            'Sangat Baik', 'A' => 'A',
            'Baik', 'B' => 'B',
            'Cukup', 'C' => 'C',
            'Kurang', 'D' => 'D',
            default => '-',
        };
    }

    public function getRoleLabelAttribute(): string
    {
        return match($this->role) {
            'ketua' => 'Ketua / Koordinator',
            'wakil_ketua' => 'Wakil Ketua',
            'sekretaris' => 'Sekretaris',
            'bendahara' => 'Bendahara',
            'sie_kegiatan' => 'Seksi Kegiatan',
            default => 'Anggota Aktif'
        };
    }

    public function getRoleBadgeColorAttribute(): string
    {
        return match($this->role) {
            'ketua' => 'from-amber-500 to-orange-600',
            'wakil_ketua' => 'from-orange-500 to-amber-600',
            'sekretaris' => 'from-blue-500 to-indigo-600',
            'bendahara' => 'from-emerald-500 to-teal-600',
            'sie_kegiatan' => 'from-purple-500 to-fuchsia-600',
            default => 'from-slate-600 to-slate-700'
        };
    }
}
