<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Student extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'user_id',
        'school_id',
        'nisn',
        'nis',
        'full_name',
        'gender',
        'birth_place',
        'birth_date',
        'religion',
        'address',
        'phone',
        'photo',
        'parent_name',
        'parent_phone',
        'previous_school',
        'guardian_name',
        'guardian_phone',
        'guardian_occupation',
        'guardian_address',
        'hobby',
        'health_history',
        'entry_year',
        'graduation_year',
        'status',
        'rfid_uid',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'entry_year' => 'integer',
        'graduation_year' => 'integer',
    ];

    /**
     * Get clean formatted NIS (cleans Excel float import commas like 12607,25 -> 12607/25 or 12607,00 -> 12607).
     */
    public function getFormattedNisAttribute(): string
    {
        if (empty($this->nis)) return '-';
        $nis = trim((string)$this->nis);
        
        // Bersihkan desimal nol (misal: 12607,00 atau 12607.0 -> 12607)
        if (preg_match('/^(\d+)[,.](0|00)$/', $nis, $m)) {
            return $m[1];
        }
        
        // Ubah format koma menjadi garis miring resmi (misal: 12607,25 -> 12607/25)
        if (str_contains($nis, ',')) {
            $parts = explode(',', $nis);
            if (count($parts) === 2) {
                return trim($parts[0]) . '/' . trim($parts[1]);
            }
        }
        
        return $nis;
    }

    /**
     * Relationship: Siswa belongs to User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: Siswa belongs to School
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Relationship: Applicant / Pendaftaran PSB
     */
    public function applicant()
    {
        return $this->hasOne(Applicant::class, 'student_id');
    }

    /**
     * Relationship: Siswa belongs to Classroom (direct assignment)
     */
    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    /**
     * Relationship: Siswa di dalam Classrooms
     */
    public function classrooms()
    {
        return $this->belongsToMany(Classroom::class, 'student_classes')
            ->withPivot('academic_year_id', 'status', 'promoted_at')
            ->withTimestamps();
    }

    /**
     * Relationship: Student Classes (pivot records)
     */
    public function studentClasses()
    {
        return $this->hasMany(StudentClass::class);
    }

    /**
     * Relationship: Latest Student Class (pivot record)
     */
    public function studentClass()
    {
        return $this->hasOne(StudentClass::class)->latestOfMany();
    }

    /**
     * Relationship: Siswa Current Classroom (active year)
     */
    public function currentClassroom()
    {
        return $this->belongsToMany(Classroom::class, 'student_classes')
            ->where('student_classes.academic_year_id', function ($query) {
                $query->select('id')
                    ->from('academic_years')
                    ->where('is_active', true)
                    ->limit(1);
            })
            ->wherePivot('status', 'aktif');
    }

    /**
     * Relationship: Absensi
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Relationship: Nilai/Grades
     */
    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    /**
     * Relationship: Final Grades
     */
    public function finalGrades()
    {
        return $this->hasMany(FinalGrade::class);
    }

    /**
     * Relationship: Tagihan
     */
    public function bills()
    {
        return $this->hasMany(StudentBill::class, 'student_id');
    }

    public function finalProjectMemberships()
    {
        return $this->hasMany(FinalProjectMember::class, 'student_id');
    }

    public function currentFinalProject()
    {
        // Get the active project through membership
        $membership = $this->finalProjectMemberships()->with('finalProject')->latest()->first();
        if ($membership) {
            return $membership->finalProject;
        }

        // Fallback for tests or projects created directly without membership records
        return FinalProject::where('student_id', $this->id)->latest()->first();
    }

    /**
     * Relationship: Pembayaran
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Relationship: Orang Tua
     */
    public function parents()
    {
        return $this->hasMany(ParentModel::class);
    }

    /**
     * Relationship: LMS Submissions
     */
    public function assignmentSubmissions()
    {
        return $this->hasMany(LmsSubmission::class);
    }

    /**
     * Relationship: Quiz Attempts
     */
    public function quizAttempts()
    {
        return $this->hasMany(LmsQuizAttempt::class);
    }

    // ─── Student Lifecycle Relationships ────────────────────────

    /**
     * Relationship: Status history (full audit trail)
     */
    public function statusHistories()
    {
        return $this->hasMany(StudentStatusHistory::class)
            ->orderByDesc('effective_date')
            ->orderByDesc('id');
    }

    /**
     * Relationship: Promotion records
     */
    public function promotions()
    {
        return $this->hasMany(StudentPromotion::class)
            ->orderByDesc('academic_year_id');
    }

    /**
     * Relationship: Alumni record
     */
    public function alumniRecord()
    {
        return $this->hasOne(Alumni::class);
    }

    // ─── Student Development Relationships ──────────────────────

    /**
     * Relationship: Student Achievements / Prestasi
     */
    public function achievements()
    {
        return $this->hasMany(StudentAchievement::class, 'student_id');
    }

    /**
     * Relationship: Counseling records (konseling, pembinaan, kasus)
     */
    public function counselingRecords()
    {
        return $this->hasMany(StudentCounselingRecord::class);
    }

    /**
     * Relationship: Recommendations from staff
     */
    public function recommendations()
    {
        return $this->hasMany(StudentRecommendation::class);
    }

    /**
     * Relationship: Development notes
     */
    public function developmentNotes()
    {
        return $this->hasMany(StudentDevelopmentNote::class);
    }

    // ─── CBT Relationships ──────────────────────────────────────

    /**
     * Relationship: CBT exam sessions
     */
    public function cbtSessions()
    {
        return $this->hasMany(CbtExamSession::class);
    }

    /**
     * Relationship: CBT exam results
     */
    public function cbtResults()
    {
        return $this->hasMany(CbtExamResult::class);
    }

    // ─── Status Helpers ─────────────────────────────────────────

    /**
     * Get status label using unified status list
     */
    public function getStatusLabelAttribute(): string
    {
        return StudentStatusHistory::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    /**
     * Check if student is currently active/enrolled
     */
    public function isActive(): bool
    {
        return in_array($this->status, StudentStatusHistory::ACTIVE_STATUSES);
    }

    /**
     * Check if student status is terminal (no longer enrolled)
     */
    public function isTerminal(): bool
    {
        return in_array($this->status, StudentStatusHistory::TERMINAL_STATUSES);
    }

    /**
     * Accessor: Dapatkan URL foto siswa.
     * Jika foto tidak ada, kembalikan foto default pasphoto.
     *
     * Penggunaan di view: $student->photo_url
     */
    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo && Storage::disk('public')->exists($this->photo)) {
            return asset('storage/' . $this->photo);
        }

        if ($this->applicant && $this->applicant->photo_path && Storage::disk('public')->exists($this->applicant->photo_path)) {
            return asset('storage/' . $this->applicant->photo_path);
        }

        if ($this->user && $this->user->photo && Storage::disk('public')->exists($this->user->photo)) {
            return asset('storage/' . $this->user->photo);
        }

        return asset('images/default-student.jpg');
    }

    /**
     * Transition student to a new status with audit trail.
     */
    public function transitionTo(
        string $newStatus,
        ?string $reason = null,
        ?string $notes = null,
        ?string $documentNumber = null,
        ?int $changedBy = null
    ): StudentStatusHistory {
        $currentStatus = $this->status;

        if (!StudentStatusHistory::isValidTransition($currentStatus, $newStatus)) {
            throw new \InvalidArgumentException(
                "Transisi status tidak valid: {$currentStatus} → {$newStatus}"
            );
        }

        $history = $this->statusHistories()->create([
            'school_id' => $this->school_id,
            'from_status' => $currentStatus,
            'to_status' => $newStatus,
            'reason' => $reason,
            'notes' => $notes,
            'document_number' => $documentNumber,
            'effective_date' => now(),
            'changed_by' => $changedBy ?? auth()->id(),
        ]);

        $this->update(['status' => $newStatus]);

        // Auto-populate alumni if graduated
        if ($newStatus === 'lulus' || $newStatus === 'alumni') {
            $this->createAlumniRecordIfNeeded();
        }

        return $history;
    }

    /**
     * Auto-create and sync alumni records across all tables when student graduates.
     */
    public function createAlumniRecordIfNeeded(): void
    {
        $this->syncAlumniRecords();
    }

    /**
     * Synchronize all alumni representations for this student.
     */
    public function syncAlumniRecords(): void
    {
        // 1. Resolve final classroom accurately from pivot history
        $latestPivot = $this->studentClasses()->with('classroom')->latest('id')->first();
        $finalClassName = $latestPivot?->classroom?->class_name 
            ?? $this->currentClassroom()->first()?->class_name;

        // 2. Resolve entry_year & graduation_year (SMP/SMA/SMK default 3 years duration)
        $gradYear = $this->graduation_year ?? now()->year;
        $entryYear = $this->entry_year;

        if (!$entryYear || $entryYear >= $gradYear || ($gradYear - $entryYear) < 2) {
            $entryYear = $gradYear - 3;
            $this->update(['entry_year' => $entryYear]);
        }

        if (!$this->graduation_year) {
            $this->update(['graduation_year' => $gradYear]);
        }

        // 3. Record in `alumni` table
        $alumniRec = $this->alumniRecord()->first();
        if (!$alumniRec) {
            Alumni::create([
                'student_id' => $this->id,
                'school_id' => $this->school_id,
                'nisn' => $this->nisn,
                'nis' => $this->nis,
                'full_name' => $this->full_name,
                'gender' => $this->gender,
                'birth_place' => $this->birth_place,
                'birth_date' => $this->birth_date,
                'religion' => $this->religion,
                'phone' => $this->phone,
                'entry_year' => $entryYear,
                'graduation_year' => $gradYear,
                'final_class' => $finalClassName,
                'moved_at' => now(),
            ]);
        } else {
            $alumniRec->update([
                'entry_year' => $entryYear,
                'graduation_year' => $gradYear,
                'final_class' => $alumniRec->final_class ?: $finalClassName,
            ]);
        }

        // 4. Record in `alumni_profiles` table (for Tracer Study)
        AlumniProfile::updateOrCreate(
            ['student_id' => $this->id],
            [
                'school_id' => $this->school_id,
                'full_name' => $this->full_name,
                'graduation_year' => $gradYear,
                'phone' => $this->phone,
                'email' => $this->user?->email,
            ]
        );

        // 5. User Role Update
        if ($this->user && $this->user->role === 'siswa') {
            $this->user->update(['role' => 'alumni']);
        }

        // 6. Record in `alumni_directories` table (for IKA PEMBDA Directory)
        if ($this->user_id) {
            AlumniDirectory::updateOrCreate(
                ['user_id' => $this->user_id],
                [
                    'full_name' => $this->full_name,
                    'gender' => $this->gender ?? 'L',
                    'address' => $this->address ?? '-',
                    'phone' => $this->phone,
                    'email' => $this->user?->email,
                    'school_id' => $this->school_id,
                    'graduation_year' => $gradYear,
                    'last_class' => $finalClassName,
                    'is_approved' => true,
                ]
            );
        }
    }

    /**
     * Get total outstanding bills
     */
    public function getTotalOutstanding(): float
    {
        return $this->bills()
            ->where('status', '!=', 'lunas')
            ->sum(\DB::raw('amount - paid_amount'));
    }

    // ─── Scopes ─────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->whereIn('status', StudentStatusHistory::ACTIVE_STATUSES);
    }

    public function scopeAlumni($query)
    {
        return $query->whereIn('status', ['lulus', 'alumni']);
    }

    public function scopeBySchool($query, int $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }
}
