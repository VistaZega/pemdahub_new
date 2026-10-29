<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\ReputationLog;
use Carbon\Carbon;

class OsisCandidate extends Model
{
    use HasFactory;

    protected $table = 'osis_candidates';

    protected $fillable = [
        'election_id',
        'candidate_number',
        'candidate_type',
        'leader_name',
        'leader_nisn',
        'leader_class',
        'vice_name',
        'secretary_name',
        'treasurer_name',
        'quote_slogan',
        'vision',
        'mission',
        'work_programs',
        'photo',
        'poster_flyer',
        'video_url',
        'color_badge',
        'vote_count',
        'is_winner',
        'leader_user_id',
        'secretary_user_id',
        'treasurer_user_id',
        'vice_user_id',
        'points_awarded',
        'points_awarded_at',
    ];

    protected $casts = [
        'candidate_number' => 'integer',
        'mission' => 'array',
        'work_programs' => 'array',
        'vote_count' => 'integer',
        'is_winner' => 'boolean',
        'points_awarded' => 'boolean',
        'points_awarded_at' => 'datetime',
    ];

    public function election(): BelongsTo
    {
        return $this->belongsTo(OsisElection::class, 'election_id');
    }

    public function ballots(): HasMany
    {
        return $this->hasMany(OsisBallot::class, 'candidate_id');
    }

    // Relasi Akun Pengurus
    public function leaderUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'leader_user_id');
    }

    public function secretaryUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'secretary_user_id');
    }

    public function treasurerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'treasurer_user_id');
    }

    public function viceUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vice_user_id');
    }

    // Helper untuk foto kartu paslon
    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo && file_exists(public_path($this->photo))) {
            return asset($this->photo);
        }
        if ($this->photo && file_exists(storage_path('app/public/' . $this->photo))) {
            return asset('storage/' . $this->photo);
        }
        return asset('images/osis/paslon_' . $this->candidate_number . '.jpg');
    }

    // Helper untuk poster HD
    public function getPosterUrlAttribute(): string
    {
        if ($this->poster_flyer && file_exists(public_path($this->poster_flyer))) {
            return asset($this->poster_flyer);
        }
        if ($this->poster_flyer && file_exists(storage_path('app/public/' . $this->poster_flyer))) {
            return asset('storage/' . $this->poster_flyer);
        }
        return $this->photo_url;
    }

    // Hitung persentase perolehan suara
    public function getVotePercentageAttribute(): float
    {
        $total = $this->election?->total_votes ?? 0;
        if ($total <= 0) return 0;
        return round(($this->vote_count / $total) * 100, 1);
    }

    /**
     * Berikan 150 Poin Reputasi ke Masing-Masing Pengurus Terpilih
     */
    public function awardOfficerPoints(int $points = 150): array
    {
        $period = $this->election?->period ?? date('Y');
        $awardedList = [];

        // 1. Ketua
        if ($this->leader_user_id) {
            ReputationLog::log(
                $this->leader_user_id,
                $points,
                'prestasi',
                "Penghargaan Ketua OSIS Terpilih Periode {$period} (+{$points} Poin)",
                $this
            );
            $awardedList[] = "Ketua: {$this->leader_name}";
        }

        // 2. Sekretaris
        if ($this->secretary_user_id) {
            ReputationLog::log(
                $this->secretary_user_id,
                $points,
                'prestasi',
                "Penghargaan Sekretaris OSIS Terpilih Periode {$period} (+{$points} Poin)",
                $this
            );
            $awardedList[] = "Sekretaris: {$this->secretary_name}";
        }

        // 3. Bendahara
        if ($this->treasurer_user_id) {
            ReputationLog::log(
                $this->treasurer_user_id,
                $points,
                'prestasi',
                "Penghargaan Bendahara OSIS Terpilih Periode {$period} (+{$points} Poin)",
                $this
            );
            $awardedList[] = "Bendahara: {$this->treasurer_name}";
        }

        // 4. Wakil Ketua (jika ada)
        if ($this->vice_user_id) {
            ReputationLog::log(
                $this->vice_user_id,
                $points,
                'prestasi',
                "Penghargaan Wakil Ketua OSIS Terpilih Periode {$period} (+{$points} Poin)",
                $this
            );
            $awardedList[] = "Wakil Ketua: {$this->vice_name}";
        }

        $this->update([
            'is_winner' => true,
            'points_awarded' => true,
            'points_awarded_at' => Carbon::now(),
        ]);

        return $awardedList;
    }
}
