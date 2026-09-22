<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LmsCourseGroup extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $table = 'lms_course_groups';

    protected $fillable = [
        'course_id',
        'name',
        'theme',
        'leader_id',
    ];

    /**
     * Relationship: Course
     */
    public function course()
    {
        return $this->belongsTo(LmsCourse::class, 'course_id');
    }

    /**
     * Relationship: Leader (Student)
     */
    public function leader()
    {
        return $this->belongsTo(Student::class, 'leader_id');
    }

    /**
     * Relationship: Members (Students)
     */
    public function members()
    {
        return $this->belongsToMany(Student::class, 'lms_course_group_members', 'course_group_id', 'student_id')
            ->withTimestamps();
    }

    /**
     * Helper: Check if student is in this group
     */
    public function hasMember(int $studentId): bool
    {
        if ($this->leader_id === $studentId) {
            return true;
        }

        return $this->members->contains('id', $studentId);
    }

    /**
     * Helper: Check if student is the leader
     */
    public function isLeader(int $studentId): bool
    {
        return $this->leader_id === $studentId;
    }
}
