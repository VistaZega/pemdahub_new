<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    ];

    protected $casts = [
        'candidate_number' => 'integer',
        'mission' => 'array',
        'work_programs' => 'array',
        'vote_count' => 'integer',
    ];

    public function election(): BelongsTo
    {
        return $this->belongsTo(OsisElection::class, 'election_id');
    }

    public function ballots(): HasMany
    {
        return $this->hasMany(OsisBallot::class, 'candidate_id');
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
        // Fallback placeholder elegan
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
}
