<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CircleSkillEndorsement extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'circle_post_id',
        'student_user_id',
        'teacher_user_id',
        'skill_name',
        'endorsement_note',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function post()
    {
        return $this->belongsTo(CirclePost::class, 'circle_post_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_user_id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_user_id');
    }
}
