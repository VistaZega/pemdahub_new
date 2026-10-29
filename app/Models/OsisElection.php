<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class OsisElection extends Model
{
    use HasFactory;

    protected $table = 'osis_elections';

    protected $fillable = [
        'school_id',
        'academic_year_id',
        'title',
        'slug',
        'period',
        'theme_tagline',
        'description',
        'banner_image',
        'start_time',
        'end_time',
        'status',
        'allow_teachers',
        'allow_staff',
        'show_realtime_count',
        'winner_candidate_id',
        'announcement_date',
        'is_active',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'announcement_date' => 'datetime',
        'allow_teachers' => 'boolean',
        'allow_staff' => 'boolean',
        'show_realtime_count' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(OsisCandidate::class, 'election_id')->orderBy('candidate_number', 'asc');
    }

    public function winnerCandidate(): BelongsTo
    {
        return $this->belongsTo(OsisCandidate::class, 'winner_candidate_id');
    }

    public function voters(): HasMany
    {
        return $this->hasMany(OsisVoter::class, 'election_id');
    }

    public function ballots(): HasMany
    {
        return $this->hasMany(OsisBallot::class, 'election_id');
    }

    // Status Helpers
    public function isVotingOpen(): bool
    {
        if ($this->status === 'voting_closed' || $this->status === 'completed') {
            return false;
        }

        $now = Carbon::now();
        if ($this->start_time && $this->end_time) {
            return $now->between($this->start_time, $this->end_time);
        }

        return $this->status === 'voting_open';
    }

    public function isUpcoming(): bool
    {
        if ($this->status === 'upcoming') {
            return true;
        }
        if ($this->start_time && Carbon::now()->lt($this->start_time)) {
            return true;
        }
        return false;
    }

    public function isClosed(): bool
    {
        if ($this->status === 'voting_closed' || $this->status === 'completed') {
            return true;
        }
        if ($this->end_time && Carbon::now()->gt($this->end_time)) {
            return true;
        }
        return false;
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed' || $this->winner_candidate_id !== null;
    }

    // Statistik
    public function getTotalVotesAttribute(): int
    {
        return $this->ballots()->count();
    }

    public function getTotalVotersAttribute(): int
    {
        return $this->voters()->count();
    }

    public function getTurnoutPercentageAttribute(): float
    {
        $total = $this->total_voters;
        if ($total <= 0) return 0;
        return round(($this->total_votes / $total) * 100, 1);
    }

    /**
     * Dapatkan agenda kampanye / pemilu aktif untuk user saat ini.
     * Mendukung akun Yayasan yang Guru, switcher multi-unit sekolah, dan auto-expire.
     */
    public static function getActiveCampaignForUser($user = null): ?self
    {
        $user = $user ?? auth()->user();
        if (!$user) {
            return null;
        }

        // Cek apakah akun adalah Yayasan atau SuperAdmin
        $isYayasanOrAdmin = in_array($user->role, ['superadmin', 'yayasan', 'ketua_yayasan', 'admin_yayasan'])
            || (method_exists($user, 'isKetuaYayasan') && $user->isKetuaYayasan())
            || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin());

        // 1. Tentukan target School ID:
        // Prioritas 1: session active_school_id (jika user beralih unit, misal Yayasan/Guru yang beralih ke SMK)
        $schoolId = session('active_school_id');

        // Prioritas 2: method getActiveSchoolId() bawaan User
        if (!$schoolId && method_exists($user, 'getActiveSchoolId')) {
            $schoolId = $user->getActiveSchoolId();
        }

        // Prioritas 3: relasi siswa / guru / user school_id
        if (!$schoolId) {
            if ($user->student) {
                $schoolId = $user->student->school_id ?? $user->school_id;
            } elseif ($user->teacher) {
                $schoolId = $user->teacher->school_id ?? $user->school_id;
            } else {
                $schoolId = $user->school_id;
            }
        }

        // Jika schoolId mengarah ke Yayasan (type == 'yayasan'), jangan gunakan ID yayasan karena OSIS adalah milik unit sekolah (SMK/SMA/SMP)
        if ($schoolId) {
            $school = School::find($schoolId);
            if ($school && $school->type === 'yayasan') {
                $schoolId = null;
            }
        }

        $now = Carbon::now();

        $query = self::with(['school', 'candidates', 'winnerCandidate'])
            ->where('is_active', true);

        // Jika schoolId valid untuk unit sekolah, filter spesifik sekolah tersebut
        if ($schoolId && !$isYayasanOrAdmin) {
            $query->where('school_id', $schoolId);
        } elseif ($schoolId && $isYayasanOrAdmin) {
            // Jika akun Yayasan sedang memilih unit sekolah di switcher, prioritaskan unit tersebut
            $query->where('school_id', $schoolId);
        }

        // Filter event yang aktif (masa kampanye / voting terbuka ATAU grace period pemenang 3 hari)
        $campaign = (clone $query)->where(function ($q) use ($now) {
            $q->where(function ($activeQ) use ($now) {
                $activeQ->whereIn('status', ['upcoming', 'voting_open'])
                    ->where(function ($timeQ) use ($now) {
                        $timeQ->whereNull('end_time')
                            ->orWhere('end_time', '>', $now);
                    });
            })->orWhere(function ($completedQ) use ($now) {
                $completedQ->where(function ($sub) {
                    $sub->where('status', 'completed')
                        ->orWhereNotNull('winner_candidate_id');
                })->where('announcement_date', '>=', $now->copy()->subDays(3));
            });
        })
        ->orderByRaw("CASE WHEN status = 'voting_open' THEN 1 WHEN status = 'upcoming' THEN 2 ELSE 3 END")
        ->latest()
        ->first();

        // Fallback khusus untuk akun Yayasan / SuperAdmin jika belum ada filter spesifik: ambil agenda aktif pertama yang ada
        if (!$campaign && $isYayasanOrAdmin) {
            $campaign = self::with(['school', 'candidates', 'winnerCandidate'])
                ->where('is_active', true)
                ->where(function ($q) use ($now) {
                    $q->where(function ($activeQ) use ($now) {
                        $activeQ->whereIn('status', ['upcoming', 'voting_open'])
                            ->where(function ($timeQ) use ($now) {
                                $timeQ->whereNull('end_time')
                                    ->orWhere('end_time', '>', $now);
                            });
                    })->orWhere(function ($completedQ) use ($now) {
                        $completedQ->where(function ($sub) {
                            $sub->where('status', 'completed')
                                ->orWhereNotNull('winner_candidate_id');
                        })->where('announcement_date', '>=', $now->copy()->subDays(3));
                    });
                })
                ->latest()
                ->first();
        }

        return $campaign;
    }
}
