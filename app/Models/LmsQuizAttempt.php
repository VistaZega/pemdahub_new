<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LmsQuizAttempt extends Model
{
    use HasFactory;

    protected $table = 'lms_quiz_attempts';

    protected $fillable = [
        'quiz_id',
        'student_id',
        'question_ids',
        'started_at',
        'finished_at',
        'score',
        'is_passed',
    ];

    protected $casts = [
        'question_ids' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'score' => 'float',
        'is_passed' => 'boolean',
    ];

    /**
     * Get list of assigned question IDs for this attempt
     */
    public function getAssignedQuestionIds(): array
    {
        if (!empty($this->question_ids) && is_array($this->question_ids)) {
            return array_values(array_map('intval', array_filter($this->question_ids)));
        }

        // Fallback untuk attempt yang sudah disubmit tetapi belum memiliki question_ids di kolom database
        if ($this->relationLoaded('answers')) {
            $answerQuestionIds = $this->answers->pluck('question_id')->filter()->values()->toArray();
        } else {
            $answerQuestionIds = $this->answers()->pluck('question_id')->filter()->values()->toArray();
        }

        if (!empty($answerQuestionIds)) {
            return array_values(array_map('intval', $answerQuestionIds));
        }

        return [];
    }

    /**
     * Get questions collection assigned to this attempt in exact order
     */
    public function getQuestions()
    {
        $ids = $this->getAssignedQuestionIds();
        if (!empty($ids)) {
            $questions = $this->quiz->questions()->reorder()->whereIn('id', $ids)->get();
            $idOrder = array_flip($ids);
            return $questions->sortBy(fn($q) => $idOrder[$q->id] ?? 999999)->values();
        }

        // Jika attempt belum memiliki question_ids dan belum ada jawaban, fallback ke quiz questions
        return $this->quiz->questions;
    }

    public function quiz()
    {
        return $this->belongsTo(LmsQuiz::class, 'quiz_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function answers()
    {
        return $this->hasMany(LmsQuizAnswer::class, 'attempt_id');
    }

    public function isFinished()
    {
        return $this->finished_at !== null;
    }

    public function getDurationAttribute()
    {
        if (!$this->finished_at) return null;
        return $this->started_at->diffInMinutes($this->finished_at);
    }
}
