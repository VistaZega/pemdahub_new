<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Support\Facades\Storage;

class Teacher extends Model
{
    use HasFactory;

    // ====================================================================
    // BIODATA DELEGATION: Employee sebagai Single Source of Truth
    // ====================================================================
    // Kolom biodata (full_name, gender, birth_place, birth_date, religion,
    // address, phone, photo) ada di KEDUA tabel teachers dan employees.
    // Untuk mencegah "data drift" (nama di Rapor berbeda dengan Slip Gaji),
    // accessor ini mendelegasikan pembacaan ke tabel employees.
    // Fallback ke kolom lokal jika employee belum ter-link.
    // ====================================================================

    /**
     * Daftar kolom biodata yang didelegasikan ke Employee.
     */
    public const DELEGATED_BIODATA_FIELDS = [
        'full_name',
        'gender',
        'birth_place',
        'birth_date',
        'religion',
        'address',
        'phone',
        'photo',
        'is_active',
    ];

    /**
     * Get full_name dari Employee (source of truth), fallback ke kolom lokal.
     */
    public function getFullNameAttribute(): string
    {
        return $this->employee?->full_name
            ?? $this->attributes['full_name']
            ?? '';
    }

    /**
     * Get gender dari Employee (source of truth), fallback ke kolom lokal.
     */
    public function getGenderAttribute(): ?string
    {
        return $this->employee?->gender
            ?? $this->attributes['gender']
            ?? null;
    }

    /**
     * Get birth_place dari Employee (source of truth), fallback ke kolom lokal.
     */
    public function getBirthPlaceAttribute(): ?string
    {
        return $this->employee?->birth_place
            ?? $this->attributes['birth_place']
            ?? null;
    }

    /**
     * Get birth_date dari Employee (source of truth), fallback ke kolom lokal.
     */
    public function getBirthDateAttribute()
    {
        $value = $this->employee?->getRawOriginal('birth_date')
            ?? $this->attributes['birth_date']
            ?? null;

        return $value ? \Illuminate\Support\Carbon::parse($value) : null;
    }

    /**
     * Get religion dari Employee (source of truth), fallback ke kolom lokal.
     */
    public function getReligionAttribute(): ?string
    {
        return $this->employee?->religion
            ?? $this->attributes['religion']
            ?? null;
    }

    /**
     * Get address dari Employee (source of truth), fallback ke kolom lokal.
     */
    public function getAddressAttribute(): ?string
    {
        return $this->employee?->address
            ?? $this->attributes['address']
            ?? null;
    }

    /**
     * Get phone dari Employee (source of truth), fallback ke kolom lokal.
     */
    public function getPhoneAttribute(): ?string
    {
        return $this->employee?->phone
            ?? $this->attributes['phone']
            ?? null;
    }

    /**
     * Get photo dari Employee (source of truth), fallback ke kolom lokal.
     */
    public function getPhotoAttribute(): ?string
    {
        return $this->employee?->getRawOriginal('photo')
            ?? $this->attributes['photo']
            ?? null;
    }

    /**
     * Get is_active dari Employee (source of truth), fallback ke kolom lokal.
     */
    public function getIsActiveAttribute(): bool
    {
        if ($this->employee) {
            return (bool) $this->employee->is_active;
        }

        return (bool) ($this->attributes['is_active'] ?? true);
    }

    /**
     * Get the teacher's photo URL.
     * Returns the default photo if no photo is uploaded.
     */
    public function getPhotoUrlAttribute(): string
    {
        $photo = $this->photo; // Sudah didelegasikan ke Employee via accessor

        if ($photo && Storage::disk('public')->exists($photo)) {
            return asset('storage/' . $photo);
        }

        return asset('images/default-student.jpg');
    }


    public $timestamps = false;

    protected $fillable = [
        'employee_id',
        'user_id',
        'school_id',
        'teacher_code',
        'full_name',
        'gender',
        'education_level',
        'major',
        'birth_place',
        'birth_date',
        'religion',
        'address',
        'phone',
        'photo',
        'position',
        'is_active',
    ];
        public function cbtExams()
        {
            return $this->hasMany(CbtExam::class, 'teacher_id');
        }

        public function cbtQuestionBanks()
        {
            return $this->hasMany(CbtQuestionBank::class, 'teacher_id');
        }

        public function knowledgeMaterials()
        {
            return $this->hasMany(KnowledgeMaterial::class, 'teacher_id');
        }

