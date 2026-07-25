<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LmsStudentAchievement extends Model
{
    use HasFactory;

    protected $table = 'lms_student_achievements';

    protected $fillable = [
        'student_id',
        'badge_key',
        'badge_name',
        'badge_icon',
        'unlocked_at',
    ];

    protected $casts = [
        'unlocked_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
