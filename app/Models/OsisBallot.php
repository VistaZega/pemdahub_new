<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OsisBallot extends Model
{
    use HasFactory;

    public $timestamps = false; // Hanya created_at

    protected $table = 'osis_ballots';

    protected $fillable = [
        'election_id',
        'candidate_id',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function election(): BelongsTo
    {
        return $this->belongsTo(OsisElection::class, 'election_id');
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(OsisCandidate::class, 'candidate_id');
    }
}
