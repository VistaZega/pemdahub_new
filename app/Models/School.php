<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class School extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'schools';

    protected $fillable = [
        'name',
        'type',
        'npsn',
        'address',
        'city',
        'province',
        'postal_code',
        'phone',
        'email',
        'website',
        'principal_name',
        'principal_id',
        'school_year_start',
        'requires_test',
        'test_type',
        'psb_required_documents',
        'psb_custom_document_types',
        'psb_contact_person',
        'psb_contact_phone',
        'psb_opening_hours',
        'psb_secretariat',
        'psb_description',
        'psb_is_active',
        'latitude',
        'longitude',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'requires_test' => 'boolean',
        'school_year_start' => 'integer',
        'psb_is_active' => 'boolean',
        'psb_required_documents' => 'array',
        'psb_custom_document_types' => 'array',
    ];

    /**
     * Get label for Alumni dropdowns & displays (includes historical aliases like STM).
     */
    public function getAlumniLabelAttribute(): string
    {
        $name = $this->name;
        if (str_contains(strtoupper($name), 'SMK')) {
            return "IKA {$name} (d/h STM Pembda)";
        }
        return "IKA {$name}";
    }

    /**
     * Cache key untuk list sekolah aktif
     */
    const CACHE_KEY_ACTIVE = 'schools.active';
    const CACHE_TTL = 3600; // 1 jam

    /**
     * Get all active schools (cached)
     */
    public static function getActiveCached()
    {
        return Cache::remember(self::CACHE_KEY_ACTIVE, self::CACHE_TTL, function () {
            return self::where('is_active', true)
                ->schoolsOnly()
                ->orderBy('name')
                ->get(['id', 'name', 'type', 'city']);
        });
    }

    /**
     * Clear cache saat data berubah
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function () {
            Cache::forget(self::CACHE_KEY_ACTIVE);
        });

        static::deleted(function () {
            Cache::forget(self::CACHE_KEY_ACTIVE);
        });
    }

    /**
     * Relationship: School has many users (Teachers, Students, Parents, Admin)
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Relationship: School has many teachers
     */
    public function teachers()
    {
        return $this->hasMany(Teacher::class);
    }

    /**
     * Relationship: Principal (Kepala Sekolah)
     */
    public function principal()
    {
        return $this->belongsTo(Teacher::class, 'principal_id');
    }

    /**
     * Relationship: School has many students
     */
    public function students()
    {
        return $this->hasMany(Student::class);
    }

    /**
     * Relationship: School has many classrooms
     */
    public function classrooms()
    {
        return $this->hasMany(Classroom::class);
    }

    /**
     * Relationship: School has many subjects
     */
    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }

    /**
     * Relationship: School has many majors (jurusan)
     */
    public function majors()
    {
        return $this->hasMany(Major::class);
    }

    /**
     * Relationship: School has many schedules
     */
    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    /**
     * Relationship: School has many registration waves
     */
    public function registrationWaves()
    {
        return $this->hasMany(RegistrationWave::class);
    }

    /**
     * Relationship: School has many admission fees
     */
    public function admissionFees()
    {
        return $this->hasMany(AdmissionFee::class);
    }

    /**
     * Relationship: School has many admission tests
     */
    public function admissionTests()
    {
        return $this->hasMany(AdmissionTest::class);
    }

    /**
     * Relationship: School has many student bills
     */
    public function bills()
    {
        return $this->hasManyThrough(StudentBill::class, Student::class);
    }

    /**
     * Relationship: School has many LMS courses
     */
    public function lmsCourses()
    {
        return $this->hasMany(LmsCourse::class);
    }

    /**
     * Relationship: School has many alumni
     */
    public function alumni()
    {
        return $this->hasMany(Alumni::class);
    }

    /**
     * Relationship: School has many program keahlians (SMK)
     */
    public function programKeahlians()
    {
        return $this->hasMany(ProgramKeahlian::class);
    }

    /**
     * Get all academic years (global)
     */
    public function academicYears()
    {
        return AcademicYear::all();
    }

    /**
     * Get currently active academic year (global)
     */
    public function currentAcademicYear(): ?AcademicYear
    {
        return AcademicYear::where('is_active', true)->first()
            ?? AcademicYear::orderBy('year', 'desc')->first();
    }

    /**
     * Get grade levels based on school type.
     * SMP: 7, 8, 9
     * SMA/SMK: 10, 11, 12
     * Yayasan: [] (no academic grades)
     */
    public function getGradeLevels(): array
    {
        return match (strtoupper($this->type)) {
            'SMP' => [7, 8, 9],
            'SMA', 'SMK' => [10, 11, 12],
            'YAYASAN' => [],
            default => [7, 8, 9, 10, 11, 12],
        };
    }

    /**
     * Check if this school record is the Yayasan (foundation).
     */
    public function isYayasan(): bool
    {
        return strtolower($this->type) === 'yayasan';
    }

    /**
     * Scope: Only actual schools (exclude yayasan).
     * Use this when listing schools for academic features.
     */
    public function scopeSchoolsOnly($query)
    {
        return $query->where('type', '!=', 'yayasan');
    }

    /**
     * Scope: Only yayasan.
     */
    public function scopeYayasanOnly($query)
    {
        return $query->where('type', 'yayasan');
    }

    /**
     * Get grade levels as comma-separated string for validation rule.
     */
    public function getGradeLevelsRule(): string
    {
        return implode(',', $this->getGradeLevels());
    }

    /**
     * Default document types for seeding.
     */
    public const DEFAULT_DOCUMENT_TYPES = [
        ['key' => 'kk', 'label' => 'Fotocopy Kartu Keluarga (KK)'],
        ['key' => 'akta', 'label' => 'Fotocopy Akta Kelahiran'],
        ['key' => 'ijazah', 'label' => 'Ijazah / SKHU / SKL'],
        ['key' => 'raport', 'label' => 'Raport Semester Terakhir'],
        ['key' => 'photo', 'label' => 'Pas Foto 3x4 (Warna)'],
        ['key' => 'certificate', 'label' => 'Sertifikat Prestasi (Opsional)'],
    ];

    /**
     * Ensure document types are seeded. Auto-seeds defaults only if property is NULL.
     */
    public function ensureDocumentTypesSeeded(): void
    {
        if (is_null($this->psb_custom_document_types)) {
            $this->update(['psb_custom_document_types' => self::DEFAULT_DOCUMENT_TYPES]);
            $this->refresh();
        }
    }

    /**
     * Get all document types for this school.
     * All documents are stored in psb_custom_document_types (fully CRUD).
     * Returns associative array: ['key' => 'Label', ...]
     */
    public function getAllDocumentTypes(): array
    {
        $this->ensureDocumentTypesSeeded();

        $docs = [];
        foreach ($this->psb_custom_document_types as $doc) {
            if (!empty($doc['key']) && !empty($doc['label'])) {
                $docs[$doc['key']] = $doc['label'];
            }
        }

        return $docs;
    }

    /**
     * Get only the document types that are marked as required (selected).
     * Returns associative array: ['key' => 'Label', ...]
     */
    public function getRequiredDocumentLabels(): array
    {
        $allDocs = $this->getAllDocumentTypes();
        $requiredKeys = is_array($this->psb_required_documents) ? $this->psb_required_documents : [];

        $result = [];
        foreach ($requiredKeys as $key) {
            $result[$key] = $allDocs[$key] ?? $key;
        }

        return $result;
    }
}
