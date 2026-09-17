<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\LogsActivity;

class CbtExamDispensation extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'cbt_exam_dispensations';

    protected $fillable = [
        'cbt_exam_id',
        'student_id',
        'classroom_id',
        'granted_by',
        'status',
        'reason',
        'granted_at',
        'revoked_at',
    ];

    protected $casts = [
        'granted_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public const STATUS_GRANTED = 'granted';
    public const STATUS_REVOKED = 'revoked';

    public function exam(): BelongsTo
    {
        return $this->belongsTo(CbtExam::class, 'cbt_exam_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'classroom_id');
    }

    public function granter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by');
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_GRANTED);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_GRANTED;
    }

    public function revoke(?int $userId = null): self
    {
        $this->update([
            'status' => self::STATUS_REVOKED,
            'revoked_at' => now(),
        ]);

        return $this;
    }
}
