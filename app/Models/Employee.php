<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'user_id',
        'employee_code',
        'nip',
        'nuptk',
        'full_name',
        'gender',
        'birth_place',
        'birth_date',
        'religion',
        'address',
        'phone',
        'email',
        'photo',
        'employee_type',
        'employment_status',
        'marital_status',
        'children_count',
        'tmt_date',
        'end_date',
        'basic_salary',
        'bank_name',
        'bank_account',
        'bank_account_name',
        'rfid_uid',
        'is_active',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'tmt_date' => 'date',
        'end_date' => 'date',
        'basic_salary' => 'decimal:2',
        'is_active' => 'boolean',
        'children_count' => 'integer',
    ];

    // Relationships
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function teacher(): HasOne
    {
        return $this->hasOne(Teacher::class);
    }

    // Many-to-many dengan positions
    public function positions(): BelongsToMany
    {
        return $this->belongsToMany(Position::class, 'employee_positions')
            ->withPivot([
                'academic_year_id',
                'classroom_id',
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
            ])
            ->withTimestamps();
    }

    // Active positions only
    public function activePositions(): BelongsToMany
    {
        return $this->positions()
            ->where(function ($q) {
                $q->whereNull('employee_positions.end_date')
                  ->orWhere('employee_positions.end_date', '>=', now()->toDateString());
            })
            ->orderByPivot('is_primary', 'desc');
    }

    // Position history
    public function positionHistory(): HasMany
    {
        return $this->hasMany(EmployeePosition::class)
            ->orderBy('start_date', 'desc');
    }

    // Alias for positionHistory (for consistency with controller)
    public function employeePositions(): HasMany
    {
        return $this->hasMany(EmployeePosition::class);
    }

    // Helper Methods
    public function isTeacher(): bool
    {
        return $this->employee_type === 'guru';
    }

    public function getWorkingYears(): int
    {
        return $this->tmt_date ? now()->diffInYears($this->tmt_date) : 0;
    }

    public function getTotalAllowance(): float
    {
        return $this->activePositions()
            ->sum('positions.allowance_amount');
    }

    public function getPrimaryPosition(): ?Position
    {
        return $this->activePositions()
            ->wherePivot('is_primary', true)
            ->first();
    }

    /**
     * Get the employee's photo URL.
     * Returns the default photo if no photo is uploaded.
     */
    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->photo)) {
            return asset('storage/' . $this->photo);
        }

        return asset('images/default-student.jpg');
    }


    // ====== NEW: HR Module Relationships ======

    public function leaves(): HasMany
    {
        return $this->hasMany(EmployeeLeave::class);
    }

    public function employeeAttendances(): HasMany
    {
        return $this->hasMany(EmployeeAttendance::class);
    }

    public function educations(): HasMany
    {
        return $this->hasMany(EmployeeEducation::class)->orderBy('graduation_year', 'desc');
    }

    public function trainings(): HasMany
    {
        return $this->hasMany(EmployeeTraining::class)->orderBy('start_date', 'desc');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function familyMembers(): HasMany
    {
        return $this->hasMany(EmployeeFamilyMember::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(EmployeeContract::class)->orderBy('start_date', 'desc');
    }

    public function activeContract()
    {
        return $this->hasOne(EmployeeContract::class)->where('is_active', true)->latestOfMany('start_date');
    }

    // ====== NEW: Cross-School & Yayasan Helpers ======

    /**
     * Get schools where this employee teaches (may differ from home school).
     */
    public function getTeachingSchools()
    {
        if (!$this->teacher) {
            return collect();
        }

        return School::whereHas('classrooms.teachingAssignments', function ($q) {
            $q->where('teacher_id', $this->teacher->id)->where('is_active', true);
        })->get();
    }

    /**
     * Check if this employee is a foundation (yayasan) staff member.
     */
    public function isYayasanStaff(): bool
    {
        return $this->school?->isYayasan() ?? false;
    }

    /**
     * Get the highest education level.
     */
    public function getHighestEducation(): ?EmployeeEducation
    {
        return $this->educations()->orderByRaw("FIELD(education_level, 'S3','S2','S1','D4','D3','D2','D1','SMA','SMP','SD') ASC")->first();
    }

    /**
     * Get children count from family members table (fallback to children_count column).
     */
    public function getActualChildrenCount(): int
    {
        $fromFamily = $this->familyMembers()->where('relation', 'anak')->count();
        return $fromFamily > 0 ? $fromFamily : ($this->children_count ?? 0);
    }

    /**
     * Get approved leave days used in a given year.
     */
    public function getLeaveDaysUsed(int $year): int
    {
        return $this->leaves()
            ->where('status', 'approved')
            ->whereYear('start_date', $year)
            ->sum('days_count');
    }

    /**
     * Check if this employee is a Kepala Sekolah (Principal).
     */
    public function isKepalaSekolah(): bool
    {
        if ($this->user && $this->user->isKepalaSekolah()) {
            return true;
        }

        if ($this->teacher && School::where('principal_id', $this->teacher->id)->exists()) {
            return true;
        }

        return $this->positions()
            ->where(function ($q) {
                $q->where(function ($sq) {
                    $sq->where('position_name', 'LIKE', '%Kepala Sekolah%')
                      ->orWhere('position_name', 'LIKE', '%Kepala SMA%')
                      ->orWhere('position_name', 'LIKE', '%Kepala SMK%')
                      ->orWhere('position_name', 'LIKE', '%Kepala SMP%')
                      ->orWhere('position_name', 'LIKE', '%Kepsek%')
                      ->orWhere('position_code', 'LIKE', '%KEPSEK%')
                      ->orWhere('position_code', 'KS');
                })
                ->where('position_name', 'NOT LIKE', '%Pembantu%')
                ->where('position_name', 'NOT LIKE', '%PKS%')
                ->where('position_name', 'NOT LIKE', '%Wakil%')
                ->where('position_name', 'NOT LIKE', '%Wakasek%')
                ->where('position_code', 'NOT LIKE', '%PKS%')
                ->where('position_code', 'NOT LIKE', '%WAKASEK%');
            })
            ->exists();
    }

    // ====== Scopes ======

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->whereNull('end_date');
    }

    public function scopeTeachers($query)
    {
        return $query->where('employee_type', 'guru');
    }

    public function scopeStaff($query)
    {
        return $query->where('employee_type', '!=', 'guru');
    }

    public function scopeBySchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    public function scopeYayasanStaff($query)
    {
        return $query->whereHas('school', fn($q) => $q->where('type', 'yayasan'));
    }
}

