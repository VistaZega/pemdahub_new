<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CbtCompetitionTeam extends Model
{
    use HasFactory;

    protected $table = 'cbt_competition_teams';

    protected $fillable = [
        'exam_id', 'team_name', 'classroom_id', 'created_by',
    ];

    // Relationships
    public function exam(): BelongsTo { return $this->belongsTo(CbtExam::class, 'exam_id'); }
    public function classroom(): BelongsTo { return $this->belongsTo(Classroom::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function members(): HasMany { return $this->hasMany(CbtCompetitionTeamMember::class, 'team_id'); }
    public function sessions(): HasMany { return $this->hasMany(CbtExamSession::class, 'competition_team_id'); }

    /**
     * Operator = siswa yang login dan mengerjakan soal mewakili tim
     */
    public function operator(): HasOne
    {
        return $this->hasOne(CbtCompetitionTeamMember::class, 'team_id')->where('is_operator', true);
    }

    /**
     * Nama tampilan: pakai team_name jika ada, fallback ke nama kelas
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->team_name ?? $this->classroom?->class_name ?? 'Tim #' . $this->id;
    }

    /**
     * Apakah tim ini sudah punya operator (siswa yang akan mengerjakan)?
     */
    public function hasOperator(): bool
    {
        return $this->members()->where('is_operator', true)->exists();
    }

    /**
     * Dapatkan student_id operator tim (yang akan mengerjakan ujian)
     */
    public function getOperatorStudentId(): ?int
    {
        return $this->members()->where('is_operator', true)->value('student_id');
    }

    // Scopes
    public function scopeByExam($query, $examId) { return $query->where('exam_id', $examId); }
}
