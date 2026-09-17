<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicYear extends Model
{
    use HasFactory, SoftDeletes;

    public $timestamps = false;

    protected $table = 'academic_years';

    protected $fillable = [
        'year',
        'start_date',
        'end_date',
        'semester_start',
        'semester_end',
        'is_active',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'semester_start' => 'date',
        'semester_end' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Boot the model and register cache clearing listeners.
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function () {
            \App\Services\CacheService::clearAcademicCache();
        });

        static::deleted(function () {
            \App\Services\CacheService::clearAcademicCache();
        });
    }

    /**
     * Relationship: AcademicYear has many semesters
     */
    public function semesters()
    {
        return $this->hasMany(Semester::class);
    }

    /**
     * Relationship: AcademicYear has many student classes
     */
    public function studentClasses()
    {
        return $this->hasMany(StudentClass::class);
    }

    /**
     * Relationship: AcademicYear has many grades
     */
    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    /**
     * Relationship: AcademicYear has many final grades
     */
    public function finalGrades()
    {
        return $this->hasMany(FinalGrade::class);
    }

    /**
     * Relationship: AcademicYear has many bills
     */
    public function bills()
    {
        return $this->hasMany(StudentBill::class);
    }

    /**
     * Relationship: AcademicYear has many student bills
     */
    public function studentBills()
    {
        return $this->hasMany(StudentBill::class);
    }

    /**
     * Get year label (e.g., "2023/2024")
     */
    public function getYearLabel()
    {
        return $this->year;
    }

    /**
     * Fallback for name attribute (returns year string)
     */
    public function getNameAttribute()
    {
        return $this->year;
    }

    /**
     * Check if academic year is ongoing
     */
    public function isOngoing()
    {
        $today = now()->toDateString();
        return $today >= $this->start_date->toDateString()
            && $today <= $this->end_date->toDateString();
    }

    /**
     * Scope: Get active academic years
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Get current academic year
     */
    public function scopeCurrent($query)
    {
        $today = now()->toDateString();
        return $query->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today);
    }
}
