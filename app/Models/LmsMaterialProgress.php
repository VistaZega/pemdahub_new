<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LmsMaterialProgress extends Model
{
    use HasFactory;

    protected $table = 'lms_material_progress';

    protected $fillable = [
        'material_id',
        'student_id',
        'status',
        'progress_percent',
        'time_spent_seconds',
        'first_viewed_at',
        'completed_at',
    ];

    protected $casts = [
        'progress_percent' => 'integer',
        'time_spent_seconds' => 'integer',
        'first_viewed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected const STATUSES = [
        'viewed' => 'Dilihat',
        'in_progress' => 'Sedang Dipelajari',
        'completed' => 'Selesai',
    ];

    public function material()
    {
        return $this->belongsTo(LmsMaterial::class, 'material_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function getStatusLabelAttribute()
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getTimeSpentFormattedAttribute()
    {
        $seconds = $this->time_spent_seconds;
        if ($seconds < 60) return $seconds . ' detik';
        if ($seconds < 3600) return floor($seconds / 60) . ' menit';
        return floor($seconds / 3600) . ' jam ' . floor(($seconds % 3600) / 60) . ' menit';
    }

    public function markCompleted()
    {
        $alreadyCompleted = $this->status === 'completed';

        $this->status = 'completed';
        $this->progress_percent = 100;
        $this->completed_at = now();
        $this->save();

        if (!$alreadyCompleted) {
            $student = $this->student;
            if ($student && $student->user_id && $this->material) {
                \App\Models\ReputationLog::log(
                    $student->user_id, 
                    20, 
                    'lms_material', 
                    "Menyelesaikan materi LMS: " . ($this->material->title ?? 'Materi'),
                    $this->material
                );
            }
        }
    }

    public static function trackView($materialId, $studentId, $timeSpent = 0)
    {
        return static::updateOrCreate(
            ['material_id' => $materialId, 'student_id' => $studentId],
            [
                'status' => 'viewed',
                'first_viewed_at' => now(),
                'time_spent_seconds' => \DB::raw('time_spent_seconds + ' . intval($timeSpent)),
            ]
        );
    }

    public static function getProgressForCourse($courseId, $studentId)
    {
        return app(\App\Services\LmsProgressService::class)->calculateCourseProgress($courseId, $studentId);
    }

    public static function getMaterialOnlyProgressForCourse($courseId, $studentId)
    {
        $totalMaterials = LmsMaterial::where('course_id', $courseId)->where('is_published', true)->count();
        if ($totalMaterials === 0) return 0;

        $completedMaterials = static::whereHas('material', fn($q) => $q->where('course_id', $courseId))
            ->where('student_id', $studentId)
            ->where('status', 'completed')
            ->count();

        return round(($completedMaterials / $totalMaterials) * 100);
    }
}
