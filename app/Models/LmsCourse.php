<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LmsCourse extends Model
{
    use HasFactory, SoftDeletes;

    public $timestamps = true;

    protected $table = 'lms_courses';

    protected $fillable = [
        'school_id',
        'teacher_id',
        'subject_id',
        'classroom_id',
        'semester_id',
        'code',
        'course_name',
        'description',
        'cover_image',
        'status',
        'is_published',
        'is_active',
        'is_sequential',
        'meeting_active',
        'meeting_started_at',
        'review_status',
        'reviewed_by',
        'reviewed_at',
        'review_note',
        'shared_from_course_id', // ID course asal saat fitur Sharing Course digunakan
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_published' => 'boolean',
        'is_sequential' => 'boolean',
        'meeting_active' => 'boolean',
        'meeting_started_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    protected const STATUSES = [
        'draft' => 'Draft',
        'active' => 'Aktif',
        'archived' => 'Diarsipkan',
    ];

    /**
     * Get shorthand course code
     */
    public function getShortCode(): string
    {
        return substr($this->code, 0, 8); // Codes are usually LMS-XXXXXXXX, 8 is good
    }

    /**
     * Accessor: $course->name returns course_name for backward compatibility
     */
    public function getNameAttribute()
    {
        return $this->course_name;
    }

    /**
     * Mutator: $course->name = 'x' sets course_name
     */
    public function setNameAttribute($value)
    {
        $this->attributes['course_name'] = $value;
    }

    /**
     * Get status label - uses status column if available, otherwise derives from is_published
     */
    public function getStatusLabel()
    {
        if (!empty($this->attributes['status'])) {
            return self::STATUSES[$this->attributes['status']] ?? $this->attributes['status'];
        }
        return $this->is_published ? 'Aktif' : 'Draft';
    }

    /**
     * Get computed status value
     */
    public function getComputedStatusAttribute()
    {
        return $this->attributes['status'] ?? ($this->is_published ? 'active' : 'draft');
    }

    /**
     * Get Subject / Course thematic visual design tokens (icon, gradient, emoji, badge color)
     */
    public function getDesignAttribute(): array
    {
        $subjectName = $this->subject->name ?? '';
        $haystack = strtolower(($this->course_name ?? '') . ' ' . $subjectName);

        if (preg_match('/(matematika|aljabar|kalkulus|hitung|statistika|math)/i', $haystack)) {
            return [
                'icon' => 'fa-solid fa-calculator',
                'emoji' => '📐',
                'gradient' => 'from-emerald-600 via-teal-600 to-cyan-700',
                'badgeBg' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                'accentColor' => 'emerald',
                'category' => 'Eksak & Hitungan',
            ];
        }

        if (preg_match('/(indonesia|sastra|bahasa id)/i', $haystack)) {
            return [
                'icon' => 'fa-solid fa-book-open-reader',
                'emoji' => '📖',
                'gradient' => 'from-rose-600 via-red-600 to-pink-700',
                'badgeBg' => 'bg-rose-50 text-rose-800 border-rose-200',
                'accentColor' => 'rose',
                'category' => 'Bahasa & Sastra',
            ];
        }

        if (preg_match('/(inggris|english|toefl|foreign)/i', $haystack)) {
            return [
                'icon' => 'fa-solid fa-earth-americas',
                'emoji' => '🌐',
                'gradient' => 'from-blue-600 via-indigo-600 to-sky-700',
                'badgeBg' => 'bg-blue-50 text-blue-800 border-blue-200',
                'accentColor' => 'blue',
                'category' => 'Bahasa Asing',
            ];
        }

        if (preg_match('/(fisika|physics)/i', $haystack)) {
            return [
                'icon' => 'fa-solid fa-atom',
                'emoji' => '⚛️',
                'gradient' => 'from-cyan-600 via-blue-600 to-indigo-800',
                'badgeBg' => 'bg-cyan-50 text-cyan-800 border-cyan-200',
                'accentColor' => 'cyan',
                'category' => 'Sains Fisika',
            ];
        }

        if (preg_match('/(kimia|chemistry)/i', $haystack)) {
            return [
                'icon' => 'fa-solid fa-flask-vial',
                'emoji' => '🧪',
                'gradient' => 'from-purple-600 via-violet-600 to-fuchsia-800',
                'badgeBg' => 'bg-purple-50 text-purple-800 border-purple-200',
                'accentColor' => 'purple',
                'category' => 'Sains Kimia',
            ];
        }

        if (preg_match('/(biologi|ipa|anatomi|lingkungan|natural)/i', $haystack)) {
            return [
                'icon' => 'fa-solid fa-dna',
                'emoji' => '🧬',
                'gradient' => 'from-emerald-700 via-green-600 to-teal-800',
                'badgeBg' => 'bg-green-50 text-green-800 border-green-200',
                'accentColor' => 'green',
                'category' => 'Sains & Hayati',
            ];
        }

        if (preg_match('/(informatika|rpl|tkj|komputer|coding|pemrograman|basis data|database|jaringan|network|web|it|software|hardware|multimedia|desain grafis)/i', $haystack)) {
            return [
                'icon' => 'fa-solid fa-laptop-code',
                'emoji' => '💻',
                'gradient' => 'from-indigo-700 via-slate-800 to-blue-950',
                'badgeBg' => 'bg-indigo-50 text-indigo-800 border-indigo-200',
                'accentColor' => 'indigo',
                'category' => 'Teknologi & IT',
            ];
        }

        if (preg_match('/(sejarah|history|ips|sosiologi|antropologi)/i', $haystack)) {
            return [
                'icon' => 'fa-solid fa-landmark-dome',
                'emoji' => '🏛️',
                'gradient' => 'from-amber-700 via-amber-600 to-yellow-800',
                'badgeBg' => 'bg-amber-50 text-amber-800 border-amber-200',
                'accentColor' => 'amber',
                'category' => 'Sosial & Humaniora',
            ];
        }

        if (preg_match('/(geografi|bumi|kebumian)/i', $haystack)) {
            return [
                'icon' => 'fa-solid fa-globe',
                'emoji' => '🌍',
                'gradient' => 'from-teal-700 via-emerald-600 to-sky-800',
                'badgeBg' => 'bg-teal-50 text-teal-800 border-teal-200',
                'accentColor' => 'teal',
                'category' => 'Geografi & Bumi',
            ];
        }

        if (preg_match('/(ekonomi|akuntansi|keuangan|bisnis|manajemen|marketing|pemasaran)/i', $haystack)) {
            return [
                'icon' => 'fa-solid fa-chart-line',
                'emoji' => '📊',
                'gradient' => 'from-emerald-700 via-teal-600 to-slate-800',
                'badgeBg' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                'accentColor' => 'emerald',
                'category' => 'Ekonomi & Bisnis',
            ];
        }

        if (preg_match('/(agama|pak|pab|kristen|katolik|islam|budi pekerti|spiritual)/i', $haystack)) {
            return [
                'icon' => 'fa-solid fa-hands-praying',
                'emoji' => '🙏',
                'gradient' => 'from-sky-600 via-indigo-600 to-teal-700',
                'badgeBg' => 'bg-sky-50 text-sky-800 border-sky-200',
                'accentColor' => 'sky',
                'category' => 'Pendidikan Keagamaan',
            ];
        }

        if (preg_match('/(ppkn|pkn|pancasila|kewarganegaraan|hukum)/i', $haystack)) {
            return [
                'icon' => 'fa-solid fa-shield-halved',
                'emoji' => '🇮🇩',
                'gradient' => 'from-red-600 via-rose-700 to-slate-900',
                'badgeBg' => 'bg-red-50 text-red-800 border-red-200',
                'accentColor' => 'red',
                'category' => 'Kewarganegaraan',
            ];
        }

        if (preg_match('/(seni|musik|rupa|tari|teater|budaya|prakarya|kerajinan)/i', $haystack)) {
            return [
                'icon' => 'fa-solid fa-palette',
                'emoji' => '🎨',
                'gradient' => 'from-pink-600 via-fuchsia-600 to-purple-800',
                'badgeBg' => 'bg-pink-50 text-pink-800 border-pink-200',
                'accentColor' => 'pink',
                'category' => 'Seni & Budaya',
            ];
        }

        if (preg_match('/(pjok|penjas|olahraga|atletik|kebugaran)/i', $haystack)) {
            return [
                'icon' => 'fa-solid fa-person-running',
                'emoji' => '⚽',
                'gradient' => 'from-orange-600 via-amber-600 to-rose-700',
                'badgeBg' => 'bg-orange-50 text-orange-800 border-orange-200',
                'accentColor' => 'orange',
                'category' => 'Olahraga & Kesehatan',
            ];
        }

        if (preg_match('/(otomotif|tkr|tbsm|sepeda motor|mobil|mesin bubut|bengkel)/i', $haystack)) {
            return [
                'icon' => 'fa-solid fa-wrench',
                'emoji' => '🔧',
                'gradient' => 'from-slate-700 via-zinc-800 to-neutral-900',
                'badgeBg' => 'bg-slate-100 text-slate-800 border-slate-300',
                'accentColor' => 'slate',
                'category' => 'Teknik Otomotif',
            ];
        }

        if (preg_match('/(listrik|elektronika|mekatronika|kelistrikan|pln)/i', $haystack)) {
            return [
                'icon' => 'fa-solid fa-bolt-lightning',
                'emoji' => '⚡',
                'gradient' => 'from-amber-600 via-orange-600 to-yellow-700',
                'badgeBg' => 'bg-amber-50 text-amber-800 border-amber-200',
                'accentColor' => 'amber',
                'category' => 'Teknik Elektro',
            ];
        }

        if (preg_match('/(hotel|perhotelan|pariwisata|tata boga|kuliner|food|beverage|restoran)/i', $haystack)) {
            return [
                'icon' => 'fa-solid fa-bell-concierge',
                'emoji' => '🛎️',
                'gradient' => 'from-rose-600 via-orange-600 to-amber-700',
                'badgeBg' => 'bg-rose-50 text-rose-800 border-rose-200',
                'accentColor' => 'rose',
                'category' => 'Pariwisata & Kuliner',
            ];
        }

        if (preg_match('/(busana|fashion|menjahit|tekstil)/i', $haystack)) {
            return [
                'icon' => 'fa-solid fa-scissors',
                'emoji' => '👗',
                'gradient' => 'from-fuchsia-600 via-pink-600 to-purple-700',
                'badgeBg' => 'bg-fuchsia-50 text-fuchsia-800 border-fuchsia-200',
                'accentColor' => 'fuchsia',
                'category' => 'Tata Busana',
            ];
        }

        if (preg_match('/(bk|konseling|psikologi|karir)/i', $haystack)) {
            return [
                'icon' => 'fa-solid fa-heart-pulse',
                'emoji' => '💖',
                'gradient' => 'from-rose-500 via-pink-500 to-purple-600',
                'badgeBg' => 'bg-rose-50 text-rose-800 border-rose-200',
                'accentColor' => 'rose',
                'category' => 'Bimbingan Konseling',
            ];
        }

        if (preg_match('/(ipas|pkk|projek kreatif|kewirausahaan|entrepreneur)/i', $haystack)) {
            return [
                'icon' => 'fa-solid fa-lightbulb',
                'emoji' => '💡',
                'gradient' => 'from-amber-600 via-teal-600 to-indigo-700',
                'badgeBg' => 'bg-amber-50 text-amber-800 border-amber-200',
                'accentColor' => 'amber',
                'category' => 'Projek & Inovasi',
            ];
        }

        // Default Subject Card Theme
        return [
            'icon' => 'fa-solid fa-graduation-cap',
            'emoji' => '🎓',
            'gradient' => 'from-purple-700 via-indigo-600 to-blue-700',
            'badgeBg' => 'bg-purple-50 text-purple-800 border-purple-200',
            'accentColor' => 'purple',
            'category' => 'Mata Pelajaran Umum',
        ];
    }

    /**
     * Relationship: Course belongs to School
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Relationship: Course belongs to Teacher
     */
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Relationship: Course belongs to Subject
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Relationship: Course belongs to Semester
     */
    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     * Relationship: Course belongs to Classroom (direct assignment in real DB)
     */
    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    /**
     * Relationship: Course has many LMS classes
     */
    public function lmsClasses()
    {
        return $this->hasMany(LmsClass::class, 'course_id');
    }

    /**
     * Relationship: Course asal dari mana course ini dishare (jika hasil sharing)
     */
    public function sharedFromCourse()
    {
        return $this->belongsTo(LmsCourse::class, 'shared_from_course_id');
    }

    /**
     * Relationship: Salinan course ini yang pernah dibagikan ke guru lain
     */
    public function sharedCopies()
    {
        return $this->hasMany(LmsCourse::class, 'shared_from_course_id');
    }

    /**
     * Alias for lmsClasses relationship
     */
    public function classes()
    {
        return $this->lmsClasses();
    }

    /**
     * Relationship: Course has many modules
     */
    public function modules()
    {
        return $this->hasMany(LmsModule::class, 'course_id');
    }

    /**
     * Relationship: Course has many materials (direct link via course_id)
     */
    public function materials()
    {
        return $this->hasMany(LmsMaterial::class, 'course_id');
    }

    /**
     * Relationship: Course has many assignments
     */
    public function assignments()
    {
        return $this->hasMany(LmsAssignment::class, 'course_id');
    }

    /**
     * Relationship: Course has many quizzes
     */
    public function quizzes()
    {
        return $this->hasMany(LmsQuiz::class, 'course_id');
    }

    public function games()
    {
        return $this->hasMany(LmsGame::class, 'course_id');
    }

    /**
     * Relationship: Course has many announcements
     */
    public function announcements()
    {
        return $this->hasMany(LmsAnnouncement::class, 'course_id');
    }

    /**
     * Relationship: Course has many course groups (Master Kelompok Belajar)
     */
    public function courseGroups()
    {
        return $this->hasMany(LmsCourseGroup::class, 'course_id');
    }

    /**
     * Relationship: Course has many discussions
     */
    public function discussions()
    {
        return $this->hasMany(LmsDiscussion::class, 'course_id');
    }

    /**
     * Relationship: Course has many submissions through assignments
     */
    public function submissions()
    {
        return $this->hasManyThrough(LmsSubmission::class, LmsAssignment::class, 'course_id', 'assignment_id');
    }

    /**
     * Relationship: Course has many enrollments through lmsClasses
     */
    public function enrollments()
    {
        return $this->hasManyThrough(LmsEnrollment::class, LmsClass::class, 'course_id', 'lms_class_id');
    }

    /**
     * Get total enrolled students across all LMS classes
     */
    public function getEnrolledStudentsCountAttribute()
    {
        return LmsEnrollment::whereIn('lms_class_id', $this->lmsClasses()->pluck('id'))
            ->whereIn('status', ['enrolled', 'in_progress'])
            ->count();
    }

    /**
     * Scope: Get active courses
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->where('is_published', true)
              ->orWhere('status', 'active');
        });
    }

    /**
     * Scope: Get courses by teacher
     */
    public function scopeByTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    /**
     * Get scientist configuration for classroom based on name
     */
    public function getScientistConfig(): ?array
    {
        if (!$this->classroom) return null;
        
        $avatar = $this->classroom->getAvatarConfig();
        if ($avatar && !empty($avatar['icon'])) {
            return $avatar;
        }

        return null;
    }

    /**
     * Get Course Schedule from Master Schedules
     */
    public function getCourseSchedule()
    {
        return \App\Models\Schedule::where('subject_id', $this->subject_id)
            ->where('classroom_id', $this->classroom_id)
            ->where('teacher_id', $this->teacher_id)
            ->with(['timeSlot'])
            ->get();
    }

    /**
     * Get Consolidated Course Schedule (grouped ranges)
     */
    public function getConsolidatedSchedule()
    {
        $schedules = $this->getCourseSchedule();
        if ($schedules->isEmpty()) return collect();

        $dayOrder = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $alphabet = [
            'monday' => 'Senin', 
            'tuesday' => 'Selasa', 
            'wednesday' => 'Rabu', 
            'thursday' => 'Kamis', 
            'friday' => 'Jumat', 
            'saturday' => 'Sabtu', 
            'sunday' => 'Minggu'
        ];

        $grouped = $schedules->groupBy('day_of_week')
            ->sortBy(fn($group, $day) => array_search($day, $dayOrder));

        $consolidated = [];

        foreach ($grouped as $day => $daySchedules) {
            $sorted = $daySchedules->sortBy('timeSlot.slot_order');
            $dayLabel = $alphabet[$day] ?? ucfirst($day);

            $currentRange = null;

            foreach ($sorted as $sch) {
                if (!$sch->timeSlot) continue;
                
                $start = substr($sch->timeSlot->start_time, 0, 5);
                $end = substr($sch->timeSlot->end_time, 0, 5);

                if ($currentRange === null) {
                    $currentRange = ['start' => $start, 'end' => $end];
                } else {
                    // If end of previous slot matches start of current slot, extend range
                    if ($currentRange['end'] === $start) {
                        $currentRange['end'] = $end;
                    } else {
                        // Close current range and start new one
                        $consolidated[] = "$dayLabel: {$currentRange['start']} - {$currentRange['end']}";
                        $currentRange = ['start' => $start, 'end' => $end];
                    }
                }
            }

            if ($currentRange) {
                $consolidated[] = "$dayLabel: {$currentRange['start']} - {$currentRange['end']}";
            }
        }

        return collect($consolidated);
    }

    /**
     * Get tailwind classes for a given color
     */
    public static function getColorClasses(?string $color): array
    {
        $map = [
            'indigo' => [
                'bg' => 'bg-indigo-600',
                'bg-light' => 'bg-indigo-50',
                'bg-soft' => 'bg-indigo-100',
                'text' => 'text-indigo-700',
                'text-dark' => 'text-indigo-900',
                'border' => 'border-indigo-200',
                'gradient' => 'from-indigo-500 to-indigo-700'
            ],
            'emerald' => [
                'bg' => 'bg-emerald-600',
                'bg-light' => 'bg-emerald-50',
                'bg-soft' => 'bg-emerald-100',
                'text' => 'text-emerald-700',
                'text-dark' => 'text-emerald-900',
                'border' => 'border-emerald-200',
                'gradient' => 'from-emerald-500 to-teal-600'
            ],
            'rose' => [
                'bg' => 'bg-rose-600',
                'bg-light' => 'bg-rose-50',
                'bg-soft' => 'bg-rose-100',
                'text' => 'text-rose-700',
                'text-dark' => 'text-rose-900',
                'border' => 'border-rose-200',
                'gradient' => 'from-rose-500 to-pink-600'
            ],
            'amber' => [
                'bg' => 'bg-amber-500',
                'bg-light' => 'bg-amber-50',
                'bg-soft' => 'bg-amber-100',
                'text' => 'text-amber-700',
                'text-dark' => 'text-amber-900',
                'border' => 'border-amber-200',
                'gradient' => 'from-amber-400 to-orange-500'
            ],
            'cyan' => [
                'bg' => 'bg-cyan-600',
                'bg-light' => 'bg-cyan-50',
                'bg-soft' => 'bg-cyan-100',
                'text' => 'text-cyan-700',
                'text-dark' => 'text-cyan-900',
                'border' => 'border-cyan-200',
                'gradient' => 'from-cyan-500 to-blue-600'
            ],
            'violet' => [
                'bg' => 'bg-violet-600',
                'bg-light' => 'bg-violet-50',
                'bg-soft' => 'bg-violet-100',
                'text' => 'text-violet-700',
                'text-dark' => 'text-violet-900',
                'border' => 'border-violet-200',
                'gradient' => 'from-violet-500 to-purple-600'
            ],
            'teal' => [
                'bg' => 'bg-teal-600',
                'bg-light' => 'bg-teal-50',
                'bg-soft' => 'bg-teal-100',
                'text' => 'text-teal-700',
                'text-dark' => 'text-teal-900',
                'border' => 'border-teal-200',
                'gradient' => 'from-teal-400 to-emerald-600'
            ],
            'blue' => [
                'bg' => 'bg-blue-600',
                'bg-light' => 'bg-blue-50',
                'bg-soft' => 'bg-blue-100',
                'text' => 'text-blue-700',
                'text-dark' => 'text-blue-900',
                'border' => 'border-blue-200',
                'gradient' => 'from-blue-500 to-indigo-600'
            ],
            'orange' => [
                'bg' => 'bg-orange-500',
                'bg-light' => 'bg-orange-50',
                'bg-soft' => 'bg-orange-100',
                'text' => 'text-orange-700',
                'text-dark' => 'text-orange-900',
                'border' => 'border-orange-200',
                'gradient' => 'from-orange-400 to-red-500'
            ],
            'fuchsia' => [
                'bg' => 'bg-fuchsia-600',
                'bg-light' => 'bg-fuchsia-50',
                'bg-soft' => 'bg-fuchsia-100',
                'text' => 'text-fuchsia-700',
                'text-dark' => 'text-fuchsia-900',
                'border' => 'border-fuchsia-200',
                'gradient' => 'from-fuchsia-500 to-pink-600'
            ],
        ];

        return $map[$color] ?? $map['blue'];
    }

    /**
     * Get dynamic color name based on first module (fallback to indigo).
     * Used by views and components accessing $course->color.
     */
    public function getColorAttribute(): string
    {
        $firstModule = $this->relationLoaded('modules')
            ? $this->modules->first()
            : $this->modules()->orderBy('sequence')->first();

        return $firstModule?->color ?: 'indigo';
    }

    /**
     * Get theme hex color based on modules.
     * Default before any module is created: Light Gray (#94a3b8 / Abu Muda).
     * Once a module is created, returns the hex code matching the module's chosen color.
     */
    public function getActiveThemeHexColor(): string
    {
        $firstModule = $this->relationLoaded('modules')
            ? $this->modules->first()
            : $this->modules()->orderBy('sequence')->first();

        if (!$firstModule || !$firstModule->color) {
            return '#94a3b8'; // Abu Muda (Default sebelum dibuat modul)
        }

        $colorMap = [
            'indigo'  => '#4f46e5',
            'emerald' => '#059669',
            'rose'    => '#e11d48',
            'amber'   => '#d97706',
            'blue'    => '#2563eb',
            'purple'  => '#9333ea',
            'cyan'    => '#0891b2',
            'orange'  => '#ea580c',
            'teal'    => '#0d9488',
            'violet'  => '#7c3aed',
            'fuchsia' => '#c026d3',
            'gray'    => '#94a3b8',
        ];

        return $colorMap[strtolower($firstModule->color)] ?? '#4f46e5';
    }

    /**
     * Get teacher profile photo / avatar URL for course display
     */
    public function getTeacherPhotoUrl(): string
    {
        if ($this->teacher && $this->teacher->user && !empty($this->teacher->user->avatar_url)) {
            return $this->teacher->user->avatar_url;
        }

        if ($this->teacher && !empty($this->teacher->photo)) {
            $p = $this->teacher->photo;
            if (\Illuminate\Support\Str::startsWith($p, ['http://', 'https://'])) {
                return $p;
            }
            return asset('storage/' . ltrim($p, '/'));
        }

        $name = $this->teacher?->user?->name ?? $this->teacher?->full_name ?? 'Guru Pengajar';
        return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=0f172a&color=ffffff&bold=true';
    }

    // ──────────────────────────────────────────────
    //  REVIEW / SUPERVISI
    // ──────────────────────────────────────────────

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopeNeedReview($query)
    {
        return $query->where('review_status', 'pending');
    }

    public function scopeApprovedOnly($query)
    {
        return $query->where('review_status', 'approved');
    }

    public function getReviewStatusLabelAttribute()
    {
        return match ($this->review_status ?? 'approved') {
            'unreviewed' => 'Belum Direview',
            'pending'    => 'Menunggu Review',
            'approved'   => 'Disetujui',
            'rejected'   => 'Ditolak',
            default      => '-',
        };
    }

    public function getReviewStatusColorAttribute()
    {
        return match ($this->review_status ?? 'approved') {
            'unreviewed' => 'bg-slate-100 text-slate-700',
            'pending'    => 'bg-amber-100 text-amber-800',
            'approved'   => 'bg-emerald-100 text-emerald-800',
            'rejected'   => 'bg-rose-100 text-rose-800',
            default      => 'bg-slate-100 text-slate-700',
        };
    }
}
