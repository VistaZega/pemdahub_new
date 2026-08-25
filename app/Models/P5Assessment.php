<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class P5Assessment extends Model
{
    use HasFactory;

    protected $table = 'p5_assessments';

    protected $fillable = [
        'p5_project_id',
        'student_id',
        'p5_project_target_id',
        'score',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(P5Project::class, 'p5_project_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(P5ProjectTarget::class, 'p5_project_target_id');
    }

    public function getScoreLabelAttribute(): string
    {
        return match($this->score) {
            'MB' => 'Mulai Berkembang',
            'SB' => 'Sedang Berkembang',
            'BSH' => 'Berkembang Sesuai Harapan',
            'SAB' => 'Sangat Berkembang',
            default => '-',
        };
    }
}
