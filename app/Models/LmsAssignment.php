<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LmsAssignment extends Model
{
    use HasFactory, SoftDeletes;

    public $timestamps = true;

    protected $table = 'lms_assignments';

    protected $fillable = [
        'course_id',
        'module_id',
        'title',
        'description',
        'assignment_type',
        'file_path',
        'deadline',
        'due_date',
        'max_score',
        'is_published',
        'allow_resubmit',
        'max_resubmissions',
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'is_published' => 'boolean',
        'max_score' => 'float',
        'allow_resubmit' => 'boolean',
        'max_resubmissions' => 'integer',
    ];

    protected const ASSIGNMENT_TYPES = [
        'file' => 'Upload File',
        'text' => 'Teks/Essay',
        'file_text' => 'File + Teks',
        'link' => 'Link URL',
    ];

    /**
     * Accessor: due_date returns deadline for backward compat
     */
    public function getDueDateAttribute()
    {
        return $this->deadline;
    }

    /**
     * Mutator: setting due_date writes to deadline
     */
    public function setDueDateAttribute($value)
    {
        $this->attributes['deadline'] = $value;
    }

    /**
     * Accessor: is_active returns is_published for backward compat
     */
    public function getIsActiveAttribute()
    {
        return $this->is_published;
    }

    /**
     * Relationship: Assignment belongs to Course
     */
    public function course()
    {
        return $this->belongsTo(LmsCourse::class, 'course_id');
    }

    /**
     * Relationship: Assignment has many submissions
     */
    public function submissions()
    {
        return $this->hasMany(LmsSubmission::class, 'assignment_id');
    }

    /**
     * Check if assignment is overdue
     */
    public function isOverdue()
    {
        return $this->deadline && now()->isAfter($this->deadline);
    }

    /**
     * Get number of submissions
     */
    public function getSubmissionCount()
    {
        return $this->submissions()->count();
    }

    /**
     * Scope: Get active/published assignments
     */
    public function scopeActive($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope: Get upcoming assignments
     */
    public function scopeUpcoming($query)
    {
        return $query->where('deadline', '>', now())
            ->where('is_published', true);
    }

    /**
     * Scope: Get overdue assignments
     */
    public function scopeOverdue($query)
    {
        return $query->where('deadline', '<', now())
            ->where('is_published', true);
    }

    /**
     * Relationship: Assignment belongs to Module (optional)
     */
    public function module()
    {
        return $this->belongsTo(LmsModule::class, 'module_id');
    }

    /**
     * Get human-readable assignment type label
     */
    public function getAssignmentTypeLabel(): string
    {
        return self::ASSIGNMENT_TYPES[$this->assignment_type] ?? 'Upload File';
    }

    /**
     * Check if a student can still resubmit
     */
    public function canResubmit(?int $studentId = null): bool
    {
        if (!$this->allow_resubmit) {
            return false;
        }

        if (!$this->max_resubmissions || !$studentId) {
            return $this->allow_resubmit;
        }

        $attemptCount = $this->submissions()
            ->where('student_id', $studentId)
            ->count();

        return $attemptCount <= $this->max_resubmissions;
    }
}
