<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\LogsActivity;

class User extends Authenticatable
{
    use HasFactory, Notifiable, LogsActivity;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'school_id',
        'photo',
        'is_active',
        'last_login',
        'must_change_password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_login' => 'datetime',
        'must_change_password' => 'boolean',
    ];

    /**
     * Relationship: User belongs to School
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Relationship: User dapat menjadi Guru
     */
    public function teacher()
    {
        return $this->hasOne(Teacher::class);
    }

    /**
     * Relationship: User dapat menjadi Siswa
     */
    public function student()
    {
        return $this->hasOne(Student::class);
    }

    /**
     * Relationship: User dapat menjadi Orang Tua
     */
    public function parents()
    {
        return $this->hasMany(ParentModel::class);
    }

    /**
     * Relationship: Activity logs
     */
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Relationship: Login history
     */
    public function loginHistory()
    {
        return $this->hasMany(LoginHistory::class);
    }

    /**
     * Relationship: Messages sent
     */
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Relationship: Messages received
     */
    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'recipient_id');
    }

    /**
     * Relationship: Notifications
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Relationship: Reputation standing
     */
    public function reputation()
    {
        return $this->hasOne(Reputation::class);
    }

    /**
     * Relationship: Reputation point logs
     */
    public function reputationLogs()
    {
        return $this->hasMany(ReputationLog::class);
    }

    /**
     * Relationship: Earned badges
     */
    public function badges()
    {
        return $this->belongsToMany(Badge::class, 'user_badges')
                    ->withPivot('earned_at');
    }

    /**
     * Check if user has specific role
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user has any of the specified roles
     */
    public function hasAnyRole(array $roles): bool
    {
        if (in_array($this->role, $roles)) {
            return true;
        }
        
        // Cek jabatan dinamis
        if (in_array('admin_sekolah', $roles) && $this->isAdminSekolah()) {
            return true;
        }

        if (in_array('kepala_sekolah', $roles) && $this->isKepalaSekolah()) {
            return true;
        }
        
        if (in_array('bendahara', $roles) && $this->isBendahara()) {
            return true;
        }

        if (in_array('panitia_cbt', $roles) && $this->isPanitiaCbt()) {
            return true;
        }

        if (in_array('panitia_pkl', $roles) && $this->isPanitiaPkl()) {
            return true;
        }

        if (in_array('panitia_ta', $roles) && $this->isPanitiaProyek()) {
            return true;
        }

        if ((in_array('pks', $roles) || in_array('piket', $roles)) && $this->isPksOrPiket()) {
            return true;
        }
        
        return false;
    }

    /**
     * Check if user is SuperAdmin or Owner (Bapak Yulianus Zega / Pembuat Aplikasi / Ketua Yayasan)
     */
    public function isOwnerOrSuperAdmin(): bool
    {
        return $this->role === 'superadmin' 
            || $this->role === 'ketua_yayasan'
            || $this->canAccessYayasan() 
            || $this->username === 'yulzega';
    }

    /**
     * Check if user has foundation-wide / all schools access
     * (Super Admin, Ketua Yayasan, atau Owner Yayasan)
     */
    public function canAccessAllSchools(): bool
    {
        return $this->isSuperAdmin() || $this->isKetuaYayasan() || $this->isOwnerOrSuperAdmin();
    }

    /**
     * Check if user is SuperAdmin
     */
    public function isSuperAdmin(): bool
    {
        if (session()->has('active_role')) {
            return session('active_role') === 'superadmin';
        }
        return $this->hasRole('superadmin') || $this->isOwnerOrSuperAdmin();
    }

    /**
     * Check if user is Admin Sekolah
     */
    public function isAdminSekolah(): bool
    {
        if ($this->isOwnerOrSuperAdmin()) {
            return false;
        }
        return $this->hasRole('admin_sekolah') || session('active_role') === 'admin_sekolah' || $this->isSecondaryAdminSekolah();
    }

    /**
     * Check if user has secondary role/duty as Admin Sekolah
     */
    public function isSecondaryAdminSekolah(): bool
    {
        if ($this->hasRole('admin_sekolah')) {
            return true;
        }

        // Cek tugas tambahan di employee_positions / special duties (misal Operator, Admin, TU)
        if ($this->hasSpecialDuty(['ADMIN', 'OPERATOR', 'OPS', 'TU', 'ADMINISTRATOR'])) {
            return true;
        }

        // Cek posisi di tabel teachers / employees
        if ($this->teacher && $this->teacher->position && preg_match('/(admin|operator|tu|tata usaha)/i', $this->teacher->position)) {
            return true;
        }

        $employee = $this->employee ?? $this->teacher?->employee;
        if ($employee && $employee->position && preg_match('/(admin|operator|tu|tata usaha)/i', $employee->position)) {
            return true;
        }

        return false;
    }

    /**
     * Check if user is Kepala Sekolah
     * Bisa dari role eksplisit 'kepala_sekolah' atau dari penunjukan struktural di tabel schools
     */
    public function isKepalaSekolah(): bool
    {
        if ($this->hasRole('kepala_sekolah') || session('active_role') === 'kepala_sekolah') {
            return true;
        }

        // Cek 1: Apakah user ini Ibu Agustiani (Kepala SMKS Swasta Pembda Nias)
        $name = strtolower($this->name ?? '');
        $username = strtolower($this->username ?? '');
        $email = strtolower($this->email ?? '');
        if (str_contains($name, 'agustiani') || str_contains($username, 'agustiani') || str_contains($email, 'agustiani')) {
            return true;
        }

        // Cek 2: Apakah principal_id di tabel schools menunjuk ke guru ini
        if ($this->teacher) {
            if (School::where('principal_id', $this->teacher->id)->where('type', '!=', 'YAYASAN')->exists()) {
                return true;
            }

            // Cek 3: Posisi di tabel teachers
            $pos = strtolower($this->teacher->position ?? '');
            if (
                !str_contains($pos, 'pembantu') && 
                !str_contains($pos, 'wakil') && 
                !str_contains($pos, 'pks') && 
                !str_contains($pos, 'wakasek') && 
                (str_contains($pos, 'kepala sekolah') || str_contains($pos, 'kepsek') || str_contains($pos, 'kasek') || str_contains($pos, 'principal'))
            ) {
                return true;
            }
        }

        // Cek 4: Posisi di tabel employees
        if ($this->employee) {
            $empPos = strtolower($this->employee->position ?? '');
            if (
                !str_contains($empPos, 'pembantu') && 
                !str_contains($empPos, 'wakil') && 
                !str_contains($empPos, 'pks') && 
                !str_contains($empPos, 'wakasek') && 
                (str_contains($empPos, 'kepala sekolah') || str_contains($empPos, 'kepsek') || str_contains($empPos, 'kasek') || str_contains($empPos, 'principal'))
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user is Guru
     */
    public function isGuru(): bool
    {
        return $this->hasRole('guru') || session('active_role') === 'guru' || (bool) $this->teacher;
    }

    /**
     * Check if user is Pegawai
     */
    public function isPegawai(): bool
    {
        return $this->hasRole('pegawai') || session('active_role') === 'pegawai' || (bool) $this->employee;
    }

    /**
     * Check if user is Siswa
     */
    public function isSiswa(): bool
    {
        return $this->hasRole('siswa') || session('active_role') === 'siswa';
    }

    /**
     * Check if user is Orang Tua
     */
    public function isOrangTua(): bool
    {
        return $this->hasRole('orang_tua') || session('active_role') === 'orang_tua';
    }

    /**
     * Check if user is eligible to access/switch to Yayasan mode.
     * Khusus Yayasan (misal Yulianus Zega / role ketua_yayasan).
     */
    public function canAccessYayasan(): bool
    {
        if ($this->email === 'erwinsm@pembdahub.com') {
            return false;
        }
        return $this->role === 'ketua_yayasan' 
            || $this->username === 'yulzega' 
            || str_contains(strtolower($this->email), 'yulianus')
            || str_contains(strtolower($this->email), 'yulzega');
    }

    /**
     * Check if user is Ketua Yayasan (Foundation Chairman)
     */
    public function isKetuaYayasan(): bool
    {
        if (session('active_role') === 'superadmin') {
            return false;
        }
        if (session('active_role') === 'ketua_yayasan') {
            return true;
        }
        if (!$this->canAccessYayasan()) {
            return false;
        }
        if ($this->hasRole('ketua_yayasan')) {
            return true;
        }
        if ($this->isSuperAdmin()) {
            return false;
        }
        return $this->username === 'yulzega';
    }

    /**
     * Alias for isKetuaYayasan()
     */
    public function isYayasan(): bool
    {
        return $this->isKetuaYayasan();
    }

    /**
     * Check if user is Bendahara (Treasurer)
     */
    public function isBendahara(): bool
    {
        return $this->hasRole('bendahara') || session('active_role') === 'bendahara';
    }

    /**
     * Check if user can manage/view general employment data (Status Kepegawaian, TMT, Jenis Pegawai)
     */
    public function canManageEmploymentData(): bool
    {
        return $this->isSuperAdmin() || $this->isAdminSekolah();
    }

    /**
     * Check if user can manage/view basic salary (Gaji Pokok)
     */
    public function canManageBasicSalary(): bool
    {
        return $this->isSuperAdmin() || $this->isKetuaYayasan();
    }

    /**
     * Check if user is a homeroom teacher (wali kelas)
     */
    public function isHomeroomTeacher(): bool
    {
        if (!$this->isGuru() || !$this->teacher) {
            return false;
        }
        
        return Classroom::where('homeroom_teacher_id', $this->teacher->id)->exists();
    }

    /**
     * Helper untuk mengecek apakah guru memiliki tugas tambahan / kepanitiaan tertentu
     */
    public function hasSpecialDuty(array $keywords): bool
    {
        if (!$this->employee) {
            return false;
        }
        
        return $this->employee->activePositions()->where(function ($query) use ($keywords) {
            foreach ($keywords as $keyword) {
                $query->orWhere('positions.position_code', 'like', "%{$keyword}%")
                      ->orWhere('positions.position_name', 'like', "%{$keyword}%");
            }
        })->exists();
    }

    public function isPanitiaCbt(): bool
    {
        return $this->role === 'panitia_cbt' || $this->hasSpecialDuty(['CBT']);
    }

    public function isPanitiaPkl(): bool
    {
        return $this->role === 'panitia_pkl' || $this->hasSpecialDuty(['PKL', 'HUBIN', 'PRAKERIN']);
    }

    public function isPanitiaProyek(): bool
    {
        return $this->role === 'panitia_ta' || $this->hasSpecialDuty(['PROYEK', 'PROJECT', 'PROJEK', 'TA', 'TUGAS AKHIR', 'TUGAS-AKHIR', 'PENELITIAN', 'KTI', 'KARYA TULIS']);
    }

    /**
     * Check if user can assign teachers as PKL supervisor/advisor
     */
    public function canAssignPklAdvisor(): bool
    {
        return $this->isSuperAdmin() || $this->isAdminSekolah() || $this->isKepalaSekolah() || $this->isPanitiaPkl();
    }

    /**
     * Check if user can assign teachers as Final Project / Research supervisor/advisor
     */
    public function canAssignFinalProjectAdvisor(): bool
    {
        return $this->isSuperAdmin() || $this->isAdminSekolah() || $this->isKepalaSekolah() || $this->isPanitiaProyek();
    }

    public function isPksOrPiket(): bool
    {
        // 1. Direct role check
        if (in_array($this->role, ['pks', 'piket', 'guru_bk', 'bk', 'bimbingan_konseling'])) {
            return true;
        }

        $activeRole = session('active_role');
        if ($activeRole && in_array($activeRole, ['pks', 'piket', 'guru_bk', 'bk', 'bimbingan_konseling'])) {
            return true;
        }

        // 2. Special duty check in employee_positions
        if ($this->hasSpecialDuty(['PKS', 'PIKET', 'DISIPLIN', 'BK', 'BP', 'KONSELING', 'KONSILING', 'BIMBINGAN'])) {
            return true;
        }

        // 3. Check teacher profile for BK position or BK teaching assignments/subjects
        if ($this->teacher) {
            if ($this->teacher->position && preg_match('/(bk|bp|konseling|bimbingan|pks|piket)/i', $this->teacher->position)) {
                return true;
            }

            try {
                if ($this->teacher->teachingAssignments()->whereHas('subject', function ($q) {
                    $q->where('code', 'like', '%BK%')
                      ->orWhere('code', 'like', '%BP%')
                      ->orWhere('name', 'like', '%Bimbingan%')
                      ->orWhere('name', 'like', '%Konseling%')
                      ->orWhere('name', 'like', '%BK%');
                })->exists()) {
                    return true;
                }
            } catch (\Throwable $e) {}

            try {
                if ($this->teacher->competentSubjects()->where(function ($q) {
                    $q->where('code', 'like', '%BK%')
                      ->orWhere('code', 'like', '%BP%')
                      ->orWhere('name', 'like', '%Bimbingan%')
                      ->orWhere('name', 'like', '%Konseling%')
                      ->orWhere('name', 'like', '%BK%');
                })->exists()) {
                    return true;
                }
            } catch (\Throwable $e) {}

            try {
                if ($this->teacher->subjects()->where(function ($q) {
                    $q->where('code', 'like', '%BK%')
                      ->orWhere('code', 'like', '%BP%')
                      ->orWhere('name', 'like', '%Bimbingan%')
                      ->orWhere('name', 'like', '%Konseling%')
                      ->orWhere('name', 'like', '%BK%');
                })->exists()) {
                    return true;
                }
            } catch (\Throwable $e) {}
        }

        // 4. Employee position string
        $employee = $this->employee ?? $this->teacher?->employee;
        if ($employee && $employee->position && preg_match('/(bk|bp|konseling|bimbingan|pks|piket)/i', $employee->position)) {
            return true;
        }

        return false;
    }

    /**
     * Get classrooms where user is homeroom teacher
     * Returns a Collection of Classroom models
     */
    public function homeroomClassrooms()
    {
        if (!$this->isGuru() || !$this->teacher) {
            return collect([]);
        }
        
        return Classroom::where('homeroom_teacher_id', $this->teacher->id)
            ->where('is_active', true)
            ->get();
    }

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->is_active === true;
    }

    /**
     * Update last login timestamp
     */
    public function updateLastLogin(): void
    {
        $this->update(['last_login' => now()]);
    }

    /**
     * Check if user is Alumni
     */
    public function isAlumni(): bool
    {
        return $this->hasRole('alumni') || session('active_role') === 'alumni';
    }

    /**
     * Get layout name based on user role
     */
    public function getLayoutAttribute(): string
    {
        $role = session('active_role', $this->role);
        
        return match ($role) {
            'superadmin', 'admin_sekolah', 'kepala_sekolah' => 'layouts.admin',
            'guru', 'pegawai' => 'layouts.guru',
            'siswa' => 'layouts.siswa',
            'orang_tua' => 'layouts.orangtua',
            'bendahara' => 'layouts.treasurer',
            'ketua_yayasan' => 'layouts.yayasan',
            'alumni' => 'layouts.alumni',
            default => 'layouts.app',
        };
    }

    /**
     * Relationship: User has one Employee profile
     */
    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    /**
     * Relationship: User has one AlumniDirectory profile
     */
    public function alumniDirectory()
    {
        return $this->hasOne(AlumniDirectory::class);
    }

    /**
     * Accessor: Get URL for user profile photo / avatar.
     */
    public function getAvatarUrlAttribute(): string
    {
        $photos = [
            $this->photo,
            $this->teacher?->photo,
            $this->employee?->photo,
            $this->student?->photo,
            $this->alumniDirectory?->photo_path,
        ];

        foreach ($photos as $p) {
            if (!empty($p)) {
                if (\Illuminate\Support\Str::startsWith($p, ['http://', 'https://'])) {
                    return $p;
                }
                if (\Illuminate\Support\Str::startsWith($p, ['storage/', '/storage/'])) {
                    return asset(ltrim($p, '/'));
                }
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($p)) {
                    return asset('storage/' . $p);
                }
                if (file_exists(public_path($p))) {
                    return asset($p);
                }
                return asset('storage/' . $p);
            }
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=3b82f6&color=ffffff&bold=true';
    }

    public function getPhotoUrlAttribute(): string
    {
        return $this->avatar_url;
    }

    // ═══════════════ MULTI-SCHOOL SUPPORT ═══════════════

    /**
     * Mendapatkan school_id aktif (dari session atau fallback ke school_id utama)
     */
    public function getActiveSchoolId(): ?int
    {
        $sessionSchoolId = session('active_school_id');

        if ($sessionSchoolId && $this->canAccessSchool($sessionSchoolId)) {
            return (int) $sessionSchoolId;
        }

        return $this->school_id;
    }

    /**
     * Mendapatkan daftar semua sekolah yang bisa diakses user ini
     */
    public function getAvailableSchools()
    {
        // Ketua Yayasan / SuperAdmin → akses semua sekolah
        if ($this->isKetuaYayasan() || $this->isSuperAdmin()) {
            return School::schoolsOnly()->where('is_active', true)->orderBy('name')->get();
        }

        // Guru biasa → sekolah utama + sekolah tambahan dari pivot
        if ($this->teacher) {
            return $this->teacher->allSchools();
        }

        // Fallback → hanya sekolah sendiri
        if ($this->school_id) {
            return School::where('id', $this->school_id)->get();
        }

        return collect();
    }

    /**
     * Cek apakah user ini bisa mengakses sekolah tertentu
     */
    public function canAccessSchool($schoolId): bool
    {
        if (!$schoolId) return false;

        // Ketua Yayasan / SuperAdmin → akses semua
        if ($this->isKetuaYayasan() || $this->isSuperAdmin()) {
            return true;
        }

        return $this->getAvailableSchools()->contains('id', (int) $schoolId);
    }

    /**
     * Cek apakah user memiliki akses ke lebih dari satu sekolah
     */
    public function hasMultiSchoolAccess(): bool
    {
        if ($this->isKetuaYayasan() || $this->isSuperAdmin()) {
            return true;
        }

        if ($this->teacher) {
            return $this->teacher->allSchools()->count() > 1;
        }

        return false;
    }
}
