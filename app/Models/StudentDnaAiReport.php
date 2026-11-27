<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentDnaAiReport extends Model
{
    protected $table = 'student_dna_ai_reports';

    protected $fillable = [
        'student_id',
        'data_fingerprint',
        'provider',
        'model_used',
        'ai_payload',
        'generated_at',
    ];

    protected $casts = [
        'ai_payload'   => 'array',
        'generated_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
