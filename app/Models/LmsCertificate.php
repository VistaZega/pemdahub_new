<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LmsCertificate extends Model
{
    use HasFactory;

    protected $table = 'lms_certificates';

    protected $fillable = [
        'student_id',
        'course_id',
        'certificate_code',
        'title',
        'final_progress',
        'final_score',
        'completed_at',
        'issued_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'issued_at' => 'datetime',
        'final_progress' => 'float',
        'final_score' => 'float',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function course()
    {
        return $this->belongsTo(LmsCourse::class, 'course_id');
    }

    /**
     * Generate unique certificate code
     */
    public static function generateCode(Student $student, LmsCourse $course): string
    {
        $prefix = 'PEMBDA-CERT-';
        $date = now()->format('ymd');
        $rand = strtoupper(Str::random(6));
        return $prefix . $date . '-' . $rand;
    }

    /**
     * Issue certificate for student when course progress is 100%
     */
    public static function issueForStudent(Student $student, LmsCourse $course): ?self
    {
        if (!$student->id || !$course->id) return null;

        $existing = self::where('student_id', $student->id)
            ->where('course_id', $course->id)
            ->first();

        if ($existing) return $existing;

        $progress = LmsMaterialProgress::getProgressForCourse($course->id, $student->id);
        if ($progress < 100) return null;

        // Get final score from grades if available
        $finalGrade = \App\Models\FinalGrade::where('student_id', $student->id)
            ->where('subject_id', $course->subject_id)
            ->where('semester_id', $course->semester_id)
            ->first();

        return self::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'certificate_code' => self::generateCode($student, $course),
            'title' => 'Sertifikat Penyelesaian: ' . ($course->course_name ?? $course->name),
            'final_progress' => 100,
            'final_score' => $finalGrade?->final_score,
            'completed_at' => now(),
            'issued_at' => now(),
        ]);
    }
}