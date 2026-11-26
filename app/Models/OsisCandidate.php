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

    /**
     * Resolve local/storage media path only when the file actually exists.
     */
    protected function resolvePublicMediaUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        $p = trim($path);
        if (str_starts_with($p, 'http://') || str_starts_with($p, 'https://')) {
            return $p;
        }

        $clean = ltrim($p, '/');
        if (str_starts_with($clean, 'storage/')) {
            $storageRelative = substr($clean, 8);
            if (file_exists(public_path($clean)) || file_exists(storage_path('app/public/' . $storageRelative))) {
                return asset($clean);
            }
            return null;
        }

        if (file_exists(public_path($clean))) {
            return asset($clean);
        }

        if (file_exists(public_path('storage/' . $clean)) || file_exists(storage_path('app/public/' . $clean))) {
            return asset('storage/' . $clean);
        }

        return null;
    }

    // Helper untuk foto kartu paslon
    public function getPhotoUrlAttribute(): string
    {
        $resolved = $this->resolvePublicMediaUrl($this->photo);
        if ($resolved !== null) {
            return $resolved;
        }

        $defaultPath = 'images/osis/paslon_' . ($this->candidate_number ?: 1) . '.jpg';
        if (file_exists(public_path($defaultPath))) {
            return asset($defaultPath);
        }

        $bgColors = [
            1 => 'e11d48',
            2 => '2563eb',
            3 => 'd97706',
            4 => '059669',
        ];
        $bg = $bgColors[$this->candidate_number] ?? '0f172a';
        $name = urlencode(trim((string) ($this->leader_name ?: 'Paslon ' . ($this->candidate_number ?: 1))));

        return "https://ui-avatars.com/api/?name={$name}&background={$bg}&color=ffffff&bold=true&size=256";
    }

    // Helper untuk poster HD
    public function getPosterUrlAttribute(): string
    {
        return $this->resolvePublicMediaUrl($this->poster_flyer) ?? $this->photo_url;
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
