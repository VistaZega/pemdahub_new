<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeePosition extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'position_id',
        'academic_year_id',
        'classroom_id',
        'semester',
        'start_date',
        'end_date',
        'sk_number',
        'sk_date',
        'notes',
        'is_primary',
        'workload_hours',
        'position_allowance',
        'pkl_supervisor_hours',
        'pkl_honor_rate',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'sk_date' => 'date',
        'is_primary' => 'boolean',
        'workload_hours' => 'integer',
        'position_allowance' => 'decimal:2',
        'pkl_supervisor_hours' => 'integer',
        'pkl_honor_rate' => 'decimal:2',
    ];

    // Relationships
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereNull('end_date');
    }

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    // Helper
    public function isActive(): bool
    {
        return is_null($this->end_date);
    }

    public function getDurationInYears(): int
    {
        $endDate = $this->end_date ?? now();
        return $this->start_date->diffInYears($endDate);
    }
}
