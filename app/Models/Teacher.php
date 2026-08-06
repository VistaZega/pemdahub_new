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

    /**
     * Get the teacher's photo URL.
     * Returns the default photo if no photo is uploaded.
     */
    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo && Storage::disk('public')->exists($this->photo)) {
            return asset('storage/' . $this->photo);
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
     * Helper: Dapatkan semua sekolah tempat guru mengajar (sekolah utama + tambahan)
     */
    public function allSchools()
    {
        $schools = collect();

        // Sekolah utama dari kolom school_id
        if ($this->school) {
            $schools->push($this->school);
        }

        // Sekolah tambahan dari pivot
        $additional = $this->additionalSchools()->get();
        foreach ($additional as $addSchool) {
            if (!$schools->contains('id', $addSchool->id)) {
                $schools->push($addSchool);
            }
        }

        return $schools;
    }

    /**
     * Helper: Cek apakah guru boleh mengakses sekolah tertentu
     */
    public function canAccessSchool($schoolId): bool
    {
        // Sekolah utama
        if ($this->school_id == $schoolId) {
            return true;
        }

        // Sekolah tambahan
        return $this->additionalSchools()->where('schools.id', $schoolId)->exists();
    }
}

