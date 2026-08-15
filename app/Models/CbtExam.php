<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CbtExam extends Model
{
    use HasFactory;

    protected $table = 'cbt_exams';

    protected $fillable = [
        'school_id', 'subject_id', 'teacher_id', 'academic_year_id', 'semester_id',
        'exam_title', 'exam_description', 'exam_type', 'exam_scope', 'status',
        'start_time', 'end_time', 'duration_minutes',
        'total_questions_shown', 'randomize_questions', 'randomize_options',
        'show_result', 'show_answer_key', 'allow_review',
        'passing_score', 'max_attempts',
        'access_code', 'prevent_tab_switch', 'prevent_copy_paste',
        'auto_sync_grade', 'created_by', 'is_paused', 'paused_at',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'duration_minutes' => 'integer',
        'total_questions_shown' => 'integer',
        'randomize_questions' => 'boolean',
        'randomize_options' => 'boolean',
        'show_result' => 'boolean',
        'show_answer_key' => 'boolean',
        'allow_review' => 'boolean',
        'passing_score' => 'float',
        'max_attempts' => 'integer',
        'prevent_tab_switch' => 'boolean',
        'prevent_copy_paste' => 'boolean',
        'auto_sync_grade' => 'boolean',
        'is_paused' => 'boolean',
        'paused_at' => 'datetime',
    ];

    public const EXAM_TYPES = [
        'tugas' => 'Tugas',
        'quiz' => 'Quiz',
        'uts' => 'UTS',
        'uas' => 'UAS',
        'remedial' => 'Remedial',
        'tryout' => 'Try Out',
        'test_masuk' => 'Test Masuk',
        'ujian_khusus' => 'Ujian Khusus',
    ];

    /**
     * Exam types that belong to school scope (Admin only)
     */
    public const SCHOOL_SCOPE_TYPES = ['uas', 'uts', 'tryout', 'test_masuk', 'ujian_khusus'];

    /**
     * Exam types that belong to class scope (Guru)
     */
    public const CLASS_SCOPE_TYPES = ['tugas', 'quiz', 'remedial'];

    public const EXAM_SCOPES = [
        'school' => 'Ujian Sekolah',
        'class' => 'Ujian Kelas',
    ];

    public const STATUSES = [
        'draft' => 'Draft',
        'published' => 'Diterbitkan',
        'active' => 'Sedang Berlangsung',
        'completed' => 'Selesai',
        'archived' => 'Diarsipkan',
    ];

    // Mapping exam_type → grade_type for sync to grades table
    public const EXAM_TYPE_TO_GRADE_TYPE = [
        'tugas' => 'tugas',
        'quiz' => 'tugas',
        'uts' => 'uts',
        'uas' => 'uas',
        'remedial' => 'tugas',
        'tryout' => 'tugas',
        'test_masuk' => 'tugas',
        'ujian_khusus' => 'uas',
    ];

    // Relationships
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }
    public function teacher(): BelongsTo { return $this->belongsTo(Teacher::class); }
    public function academicYear(): BelongsTo { return $this->belongsTo(AcademicYear::class); }
    public function semester(): BelongsTo { return $this->belongsTo(Semester::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    public function questionBanks(): BelongsToMany
    {
        return $this->belongsToMany(CbtQuestionBank::class, 'cbt_exam_question_bank', 'exam_id', 'question_bank_id')
            ->withPivot('questions_to_pick');
    }

    public function examQuestions(): HasMany
    {
        return $this->hasMany(CbtExamQuestion::class, 'exam_id')->orderBy('sort_order');
    }

    public function participants(): HasMany { return $this->hasMany(CbtExamParticipant::class, 'exam_id'); }
    public function classrooms(): BelongsToMany
    {
        return $this->belongsToMany(Classroom::class, 'cbt_exam_participants', 'exam_id', 'classroom_id');
    }
    public function sessions(): HasMany { return $this->hasMany(CbtExamSession::class, 'exam_id'); }
    public function results(): HasMany { return $this->hasMany(CbtExamResult::class, 'exam_id'); }

    // Helpers
    public function isActive(): bool { return $this->status === 'active'; }
    public function isDraft(): bool { return $this->status === 'draft'; }
    public function isCompleted(): bool { return $this->status === 'completed'; }

    public function isAccessible(): bool
    {
        if (!$this->isActive()) return false;
        $now = now();
        if ($this->start_time && $now->lt($this->start_time)) return false;
        if ($this->end_time && $now->gt($this->end_time)) return false;
        return true;
    }

    public function verifyAccessCode(?string $code): bool
    {
        if (empty($this->access_code)) return true;
        return $this->access_code === $code;
    }

    public function getGradeType(): string
    {
        return self::EXAM_TYPE_TO_GRADE_TYPE[$this->exam_type] ?? 'tugas';
    }

    public function getExamTypeLabelAttribute(): string
    {
        return self::EXAM_TYPES[$this->exam_type] ?? $this->exam_type;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    // Scopes
    public function scopeBySchool($query, $schoolId) { return $query->where('school_id', $schoolId); }
    public function scopeByTeacher($query, $teacherId) { return $query->where('teacher_id', $teacherId); }
    public function scopeByStatus($query, $status) { return $query->where('status', $status); }
    public function scopeActive($query) { return $query->where('status', 'active'); }
    public function scopePublished($query) { return $query->whereIn('status', ['published', 'active']); }
    public function scopeSchoolLevel($query) { return $query->where('exam_scope', 'school'); }
    public function scopeClassLevel($query) { return $query->where('exam_scope', 'class'); }

    /**
     * Check if this exam type belongs to school scope
     */
    public function isSchoolScope(): bool { return $this->exam_scope === 'school'; }
    public function isClassScope(): bool { return $this->exam_scope === 'class'; }

    /**
     * Get exam types for school scope
     */
    public static function getSchoolScopeTypes(): array
    {
        return collect(self::EXAM_TYPES)->only(self::SCHOOL_SCOPE_TYPES)->toArray();
    }

    /**
     * Get exam types for class scope
     */
    public static function getClassScopeTypes(): array
    {
        return collect(self::EXAM_TYPES)->only(self::CLASS_SCOPE_TYPES)->toArray();
    }
}