    protected $casts = [
        'birth_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Relationship: Teacher belongs to Employee (NEW)
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Relationship: Guru belongs to User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: Guru belongs to School
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Relationship: Guru mengajar Subjects (via schedules) - DEPRECATED
     * Use teachingSubjects() for actual teaching subjects, competentSubjects() for competencies
     */
    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'schedules', 'teacher_id', 'subject_id')->distinct();
    }

    /**
     * Relationship: Kompetensi Guru - Mata Pelajaran yang dikuasai (NEW)
     * Many-to-Many through subject_teacher pivot table
     */
    public function competentSubjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'subject_teacher', 'teacher_id', 'subject_id')
            ->withTimestamps()
            ->orderBy('subject_name');
    }

    /**
     * Helper: Check if teacher is competent in a subject
     */
    public function isCompetentIn($subjectId): bool
    {
        return $this->competentSubjects()->where('subjects.id', $subjectId)->exists();
    }

    /**
     * Relationship: Guru mengajar di Classrooms
     */
    public function classrooms(): BelongsToMany
    {
        return $this->belongsToMany(Classroom::class, 'schedules');
    }

    /**
     * Relationship: Jadwal mengajar
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    /**
     * Relationship: Penugasan mengajar (teaching assignments)
     */
    public function teachingAssignments(): HasMany
    {
        return $this->hasMany(TeachingAssignment::class);
    }

    /**
     * Relationship: LMS Courses
     */
    public function courses(): HasMany
    {
        return $this->hasMany(LmsCourse::class);
    }

    /**
     * Relationship: LMS Courses (alias)
     */
    public function lmsCourses(): HasMany
    {
        return $this->hasMany(LmsCourse::class, 'teacher_id');
    }

    /**
     * Relationship: Rombel yang diampu sebagai Wali Kelas
     */
    public function homeroomClassrooms(): HasMany
    {
        return $this->hasMany(Classroom::class, 'homeroom_teacher_id');
    }

    /**
     * Get school name for display
     */
    public function getSchoolNameAttribute(): string
    {
        return $this->school?->name ?? 'Unknown School';
    }

    /**
     * Helper: Get employee data (NEW)
     */
    public function getEmployeeData(): ?Employee
    {
        return $this->employee;
    }

    /**
     * Helper: Get active positions (NEW)
     */
    public function getActivePositions()
    {
        return $this->employee?->activePositions() ?? collect();
    }

    /**
     * Helper: Get total allowance (NEW)
     */
    public function getTotalAllowance(): float
    {
        return $this->employee?->getTotalAllowance() ?? 0;
    }

    /**
     * Helper: Dapatkan Total Jam Pelajaran (JP) Penugasan Pembimbing PKL
     */
    public function getPklSupervisorHours(?int $academicYearId = null): int
    {
        $employee = $this->employee;
        if (!$employee && $this->user_id) {
            $employee = \App\Models\Employee::where('user_id', $this->user_id)->first();
        }
        if (!$employee) {
            return 0;
        }

        $activeYearId = $academicYearId ?? \App\Models\AcademicYear::where('is_active', true)->value('id');

        // 1. Cek dari employee_positions
        $hours = (int) \Illuminate\Support\Facades\DB::table('employee_positions')
            ->join('positions', 'positions.id', '=', 'employee_positions.position_id')
            ->where('employee_positions.employee_id', $employee->id)
            ->where(function($q) use ($activeYearId) {
                if ($activeYearId) {
                    $q->where('employee_positions.academic_year_id', $activeYearId)
                      ->orWhereNull('employee_positions.academic_year_id');
                }
            })
            ->whereNull('employee_positions.end_date')
            ->where(function($q) {
                $q->where('positions.position_code', 'LIKE', '%PKL%')
                  ->orWhere('positions.position_name', 'LIKE', '%PKL%')
                  ->orWhere('employee_positions.pkl_supervisor_hours', '>', 0);
            })
            ->sum('employee_positions.pkl_supervisor_hours');

        if ($hours > 0) {
            return $hours;
        }

        // 2. Cek dari EmployeeWorkloadSummary
        if ($activeYearId) {
            $summaryHours = \Illuminate\Support\Facades\DB::table('employee_workload_summaries')
                ->where('employee_id', $employee->id)
                ->where('academic_year_id', $activeYearId)
                ->value('pkl_supervisor_hours');
            if ($summaryHours) {
                return (int) $summaryHours;
            }
        }

        return 0;
    }

    /**
     * Relationship: Guru memiliki banyak Penempatan PKL
     */
    public function pklPlacements(): HasMany
    {
        return $this->hasMany(PklPlacement::class, 'teacher_id');
    }

    /**
     * Relationship: Guru memiliki banyak Laporan Monitoring PKL
     */
    public function pklMonitorings(): HasMany
    {
        return $this->hasMany(PklMonitoring::class, 'teacher_id');
    }

    /**
     * Relationship: Sekolah tambahan tempat guru mengajar (Multi-School)
     * Melalui tabel pivot teacher_schools
     */
    public function additionalSchools(): BelongsToMany
    {
        return $this->belongsToMany(School::class, 'teacher_schools')
                    ->withPivot('is_primary')
                    ->withTimestamps();
    }

    /**
     * Helper: Dapatkan semua sekolah tempat guru mengajar (sekolah utama + tambahan + dari penugasan/LMS/jadwal)
     */
    public function allSchools()
    {
        $schools = collect();

        // 1. Sekolah utama dari kolom school_id
        if ($this->school) {
            $schools->push($this->school);
        }

        // 2. Sekolah dari user
        if ($this->user && $this->user->school && !$schools->contains('id', $this->user->school_id)) {
            $schools->push($this->user->school);
        }

        // 3. Sekolah tambahan dari pivot teacher_schools
        try {
            $additional = $this->additionalSchools()->get();
            foreach ($additional as $addSchool) {
                if (!$schools->contains('id', $addSchool->id)) {
                    $schools->push($addSchool);
                }
            }
        } catch (\Throwable $e) {}

        // 4. Sekolah dari Teaching Assignments (Pembagian Tugas Mengajar)
        try {
            $taSchools = TeachingAssignment::where('teacher_id', $this->id)
                ->where('is_active', true)
                ->whereHas('classroom.school')
                ->with('classroom.school')
                ->get()
                ->pluck('classroom.school')
                ->filter();
            foreach ($taSchools as $taSchool) {
                if (!$schools->contains('id', $taSchool->id)) {
                    $schools->push($taSchool);
                }
            }
        } catch (\Throwable $e) {}

        // 5. Sekolah dari Schedules (Jadwal Mengajar Harian)
        try {
            $schedSchools = Schedule::where('teacher_id', $this->id)
                ->whereHas('school')
                ->with('school')
                ->get()
                ->pluck('school')
                ->filter();
            foreach ($schedSchools as $sSchool) {
                if (!$schools->contains('id', $sSchool->id)) {
                    $schools->push($sSchool);
                }
            }
        } catch (\Throwable $e) {}

        // 6. Sekolah dari LMS Courses & LMS Classes
        try {
            $lmsSchools = LmsCourse::where('teacher_id', $this->id)
                ->with('classes.school')
                ->get()
                ->flatMap->classes
                ->pluck('school')
                ->filter();
            foreach ($lmsSchools as $lmsSchool) {
                if (!$schools->contains('id', $lmsSchool->id)) {
                    $schools->push($lmsSchool);
                }
            }
        } catch (\Throwable $e) {}

        // 7. Sekolah dari Wali Kelas (Homeroom Classroom)
        try {
            $hrSchools = Classroom::where('homeroom_teacher_id', $this->id)
                ->whereHas('school')
                ->with('school')
                ->get()
                ->pluck('school')
                ->filter();
            foreach ($hrSchools as $hrSchool) {
                if (!$schools->contains('id', $hrSchool->id)) {
                    $schools->push($hrSchool);
                }
            }
        } catch (\Throwable $e) {}

        return $schools->sortBy('name')->values();
    }

    /**
     * Helper: Cek apakah guru boleh mengakses sekolah tertentu
     */
    public function canAccessSchool($schoolId): bool
    {
        if (!$schoolId) return false;
        return $this->allSchools()->contains('id', (int) $schoolId);
    }

    /**
     * Helper: Dapatkan seluruh ID profil guru milik pengguna yang sama (misal multi-unit SMK & SMP)
     */
    public function allTeacherIds(): array
    {
        $ids = [$this->id];
        if ($this->user_id) {
            $uIds = self::where('user_id', $this->user_id)->pluck('id')->toArray();
            $ids = array_merge($ids, $uIds);
        }
        if ($this->employee_id) {
            $eIds = self::where('employee_id', $this->employee_id)->pluck('id')->toArray();
            $ids = array_merge($ids, $eIds);
        }
        return array_unique(array_filter($ids));
    }

    /**
     * Accessor for name -> alias for full_name
     */
    public function getNameAttribute(): string
    {
        return $this->full_name ?? '';
    }

    /**
     * Accessor for nip -> alias for teacher_code
     */
    public function getNipAttribute(): ?string
    {
        return $this->teacher_code ?? null;
    }
}

