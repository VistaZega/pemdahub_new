<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentDiagnosticAssessment extends Model
{
    use HasFactory;

    protected $table = 'student_diagnostic_assessments';

    protected $fillable = [
        'student_id',
        'interests',
        'work_style_preference',
        'favorite_subject_cluster',
        'career_aspiration',
        'logic_self_score',
        'creative_self_score',
        'communication_self_score',
        'technical_self_score',
        'social_self_score',
        'discipline_self_score',
        'notes',
    ];

    protected $casts = [
        'interests' => 'array',
        'logic_self_score' => 'integer',
        'creative_self_score' => 'integer',
        'communication_self_score' => 'integer',
        'technical_self_score' => 'integer',
        'social_self_score' => 'integer',
        'discipline_self_score' => 'integer',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
