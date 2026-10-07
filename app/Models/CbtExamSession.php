<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CbtExamSession extends Model
{
    use HasFactory;

    protected $table = 'cbt_exam_sessions';

    protected $fillable = [
        'exam_id', 'student_id', 'classroom_id', 'competition_team_id',
        'attempt_number', 'started_at', 'finished_at', 'deadline_at',
        'status', 'question_order', 'option_orders',
        'tab_switch_count', 'ip_address', 'user_agent',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'deadline_at' => 'datetime',
        'question_order' => 'array',
        'option_orders' => 'array',
        'attempt_number' => 'integer',
        'tab_switch_count' => 'integer',
    ];

    public const STATUSES = [
        'not_started' => 'Belum Mulai',
        'in_progress' => 'Sedang Mengerjakan',
        'submitted' => 'Sudah Dikumpulkan',
        'timeout' => 'Waktu Habis',
        'graded' => 'Sudah Dinilai',
    ];

    // Relationships
    public function exam(): BelongsTo { return $this->belongsTo(CbtExam::class, 'exam_id'); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function classroom(): BelongsTo { return $this->belongsTo(Classroom::class); }
    public function competitionTeam(): BelongsTo { return $this->belongsTo(CbtCompetitionTeam::class, 'competition_team_id'); }

    public function answers(): HasMany { return $this->hasMany(CbtAnswer::class, 'session_id'); }

    public function result(): HasOne { return $this->hasOne(CbtExamResult::class, 'session_id'); }

    // Helpers
    public function isInProgress(): bool { return $this->status === 'in_progress'; }
    public function isSubmitted(): bool { return in_array($this->status, ['submitted', 'graded']); }
    public function isTimedOut(): bool { return $this->status === 'timeout'; }

    public function hasTimeRemaining(): bool
    {
        if (!$this->deadline_at) return true;
        return now()->lt($this->deadline_at);
    }

    public function getTimeRemainingSeconds(): int
    {
        if (!$this->deadline_at) return 0;
        return max(0, now()->diffInSeconds($this->deadline_at, false));
    }

    public function recordTabSwitch(): void
    {
        $this->increment('tab_switch_count');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    // Scopes
    public function scopeByExam($query, $examId) { return $query->where('exam_id', $examId); }
    public function scopeByStudent($query, $studentId) { return $query->where('student_id', $studentId); }
    public function scopeInProgress($query) { return $query->where('status', 'in_progress'); }
}
