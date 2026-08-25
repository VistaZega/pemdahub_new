<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class P5Project extends Model
{
    use HasFactory;

    protected $table = 'p5_projects';

    protected $fillable = [
        'school_id',
        'academic_year_id',
        'classroom_id',
        'title',
        'theme',
        'description',
        'created_by',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function targets(): HasMany
    {
        return $this->hasMany(P5ProjectTarget::class, 'p5_project_id');
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(P5Assessment::class, 'p5_project_id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(P5ProjectNote::class, 'p5_project_id');
    }
}
