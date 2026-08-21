<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LmsAssignmentGroup extends Model
{
    use HasFactory;

    protected $table = 'lms_assignment_groups';

    protected $fillable = [
        'assignment_id',
        'name',
        'leader_id',
    ];

    /**
     * Group belongs to an assignment
     */
    public function assignment()
    {
        return $this->belongsTo(LmsAssignment::class, 'assignment_id');
    }

    /**
     * Group leader (Student)
     */
    public function leader()
    {
        return $this->belongsTo(Student::class, 'leader_id');
    }

    /**
     * Group members (Students)
     */
    public function members()
    {
        return $this->belongsToMany(Student::class, 'lms_assignment_group_members', 'group_id', 'student_id')
            ->withTimestamps();
    }

    /**
     * Group submission
     */
    public function submission()
    {
        return $this->hasOne(LmsSubmission::class, 'group_id');
    }

    /**
     * Check if a student is a member of this group
     */
    public function hasMember(int $studentId): bool
    {
        return $this->members()->where('students.id', $studentId)->exists() || $this->leader_id === $studentId;
    }

    /**
     * Check if a student is the leader of this group
     */
    public function isLeader(int $studentId): bool
    {
        return $this->leader_id === $studentId;
    }
}
