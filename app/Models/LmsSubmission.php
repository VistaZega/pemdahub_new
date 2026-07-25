<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LmsSubmission extends Model
{
    use HasFactory, SoftDeletes;

    public $timestamps = true;

    protected $table = 'lms_submissions';

    protected $fillable = [
        'assignment_id',
        'student_id',
        'submission_text',
        'file_path',
        'file_size',
        'score',
        'feedback',
        'teacher_notes',
        'revision_notes',
        'status',
        'submitted_at',
        'graded_at',
        'graded_by',
        'attempt_number',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime',
        'score' => 'float',
    ];

    protected const STATUSES = [
        'draft' => 'Draft',
        'submitted' => 'Dikumpulkan',
        'graded' => 'Dinilai',
        'late' => 'Terlambat',
        'revision_requested' => 'Minta Revisi',
    ];

    /**
     * Relationship: Submission belongs to Assignment
     */
    public function assignment()
    {
        return $this->belongsTo(LmsAssignment::class, 'assignment_id');
    }

    /**
     * Relationship: Submission belongs to Student
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Relationship: Submission graded by User
     */
    public function gradedBy()
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    /**
     * Get status label
     */
    public function getStatusLabel()
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /**
     * Check if submission is late
     */
    public function isLate()
    {
        return $this->submitted_at && $this->submitted_at->isAfter($this->assignment->due_date);
    }

    /**
     * Scope: Get submitted assignments
     */
    public function scopeSubmitted($query)
    {
        return $query->whereIn('status', ['submitted', 'graded', 'late']);
    }

    /**
     * Scope: Get graded submissions
     */
    public function scopeGraded($query)
    {
        return $query->where('status', 'graded');
    }

    public function isPdf(): bool
    {
        if (!$this->file_path) return false;
        return strtolower(pathinfo($this->file_path, PATHINFO_EXTENSION)) === 'pdf';
    }

    public function isImage(): bool
    {
        if (!$this->file_path) return false;
        $ext = strtolower(pathinfo($this->file_path, PATHINFO_EXTENSION));
        return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    }

    public function needsRevision(): bool
    {
        return $this->status === 'revision_requested';
    }
}
