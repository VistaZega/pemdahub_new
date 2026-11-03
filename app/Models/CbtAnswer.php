<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CbtAnswer extends Model
{
    use HasFactory;

    protected $table = 'cbt_answers';

    protected $fillable = [
        'session_id', 'question_id',
        'selected_option', 'text_answer', 'handwriting_image',
        'is_correct', 'score_obtained', 'is_flagged',
        'manual_score', 'teacher_feedback', 'graded_by', 'graded_at',
        'time_spent_seconds',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'score_obtained' => 'float',
        'is_flagged' => 'boolean',
        'manual_score' => 'float',
        'graded_at' => 'datetime',
        'time_spent_seconds' => 'integer',
    ];

    // Relationships
    public function session(): BelongsTo { return $this->belongsTo(CbtExamSession::class, 'session_id'); }
    public function question(): BelongsTo { return $this->belongsTo(CbtQuestion::class, 'question_id'); }
    public function gradedByUser(): BelongsTo { return $this->belongsTo(User::class, 'graded_by'); }

    // Media URL Accessor
    public function getHandwritingImageUrlAttribute(): ?string
    {
        return $this->handwriting_image ? asset('storage/' . $this->handwriting_image) : null;
    }

    // Helpers
    public function isAnswered(): bool
    {
        return !is_null($this->selected_option) || !empty(trim($this->text_answer ?? '')) || !empty($this->handwriting_image);
    }

    public function needsManualGrading(): bool
    {
        return $this->question && in_array($this->question->question_type, ['essay', 'fill_blank'])
            && is_null($this->manual_score);
    }

    // Scopes
    public function scopeBySession($query, $sessionId) { return $query->where('session_id', $sessionId); }
    public function scopeCorrect($query) { return $query->where('is_correct', true); }
    public function scopeFlagged($query) { return $query->where('is_flagged', true); }
    public function scopeNeedsGrading($query)
    {
        return $query->whereHas('question', function ($q) {
            $q->whereIn('question_type', ['essay', 'fill_blank']);
        })->whereNull('manual_score');
    }
}
