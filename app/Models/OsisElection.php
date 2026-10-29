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
        'is_simulation',
        'is_active',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'announcement_date' => 'datetime',
        'allow_teachers' => 'boolean',
        'allow_staff' => 'boolean',
        'show_realtime_count' => 'boolean',
        'is_simulation' => 'boolean',
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

    public function getIsSimulationAttribute(): bool
    {
        return array_key_exists('is_simulation', $this->attributes) 
            ? (bool) $this->attributes['is_simulation'] 
            : true;
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
        if ($schoolId) {
            $school = School::find($schoolId);
            if ($school && strtolower($school->type ?? '') === 'yayasan') {
                $schoolId = null;
            }
        }

        // 1. Cari election aktif yang sesuai unit sekolah yang dipilih
        $campaign = null;
        if ($schoolId) {
            $campaign = self::with(['school', 'candidates', 'winnerCandidate'])
                ->where('is_active', true)
                ->where('school_id', $schoolId)
                ->orderByRaw("CASE WHEN status = 'voting_open' THEN 1 WHEN status = 'upcoming' THEN 2 ELSE 3 END")
                ->latest()
                ->first();
        }

        // 2. Fallback untuk semua user (Yayasan, Guru, Siswa):
        // Jika belum ada agenda di unit sekolah terkait, ambil agenda OSIS aktif manapun di database (misal simulasi SMK)
        if (!$campaign) {
            $campaign = self::with(['school', 'candidates', 'winnerCandidate'])
                ->where('is_active', true)
                ->orderByRaw("CASE WHEN status = 'voting_open' THEN 1 WHEN status = 'upcoming' THEN 2 ELSE 3 END")
                ->latest()
                ->first();
        }

        if (!$campaign) {
            return null;
        }

        // 3. Logika Auto-Expire:
        // Banner hanya disembunyikan jika agenda sudah selesai (completed) dan telah melewati grace period 3 hari setelah pengumuman
        if ($campaign->isCompleted() && $campaign->announcement_date) {
            $announceTime = Carbon::parse($campaign->announcement_date);
            if (Carbon::now()->gt($announceTime->addDays(3))) {
                return null;
            }
        }

        return $campaign;
    }
}
