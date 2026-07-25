<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LmsModule extends Model
{
    use HasFactory, SoftDeletes;

    public $timestamps = true;

    protected $table = 'lms_modules';

    protected $fillable = [
        'course_id',
        'title',
        'description',
        'sequence',
        'is_active',
        'is_sequential',
        'color',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_sequential' => 'boolean',
        'sequence' => 'integer',
    ];

    /**
     * Relationship: Module belongs to Course
     */
    public function course()
    {
        return $this->belongsTo(LmsCourse::class, 'course_id');
    }

    /**
     * Relationship: Module has many materials
     */
    public function materials()
    {
        return $this->hasMany(LmsMaterial::class, 'module_id');
    }

    /**
     * Scope: Get active modules
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Order by sequence
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sequence', 'asc');
    }

    public function assignments()
    {
        return $this->hasMany(LmsAssignment::class, 'module_id');
    }

    public function quizzes()
    {
        return $this->hasMany(LmsQuiz::class, 'module_id');
    }

    public function games()
    {
        return $this->hasMany(LmsGame::class, 'module_id');
    }

    /**
     * Get shorthand module code (e.g., M1)
     */
    public function getCode(): string
    {
        return 'M' . $this->sequence;
    }
}
