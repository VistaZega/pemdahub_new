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
        'group_id',
        'submission_text',
        'file_path',
        'file_paths',
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
        'file_paths' => 'array',
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
     * Relationship: Submission belongs to Student (who submitted, or group leader)
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Relationship: Submission belongs to Group (if group assignment)
     */
    public function group()
    {
        return $this->belongsTo(LmsAssignmentGroup::class, 'group_id');
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

    /**
     * Get full list of files (backward compatible with single file_path)
     */
    public function getFileListAttribute(): array
    {
        if (!empty($this->file_paths) && is_array($this->file_paths)) {
            return array_values(array_filter($this->file_paths));
        }
        if (!empty($this->file_paths) && is_string($this->file_paths)) {
            $decoded = json_decode($this->file_paths, true);
            if (is_array($decoded) && count($decoded) > 0) {
                return array_values(array_filter($decoded));
            }
        }
        if (!empty($this->file_path)) {
            return [$this->file_path];
        }
        return [];
    }

    public static function isPdfPath(?string $path): bool
    {
        if (!$path) return false;
        return strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'pdf';
    }

    public static function isImagePath(?string $path): bool
    {
        if (!$path) return false;
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'heic', 'heif']);
    }

    public function isPdf(?string $path = null): bool
    {
        $targetPath = $path ?? $this->file_path;
        if ($targetPath) {
            return static::isPdfPath($targetPath);
        }
        foreach ($this->file_list as $f) {
            if (static::isPdfPath($f)) return true;
        }
        return false;
    }

    public function isImage(?string $path = null): bool
    {
        $targetPath = $path ?? $this->file_path;
        if ($targetPath) {
            return static::isImagePath($targetPath);
        }
        foreach ($this->file_list as $f) {
            if (static::isImagePath($f)) return true;
        }
        return false;
    }

    public function needsRevision(): bool
    {
        return $this->status === 'revision_requested';
    }
}
