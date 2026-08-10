<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LmsQuiz extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'lms_quizzes';

    protected $fillable = [
        'course_id',
        'module_id',
        'question_package_id',
        'question_sample_count',
        'points_per_question',
        'title',
        'description',
        'start_time',
        'end_time',
        'time_limit',
        'total_score',
        'passing_score',
        'max_attempts',
        'shuffle_questions',
        'show_result',
        'is_published',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_published' => 'boolean',
        'total_score' => 'float',
        'points_per_question' => 'float',
        'passing_score' => 'integer',
        'time_limit' => 'integer',
        'max_attempts' => 'integer',
        'question_sample_count' => 'integer',
        'shuffle_questions' => 'boolean',
        'show_result' => 'boolean',
        'question_package_id' => 'integer',
    ];

    public function course()
    {
        return $this->belongsTo(LmsCourse::class, 'course_id');
    }

    public function module()
    {
        return $this->belongsTo(LmsModule::class, 'module_id');
    }

    public function cbtQuestionBank()
    {
        return $this->belongsTo(CbtQuestionBank::class, 'question_package_id');
    }

    public function questions()
    {
        return $this->hasMany(LmsQuizQuestion::class, 'quiz_id')->orderBy('order_number');
    }

    public function attempts()
    {
        return $this->hasMany(LmsQuizAttempt::class, 'quiz_id');
    }

    public function isAvailable()
    {
        if (!$this->is_published) return false;
        $now = now();
        if ($this->start_time && $now->isBefore($this->start_time)) return false;
        if ($this->end_time && $now->isAfter($this->end_time)) return false;
        return true;
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Check if student can attempt this quiz
     */
    public function canAttempt($studentId): bool
    {
        if (!$this->isAvailable()) return false;

        $attemptCount = $this->attempts()
            ->where('student_id', $studentId)
            ->whereNotNull('finished_at')
            ->count();

        return $attemptCount < ($this->max_attempts ?? 1);
    }

    /**
     * Get remaining attempts for a student
     */
    public function getRemainingAttempts($studentId): int
    {
        $used = $this->attempts()
            ->where('student_id', $studentId)
            ->whereNotNull('finished_at')
            ->count();

        return max(0, ($this->max_attempts ?? 1) - $used);
    }

    /**
     * Get best score for a student
     */
    public function getBestScore($studentId): ?float
    {
        return $this->attempts()
            ->where('student_id', $studentId)
            ->whereNotNull('finished_at')
            ->max('score');
    }

    /**
     * Calculate effective points per question for this quiz
     */
    public function getEffectivePointsPerQuestion(int $questionsCount = 0): float
    {
        if ($this->points_per_question !== null && $this->points_per_question > 0) {
            return (float) $this->points_per_question;
        }

        $effectiveCount = $this->question_sample_count ?: $questionsCount;
        if ($effectiveCount > 0 && $this->total_score > 0) {
            return round((float) $this->total_score / $effectiveCount, 2);
        }

        return 1.0;
    }
}
