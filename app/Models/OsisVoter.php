<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OsisVoter extends Model
{
    use HasFactory;

    protected $table = 'osis_voters';

    protected $fillable = [
        'election_id',
        'user_id',
        'voter_type',
        'has_voted',
        'voted_at',
        'vote_token',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'has_voted' => 'boolean',
        'voted_at' => 'datetime',
    ];

    public function election(): BelongsTo
    {
        return $this->belongsTo(OsisElection::class, 'election_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
