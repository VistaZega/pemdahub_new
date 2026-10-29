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
     * Otomatis mengembalikan null jika masa pemilihan + grace period pengumuman telah berakhir.
     */
    public static function getActiveCampaignForUser($user = null): ?self
    {
        $user = $user ?? auth()->user();
        if (!$user) {
            return null;
        }

        $schoolId = $user->school_id;
        if ($user->student) {
            $schoolId = $user->student->school_id ?? $schoolId;
        } elseif ($user->teacher) {
            $schoolId = $user->teacher->school_id ?? $schoolId;
        }

        $now = Carbon::now();

        $query = self::with(['school', 'candidates', 'winnerCandidate'])
            ->where('is_active', true);

        if ($schoolId && !in_array($user->role, ['superadmin', 'yayasan'])) {
            $query->where('school_id', $schoolId);
        }

        return $query->where(function ($q) use ($now) {
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
    }
}
