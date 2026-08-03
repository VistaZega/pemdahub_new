<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Position extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'position_name',
        'position_code',
        'position_category',
        'position_level',
        'is_structural',
        'allowance_amount',
        'description',
        'is_active',
    ];

    protected $casts = [
        'position_level' => 'integer',
        'is_structural' => 'boolean',
        'allowance_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get display name of position with school type suffix (e.g., SMP, SMA, SMK) if applicable.
     */
    public function getDisplayNameAttribute(): string
    {
        $name = $this->position_name;
        
        if ($this->school) {
            $schoolType = strtoupper($this->school->type ?? '');
            $suffix = '';
            if (str_contains($schoolType, 'SMA')) {
                $suffix = 'SMA';
            } elseif (str_contains($schoolType, 'SMK')) {
                $suffix = 'SMK';
            } elseif (str_contains($schoolType, 'SMP')) {
                $suffix = 'SMP';
            }
            
            if (!empty($suffix)) {
                $pattern = '/\b' . preg_quote($suffix, '/') . '\b/i';
                if (!preg_match($pattern, $name)) {
                    $name .= ' ' . $suffix;
                }
            }
        }
        
        return $name;
    }

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'employee_positions')
            ->withPivot([
                'start_date',
                'end_date',
                'sk_number',
                'sk_date',
                'notes',
                'is_primary',
                'pkl_supervisor_hours',
                'pkl_honor_rate',
            ])
            ->withTimestamps();
    }

    // Active employees in this position
    public function activeEmployees(): BelongsToMany
    {
        return $this->employees()
            ->wherePivotNull('end_date');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeGlobal($query)
    {
        return $query->whereNull('school_id');
    }

    public function scopeBySchool($query, $schoolId)
    {
        return $query->where(function ($q) use ($schoolId) {
            $q->whereNull('school_id')
              ->orWhere('school_id', $schoolId);
        });
    }

    public function scopeStructural($query)
    {
        return $query->where('position_category', 'structural');
    }

    public function scopeFunctional($query)
    {
        return $query->where('position_category', 'functional');
    }
}
