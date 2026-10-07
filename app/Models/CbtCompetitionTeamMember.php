<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CbtCompetitionTeamMember extends Model
{
    use HasFactory;

    protected $table = 'cbt_competition_team_members';

    protected $fillable = [
        'team_id', 'student_id', 'is_operator',
    ];

    protected $casts = [
        'is_operator' => 'boolean',
    ];

    // Relationships
    public function team(): BelongsTo { return $this->belongsTo(CbtCompetitionTeam::class, 'team_id'); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }

    // Scopes
    public function scopeOperators($query) { return $query->where('is_operator', true); }
    public function scopeMembers($query) { return $query->where('is_operator', false); }
}
