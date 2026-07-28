<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TimeSlot extends Model
{
    protected $fillable = [
        'school_id',
        'academic_year_id',
        'day_of_week',
        'slot_name',
        'shift',
        'slot_type',
        'slot_order',
        'start_time',
        'end_time',
        'duration_minutes',
        'is_teaching_slot',
        'is_active'
    ];

    protected $casts = [
        'slot_order' => 'integer',
        'duration_minutes' => 'integer',
        'is_teaching_slot' => 'boolean',
        'is_active' => 'boolean'
    ];

    // Relationships
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    // Scopes
    public function scopeTeaching($query)
    {
        return $query->where('is_teaching_slot', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('slot_order');
    }

    // Accessors
    public function getFormattedTimeAttribute()
    {
        return $this->start_time . ' - ' . $this->end_time;
    }

    public function getDurationInHoursAttribute()
    {
        return round($this->duration_minutes / 60, 2);
    }
}
