<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    use HasFactory;

    // table does not have timestamps columns
    public $timestamps = false;

    protected $table = 'classrooms';

    protected $fillable = [
        'school_id',
        'academic_year_id',
        'major_id',
        'program_keahlian_id',
        'konsentrasi_keahlian_id',
        'class_code',
        'class_name',
        'class_type',
        'grade_level',
        'shift',
        'homeroom_teacher_id',
        'capacity',
        'notes',
        'entry_time',
        'late_tolerance',
        'is_combined',
        'is_active',
    ];

    protected $casts = [
        'grade_level' => 'integer',
        'capacity' => 'integer',
        'homeroom_teacher_id' => 'integer',
        'school_id' => 'integer',
        'academic_year_id' => 'integer',
        'is_combined' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Check if classroom is a combined / multi-major class
     */
    public function isCombinedClass(): bool
    {
        return (bool) ($this->is_combined || $this->class_type === 'gabungan');
    }

    /**
     * Dapatkan batas waktu toleransi kehadiran kelas (H:i:s).
     * Default entry_time: 07:30, default late_tolerance: 15 menit -> 07:45:00
     */
    public function getLateThreshold(): string
    {
        $entryTime = $this->entry_time ?: '07:30';
        $tolerance = (int) ($this->late_tolerance ?? 15);
        return date('H:i:s', strtotime("{$entryTime} +{$tolerance} minutes"));
    }

    /**
     * Tentukan apakah waktu tertentu melewati batas toleransi masuk kelas
     */
    public function isLate(?string $time = null): bool
    {
        $time = $time ?: now()->format('H:i:s');
        if (strlen($time) === 5) {
            $time .= ':00';
        }
        return $time > $this->getLateThreshold();
    }

    /**
     * Classroom belongs to a School
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Classroom belongs to an Academic Year
     */
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Optional relationship: students in this classroom
     */
    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_classes', 'classroom_id', 'student_id')
            ->withPivot('academic_year_id', 'status', 'promoted_at')
            ->withTimestamps();
    }

    /**
     * Optional relationship: major (jurusan for SMA/SMP)
     */
    public function major()
    {
        return $this->belongsTo(Major::class);
    }

    /**
     * Optional relationship: program keahlian
     */
    public function programKeahlian()
    {
        return $this->belongsTo(ProgramKeahlian::class);
    }

    /**
     * Optional relationship: konsentrasi keahlian
     */
    public function konsentrasiKeahlian()
    {
        return $this->belongsTo(KonsentrasiKeahlian::class);
    }

    /**
     * Classroom belongs to a homeroom teacher (wali kelas)
     */
    public function homeroomTeacher()
    {
        return $this->belongsTo(Teacher::class, 'homeroom_teacher_id');
    }

    /**
     * Classroom has many schedules
     */
    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    /**
     * Classroom has many teaching assignments
     */
    public function teachingAssignments()
    {
        return $this->hasMany(TeachingAssignment::class);
    }

    /**
     * Classroom has many LMS classes
     */
    public function lmsClasses()
    {
        return $this->hasMany(LmsClass::class, 'classroom_id');
    }

    /**
     * Get avatar configuration for the classroom.
     * Returns gradient, ring, icon, initials, and name.
     */
    public function getAvatarConfig(): array
    {
        $className = $this->class_name;
        $namePart = $className;
        $gradePart = '';

        // Extract grade level (e.g., VII, VIII, IX, X, XI, XII, or digits)
        if (preg_match('/^([0-9]+|[IVXLC]+)[- ]+(.+)$/i', $className, $matches)) {
            $gradePart = trim($matches[1]);
            $namePart = trim($matches[2]);
        }

        // Check for vocational school programs (SMK)
        $vocationalConfigs = [
            'DPIB' => [
                'gradient' => 'from-cyan-500 to-sky-700',
                'ring' => 'ring-cyan-300',
                'icon' => '<path d="M3 21h18M9 21V9a3 3 0 013-3h0a3 3 0 013 3v12M5 21V5a2 2 0 012-2h10a2 2 0 012 2v16" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/><path d="M9 9h6M9 13h6M9 17h6" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/>',
                'name' => 'Desain Pemodelan & Informasi Bangunan (DPIB)'
            ],
            'TJKT' => [
                'gradient' => 'from-blue-600 to-indigo-800',
                'ring' => 'ring-blue-300',
                'icon' => '<rect x="2" y="3" width="20" height="14" rx="2" ry="2" stroke-width="1.5"/><path d="M8 21h8M12 17v4M2 17h20" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/>',
                'name' => 'Teknik Jaringan Komputer & Telekomunikasi (TJKT)'
            ],
            'TKJ' => [
                'gradient' => 'from-blue-600 to-indigo-800',
                'ring' => 'ring-blue-300',
                'icon' => '<rect x="2" y="3" width="20" height="14" rx="2" ry="2" stroke-width="1.5"/><path d="M8 21h8M12 17v4M2 17h20" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/>',
                'name' => 'Teknik Komputer & Jaringan (TKJ)'
            ],
            'ACP' => [
                'gradient' => 'from-indigo-600 to-violet-800',
                'ring' => 'ring-indigo-300',
                'icon' => '<rect x="2" y="3" width="20" height="14" rx="2" ry="2" stroke-width="1.5"/><path d="M8 21h8M12 17v4M2 17h20" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/>',
                'name' => 'Axioo Class Program (ACP)'
            ],
            'TSM' => [
                'gradient' => 'from-amber-500 to-orange-700',
                'ring' => 'ring-amber-300',
                'icon' => '<circle cx="12" cy="12" r="3" stroke-width="1.5"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 11-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 11-2.83-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 005 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 112.83-2.83l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 112.83 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/>',
                'name' => 'Teknik Sepeda Motor (TSM)'
            ],
            'TKR' => [
                'gradient' => 'from-emerald-500 to-teal-700',
                'ring' => 'ring-emerald-300',
                'icon' => '<circle cx="12" cy="12" r="10" stroke-width="1.5"/><path d="M12 2v20M2 12h20M12 12l-7 7M12 12l7 7" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/>',
                'name' => 'Teknik Kendaraan Ringan (TKR)'
            ],
            'TO' => [
                'gradient' => 'from-emerald-500 to-teal-700',
                'ring' => 'ring-emerald-300',
                'icon' => '<circle cx="12" cy="12" r="10" stroke-width="1.5"/><path d="M12 2v20M2 12h20M12 12l-7 7M12 12l7 7" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/>',
                'name' => 'Teknik Otomotif (TO)'
            ],
            'TAV' => [
                'gradient' => 'from-violet-500 to-purple-700',
                'ring' => 'ring-violet-300',
                'icon' => '<rect x="4" y="4" width="16" height="16" rx="2" stroke-width="1.5"/><path d="M9 9h6v6H9zM9 1v3M15 1v3M9 20v3M15 20v3M20 9h3M20 15h3M1 9h3M1 15h3" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/>',
                'name' => 'Teknik Audio Video (TAV)'
            ],
            'TE' => [
                'gradient' => 'from-violet-500 to-purple-700',
                'ring' => 'ring-violet-300',
                'icon' => '<rect x="4" y="4" width="16" height="16" rx="2" stroke-width="1.5"/><path d="M9 9h6v6H9zM9 1v3M15 1v3M9 20v3M15 20v3M20 9h3M20 15h3M1 9h3M1 15h3" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/>',
                'name' => 'Teknik Elektronika (TE)'
            ]
        ];

        foreach ($vocationalConfigs as $key => $config) {
            if (preg_match('/\b' . preg_quote($key, '/') . '\b/i', $className)) {
                return [
                    'name' => $config['name'],
                    'is_predefined' => true,
                    'gradient' => $config['gradient'],
                    'ring' => $config['ring'],
                    'icon' => $config['icon'],
                    'initials' => $key
                ];
            }
        }

        // List of predefined configurations for scientists and national heroes
        $predefined = [
            // Scientists
            'Alessandro Volta' => [
                'gradient' => 'from-amber-400 to-yellow-600',
                'ring' => 'ring-amber-300',
                'icon' => '<path d="M13 10V3L4 14h7v7l9-11h-7z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/>'
            ],
            'Isaac Newton' => [
                'gradient' => 'from-emerald-400 to-teal-600',
                'ring' => 'ring-emerald-300',
                'icon' => '<circle cx="12" cy="8" r="2" stroke-width="1.5"/><path d="M12 10v6M9 20c1-2 2-4 3-4s2 2 3 4" stroke-linecap="round" stroke-width="1.5"/><path d="M14.5 4.5c1-2 3-1 2 1" stroke-linecap="round" stroke-width="1.5"/>'
            ],
            'Nikola Tesla' => [
                'gradient' => 'from-violet-500 to-purple-700',
                'ring' => 'ring-violet-300',
                'icon' => '<path d="M13 10V3L4 14h7v7l9-11h-7z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/>'
            ],
            'Marie Curie' => [
                'gradient' => 'from-rose-400 to-pink-600',
                'ring' => 'ring-rose-300',
                'icon' => '<circle cx="12" cy="10" r="3" stroke-width="1.5"/><path d="M12 7V4M12 16v-3M15 10h3M9 10H6M14.1 7.9l2.1-2.1M7.8 14.2l2.1-2.1M14.1 12.1l2.1 2.1M7.8 5.8l2.1 2.1" stroke-linecap="round" stroke-width="1.5"/>'
            ],
            'Albert Einstein' => [
                'gradient' => 'from-blue-500 to-indigo-700',
                'ring' => 'ring-blue-300',
                'icon' => '<text x="12" y="17" text-anchor="middle" fill="white" font-size="14" font-weight="bold" font-style="italic" font-family="serif">E</text>'
            ],
            'Thomas Alva Edison' => [
                'gradient' => 'from-orange-400 to-red-600',
                'ring' => 'ring-orange-300',
                'icon' => '<path d="M9 21h6M12 3a6 6 0 00-4 10.5V17h8v-3.5A6 6 0 0012 3z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/>'
            ],
            'Pythagoras' => [
                'gradient' => 'from-cyan-500 to-blue-700',
                'ring' => 'ring-cyan-300',
                'icon' => '<path d="M4 20h7V9l9 11" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/>'
            ],
            'Archimedes' => [
                'gradient' => 'from-sky-400 to-cyan-600',
                'ring' => 'ring-sky-300',
                'icon' => '<path d="M12 3v18M8 7c0-2 8-2 8 0M7 12h10M8 17c0 2 8 2 8 0" stroke-linecap="round" stroke-width="1.5"/>'
            ],
            'Blaise Pascal' => [
                'gradient' => 'from-indigo-400 to-blue-600',
                'ring' => 'ring-indigo-300',
                'icon' => '<path d="M12 3L3 20h18L12 3z" stroke-linejoin="round" stroke-width="1.5"/><path d="M7.5 14h9M10 9h4" stroke-linecap="round" stroke-width="1.5"/>'
            ],
            'Gregor Mendel' => [
                'gradient' => 'from-lime-400 to-green-600',
                'ring' => 'ring-lime-300',
                'icon' => '<path d="M12 22V12M12 12c-3-3-7 0-7 4M12 12c3-3 7 0 7 4M12 12c-2-4-5-7-2-9M12 12c2-4 5-7 2-9" stroke-linecap="round" stroke-width="1.5"/>'
            ],
            'Alexsander Graham Bell' => [
                'gradient' => 'from-teal-400 to-emerald-600',
                'ring' => 'ring-teal-300',
                'icon' => '<path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/>'
            ],
            'Aristoteles' => [
                'gradient' => 'from-purple-400 to-fuchsia-600',
                'ring' => 'ring-purple-300',
                'icon' => '<path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/>'
            ],

            // Heroes
            'Amir Hamza' => [
                'gradient' => 'from-teal-500 to-emerald-700',
                'ring' => 'ring-teal-300',
                'icon' => '<path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/>'
            ],
            'Budi Utomo' => [
                'gradient' => 'from-orange-500 to-amber-700',
                'ring' => 'ring-orange-300',
                'icon' => '<path d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m0-12.728l.707.707m11.314 11.314l.707.707M12 8a4 4 0 100 8 4 4 0 000-8z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/>'
            ],
            'Chairil' => [
                'gradient' => 'from-purple-500 to-indigo-700',
                'ring' => 'ring-purple-300',
                'icon' => '<path d="M20 4c-4.07 0-7.82 2.44-9.37 6H7v4h1.76c.32 2 .98 3.84 2 5.37L12 18l5-8c.95.14 1.83.63 2.5 1.37" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/><path d="M3 21l3-3" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/>'
            ],
            'Hamka' => [
                'gradient' => 'from-emerald-600 to-green-800',
                'ring' => 'ring-emerald-300',
                'icon' => '<path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/>'
            ],
            'Ismail Marzuki' => [
                'gradient' => 'from-rose-500 to-red-700',
                'ring' => 'ring-rose-300',
                'icon' => '<path d="M9 18V5l12-2v13" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/><circle cx="6" cy="18" r="3" stroke-width="1.5"/><circle cx="18" cy="16" r="3" stroke-width="1.5"/>'
            ],
            'Wahidin Sudirohusodo' => [
                'gradient' => 'from-cyan-500 to-teal-700',
                'ring' => 'ring-cyan-300',
                'icon' => '<path d="M22 12h-4l-3 9L9 3l-3 9H2" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/>'
            ],
            'Ws. Rendra' => [
                'gradient' => 'from-fuchsia-500 to-pink-700',
                'ring' => 'ring-fuchsia-300',
                'icon' => '<path d="M12 2a10 10 0 100 20 10 10 0 000-20z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/><path d="M8 14s1.5 2 4 2 4-2 4-2M9 9h.01M15 9h.01" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>'
            ],
            'WS Rendra' => [
                'gradient' => 'from-fuchsia-500 to-pink-700',
                'ring' => 'ring-fuchsia-300',
                'icon' => '<path d="M12 2a10 10 0 100 20 10 10 0 000-20z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/><path d="M8 14s1.5 2 4 2 4-2 4-2M9 9h.01M15 9h.01" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>'
            ],
            'Ahmad Yani' => [
                'gradient' => 'from-red-600 to-amber-800',
                'ring' => 'ring-red-300',
                'icon' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/>'
            ],
            'HM Hatta' => [
                'gradient' => 'from-blue-600 to-cyan-800',
                'ring' => 'ring-blue-300',
                'icon' => '<path d="M10 14l2-2 5 5M19 10l-7 7-3-3" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/><circle cx="12" cy="12" r="10" stroke-width="1.5"/>'
            ],
            'HM Yamin' => [
                'gradient' => 'from-red-500 to-rose-700',
                'ring' => 'ring-red-300',
                'icon' => '<path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1zM4 22V15" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/>'
            ],
            'RA Kartini' => [
                'gradient' => 'from-pink-500 to-purple-600',
                'ring' => 'ring-pink-300',
                'icon' => '<path d="M12 2v2M12 18v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41M12 7a5 5 0 100 10 5 5 0 000-10z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/>'
            ],
            'SM Raja' => [
                'gradient' => 'from-amber-600 to-red-800',
                'ring' => 'ring-amber-300',
                'icon' => '<path d="M14.5 17.5L3 6V3h3l11.5 11.5M13 19l2 2M19 13l2 2" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/><path d="M19 5l-2.5 2.5M16.5 7.5L14 10" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/>'
            ],
            'Sudirman' => [
                'gradient' => 'from-green-600 to-emerald-800',
                'ring' => 'ring-green-400',
                'icon' => '<path d="M12 17l-5.878 3.09 1.123-6.545L2.489 8.91l6.572-.955L12 2l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/>'
            ],
            'Supomo' => [
                'gradient' => 'from-indigo-600 to-blue-800',
                'ring' => 'ring-indigo-300',
                'icon' => '<path d="M12 3v18M12 7l-8 4v2l8-4M12 7l8 4v2l-8-4M8 12v3c0 2.21 1.79 4 4 4s4-1.79 4-4v-3" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/>'
            ],
            'Hasanuddin' => [
                'gradient' => 'from-amber-500 to-red-700',
                'ring' => 'ring-amber-300',
                'icon' => '<path d="M12 4l3 6 6-1-4 7h-10l-4-7 6 1z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/><path d="M12 20h8M4 20h8" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/>'
            ],
            'Adam Malik' => [
                'gradient' => 'from-cyan-600 to-sky-800',
                'ring' => 'ring-cyan-300',
                'icon' => '<circle cx="12" cy="12" r="10" stroke-width="1.5"/><path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10zM2 12h20" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/>'
            ],
            'Diponegoro' => [
                'gradient' => 'from-yellow-500 to-orange-700',
                'ring' => 'ring-yellow-300',
                'icon' => '<path d="M14.5 17.5L3 6V3h3l11.5 11.5" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/>'
            ],
            'Katamso' => [
                'gradient' => 'from-slate-600 to-slate-800',
                'ring' => 'ring-slate-400',
                'icon' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/><path d="M12 8v8M8 12h8" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/>'
            ],
            'Ki Hadjar Dewantara' => [
                'gradient' => 'from-sky-500 to-blue-700',
                'ring' => 'ring-sky-300',
                'icon' => '<path d="M22 10L12 5 2 10l10 5 10-5zM6 12v5c0 2 2.5 3 6 3s6-1 6-3v-5" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/>'
            ],
            'Pattimura' => [
                'gradient' => 'from-indigo-500 to-purple-700',
                'ring' => 'ring-indigo-300',
                'icon' => '<path d="M12 22a8 8 0 008-8h-8V2c-3 0-8 3-8 12a8 8 0 008 8z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/><path d="M12 14v4M8 16h8" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/>'
            ],
            'Sutomo' => [
                'gradient' => 'from-red-600 to-orange-700',
                'ring' => 'ring-red-400',
                'icon' => '<path d="M11 5L6 9H2v6h4l5 4V5zM15.54 8.46a5 5 0 010 7.07M19.07 4.93a10 10 0 010 14.14" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/>'
            ],
            'Yos Sudarso' => [
                'gradient' => 'from-blue-500 to-cyan-700',
                'ring' => 'ring-blue-300',
                'icon' => '<path d="M12 5V2M12 22c-3.866 0-7-3.134-7-7h2a5 5 0 0010 0h2c0 3.866-3.134 7-7 7zM8 12h8M12 12V5" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/><circle cx="12" cy="4" r="2" stroke-width="1.5"/>'
            ]
        ];

        // If exact match found
        if (isset($predefined[$namePart])) {
            return array_merge($predefined[$namePart], [
                'name' => $namePart,
                'is_predefined' => true,
                'initials' => null
            ]);
        }

        // Check case-insensitive match just in case
        foreach ($predefined as $key => $config) {
            if (strcasecmp($key, $namePart) === 0) {
                return array_merge($config, [
                    'name' => $key,
                    'is_predefined' => true,
                    'initials' => null
                ]);
            }
        }

        // Fallback initials generator for non-predefined classes
        $words = preg_split('/[- ]+/', $namePart);
        $initials = '';
        if (count($words) >= 2) {
            $initials = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        } elseif (count($words) === 1 && !empty($words[0])) {
            $initials = strtoupper(substr($words[0], 0, min(2, strlen($words[0]))));
        }

        if (empty($initials)) {
            $initials = strtoupper(substr($className, 0, 2));
        }

        // Deterministic gradient selection based on class name string hash
        $gradients = [
            ['gradient' => 'from-cyan-500 to-blue-600', 'ring' => 'ring-cyan-300'],
            ['gradient' => 'from-emerald-500 to-teal-600', 'ring' => 'ring-emerald-300'],
            ['gradient' => 'from-indigo-500 to-purple-600', 'ring' => 'ring-indigo-300'],
            ['gradient' => 'from-rose-500 to-pink-600', 'ring' => 'ring-rose-300'],
            ['gradient' => 'from-amber-500 to-orange-600', 'ring' => 'ring-amber-300'],
            ['gradient' => 'from-violet-500 to-fuchsia-600', 'ring' => 'ring-violet-300'],
            ['gradient' => 'from-teal-500 to-emerald-600', 'ring' => 'ring-teal-300'],
            ['gradient' => 'from-blue-500 to-indigo-600', 'ring' => 'ring-blue-300'],
        ];

        $hash = crc32($namePart);
        $selectedGradient = $gradients[abs($hash) % count($gradients)];

        return [
            'name' => $namePart,
            'is_predefined' => false,
            'gradient' => $selectedGradient['gradient'],
            'ring' => $selectedGradient['ring'],
            'icon' => null,
            'initials' => $initials
        ];
    }

    /**
     * Fallback for name attribute.
     */
    public function getNameAttribute()
    {
        return $this->class_name;
    }
}


