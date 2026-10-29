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

        // Cek apakah akun memiliki hak akses Yayasan atau SuperAdmin
        $isYayasanOrAdmin = in_array($user->role, ['superadmin', 'yayasan', 'ketua_yayasan', 'admin_yayasan'])
            || (method_exists($user, 'isKetuaYayasan') && $user->isKetuaYayasan())
            || (method_exists($user, 'isYayasan') && $user->isYayasan())
            || (method_exists($user, 'canAccessYayasan') && $user->canAccessYayasan())
            || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin())
            || in_array(session('active_role'), ['yayasan', 'ketua_yayasan', 'superadmin']);

        // 1. Tentukan target School ID:
        // Prioritas 0: Query string jika ada
        $schoolId = request('school_id') ?: request('target_school_id') ?: request('unit_id');

        // Prioritas 1: session active_school_id (dari unit switcher di navbar)
        if (!$schoolId) {
            $schoolId = session('active_school_id');
        }

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

        // Jika schoolId mengarah ke Yayasan (type == 'yayasan'), jangan batasi ke ID yayasan
        // karena OSIS hanya diselenggarakan di 3 unit sekolah aktif (SMK, SMA, SMP)
        if ($schoolId) {
            $school = School::find($schoolId);
            if ($school && strtolower($school->type ?? '') === 'yayasan') {
                $schoolId = null;
            }
        }

        $now = Carbon::now();

        // Base query
        $buildQuery = function (?int $filterSchoolId = null) use ($now) {
            $q = self::with(['school', 'candidates', 'winnerCandidate'])
                ->where('is_active', true);

            if ($filterSchoolId) {
                $q->where('school_id', $filterSchoolId);
            }

            // Filter status aktif (voting_open atau upcoming yang belum berakhir, ATAU grace period pemenang 3 hari)
            $q->where(function ($sub) use ($now) {
                // Skenario A: Sedang Berlangsung (voting_open selalu aktif jika is_active=1)
                $sub->where(function ($openQ) use ($now) {
                    $openQ->where('status', 'voting_open')
                        ->where(function ($timeQ) use ($now) {
                            $timeQ->whereNull('end_time')
                                ->orWhere('end_time', '>=', $now->copy()->subHours(6)); // Beri kelonggaran toleransi waktu
                        });
                })
                // Skenario B: Masa Kampanye / Pra-Pemilihan (Upcoming)
                ->orWhere(function ($upcomingQ) use ($now) {
                    $upcomingQ->where('status', 'upcoming')
                        ->where(function ($timeQ) use ($now) {
                            $timeQ->whereNull('end_time')
                                ->orWhere('end_time', '>', $now);
                        });
                })
                // Skenario C: Selesai & Ada Pemenang (Grace period spotlight 3 hari setelah pengumuman)
                ->orWhere(function ($completedQ) use ($now) {
                    $completedQ->where(function ($c) {
                        $c->where('status', 'completed')
                          ->orWhereNotNull('winner_candidate_id');
                    });
                    $completedQ->where(function ($dateQ) use ($now) {
                        $dateQ->whereNull('announcement_date')
                              ->orWhere('announcement_date', '>=', $now->copy()->subDays(3));
                    });
                });
            });

            return $q->orderByRaw("CASE WHEN status = 'voting_open' THEN 1 WHEN status = 'upcoming' THEN 2 ELSE 3 END")
                     ->latest();
        };

        // 1. Coba ambil berdasarkan unit sekolah aktif terpilih
        $campaign = null;
        if ($schoolId) {
            $campaign = $buildQuery($schoolId)->first();
        }

        // 2. Jika belum ketemu dan user adalah Yayasan / SuperAdmin, coba ambil event aktif dari unit sekolah manapun
        if (!$campaign && $isYayasanOrAdmin) {
            $campaign = $buildQuery(null)->first();
        }

        // 3. Fallback darurat: Jika masih null (misal ada agenda is_active = 1 tapi status belum diset sempurna)
        if (!$campaign && $isYayasanOrAdmin) {
            $campaign = self::with(['school', 'candidates', 'winnerCandidate'])
                ->where('is_active', true)
                ->whereIn('status', ['voting_open', 'upcoming'])
                ->latest()
                ->first();
        }

        return $campaign;
    }
}
