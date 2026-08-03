<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeWorkloadSummary extends Model
{
    use HasFactory;

    protected $table = 'employee_workload_summaries';

    protected $fillable = [
        'employee_id',
        'academic_year_id',
        'semester_id',
        'total_position_count',
        'total_position_allowance',
        'total_teaching_hours',
        'total_teaching_classes',
        'total_teaching_subjects',
        'total_teaching_allowance',
        'honor_pkl',
        'pkl_supervisor_hours',
        'family_allowance',
        'child_allowance',
        'rice_allowance',
        'bpjs_kesehatan',
        'bpjs_ketenagakerjaan',
        'total_deductions',
        'total_allowance',
        'basic_salary',
        'gross_pay',
        'total_compensation',
        'status',
        'confirmed_by',
        'confirmed_at',
        'notes',
    ];

    protected $casts = [
        'total_position_allowance' => 'float',
        'total_teaching_hours' => 'integer',
        'total_teaching_classes' => 'integer',
        'total_teaching_subjects' => 'integer',
        'total_teaching_allowance' => 'float',
        'honor_pkl' => 'float',
        'pkl_supervisor_hours' => 'integer',
        'family_allowance' => 'float',
        'child_allowance' => 'float',
        'rice_allowance' => 'float',
        'bpjs_kesehatan' => 'float',
        'bpjs_ketenagakerjaan' => 'float',
        'total_deductions' => 'float',
        'total_allowance' => 'float',
        'basic_salary' => 'float',
        'gross_pay' => 'float',
        'total_compensation' => 'float',
        'confirmed_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function confirmedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }
}
