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

    protected $appends = [
        'photo_url',
        'poster_url',
        'video_embed_url',
        'video_type',
        'has_video',
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
        if (!empty($this->photo)) {
            $p = trim($this->photo);
            if (str_starts_with($p, 'http://') || str_starts_with($p, 'https://')) {
                return $p;
            }
            if (str_starts_with($p, 'storage/') || str_starts_with($p, '/storage/')) {
                return asset(ltrim($p, '/'));
            }
            if (file_exists(public_path($p))) {
                return asset($p);
            }
            return asset('storage/' . ltrim($p, '/'));
        }
        return asset('images/osis/paslon_' . ($this->candidate_number ?: 1) . '.jpg');
    }

    // Helper untuk poster HD
    public function getPosterUrlAttribute(): string
    {
        if (!empty($this->poster_flyer)) {
            $p = trim($this->poster_flyer);
            if (str_starts_with($p, 'http://') || str_starts_with($p, 'https://')) {
                return $p;
            }
            if (str_starts_with($p, 'storage/') || str_starts_with($p, '/storage/')) {
                return asset(ltrim($p, '/'));
            }
            if (file_exists(public_path($p))) {
                return asset($p);
            }
            return asset('storage/' . ltrim($p, '/'));
        }
        return $this->photo_url;
    }

    // Helper cek status video kampanye
    public function getHasVideoAttribute(): bool
    {
        return !empty($this->video_url);
    }

    // Helper tipe video (youtube, drive, video_file)
    public function getVideoTypeAttribute(): ?string
    {
        if (empty($this->video_url)) return null;

        $url = trim($this->video_url);

        if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)/i', $url)) {
            return 'youtube';
        }

        if (preg_match('/drive\.google\.com\/file\/d\//i', $url)) {
            return 'drive';
        }

        return 'video_file';
    }

    // Helper URL embed video untuk iframe atau pemutar HTML5
    public function getVideoEmbedUrlAttribute(): ?string
    {
        if (empty($this->video_url)) return null;

        $url = trim($this->video_url);

        // 1. YouTube (Watch URL, Shorts, Embed, youtu.be)
        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?|shorts)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i', $url, $matches)) {
            return 'https://www.youtube-nocookie.com/embed/' . $matches[1] . '?rel=0&modestbranding=1';
        }

        // 2. Google Drive
        if (preg_match('/drive\.google\.com\/file\/d\/([a-zA-Z0-9_-]+)/i', $url, $matches)) {
            return 'https://drive.google.com/file/d/' . $matches[1] . '/preview';
        }

        // 3. Local Upload / Direct Video URL
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        if (str_starts_with($url, 'storage/') || str_starts_with($url, '/storage/')) {
            return asset(ltrim($url, '/'));
        }

        return asset('storage/' . ltrim($url, '/'));
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
