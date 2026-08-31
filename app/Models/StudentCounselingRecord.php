<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentCounselingRecord extends Model
{
    use HasFactory;

    protected $table = 'student_counseling_records';

    protected $fillable = [
        'student_id',
        'school_id',
        'academic_year_id',
        'semester_id',
        'record_type',
        'category',
        'achievement_level',
        'competition_name',
        'organizer',
        'ranking',
        'severity',
        'title',
        'description',
        'background',
        'action_taken',
        'sanction',
        'sanction_type',
        'sanction_duration_days',
        'result',
        'follow_up',
        'incident_date',
        'location',
        'parent_notified',
        'parent_notified_date',
        'parent_response',
        'status',
        'resolved_date',
        'counselor_id',
        'is_confidential',
        'attachment',
        'attachment_name',
    ];

    protected $casts = [
        'incident_date' => 'date',
        'parent_notified' => 'boolean',
        'parent_notified_date' => 'date',
        'resolved_date' => 'date',
        'is_confidential' => 'boolean',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function counselor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counselor_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(CounselingParticipant::class, 'counseling_record_id');
    }

    /**
     * Mutator to safely normalize category to valid database enum values
     */
    public function setCategoryAttribute($value)
    {
        $map = [
            'perilaku' => 'perilaku',
            'moral' => 'perilaku',
            'sikap' => 'perilaku',
            'karakter' => 'perilaku',
            'kedisiplinan' => 'kedisiplinan',
            'disiplin' => 'kedisiplinan',
            'absensi' => 'absensi',
            'kehadiran' => 'absensi',
            'presensi' => 'absensi',
            'akademik' => 'akademik',
            'sosial' => 'sosial',
            'pribadi' => 'pribadi',
            'olahraga' => 'olahraga',
            'seni' => 'seni',
            'keagamaan' => 'keagamaan',
            'karir' => 'karir',
            'lainnya' => 'lainnya',
        ];

        $clean = strtolower(trim((string)$value));
        $this->attributes['category'] = $map[$clean] ?? 'lainnya';
    }

    /**
     * Mutator to safely normalize record_type
     */
    public function setRecordTypeAttribute($value)
    {
        $allowed = ['konseling', 'pembinaan', 'pelanggaran', 'penghargaan', 'home_visit'];
        $clean = strtolower(trim((string)$value));
        $this->attributes['record_type'] = in_array($clean, $allowed) ? $clean : 'pembinaan';
    }

    /**
     * Mutator to safely normalize severity
     */
    public function setSeverityAttribute($value)
    {
        $allowed = ['ringan', 'sedang', 'berat'];
        $clean = strtolower(trim((string)$value));
        $this->attributes['severity'] = in_array($clean, $allowed) ? $clean : 'ringan';
    }

    /**
     * Mutator to safely normalize status
     */
    public function setStatusAttribute($value)
    {
        $allowed = ['open', 'in_progress', 'resolved', 'closed'];
        $clean = strtolower(trim((string)$value));
        $this->attributes['status'] = in_array($clean, $allowed) ? $clean : 'open';
    }
}
