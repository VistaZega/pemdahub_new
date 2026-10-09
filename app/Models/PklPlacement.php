<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PklPlacement extends Model
{
    use HasFactory;

    protected $table = 'pkl_placements';

    protected $fillable = [
        'student_id',
        'academic_year_id',
        'dudi_id',
        'company_name',
        'shift',
        'start_date',
        'end_date',
        'signed_token',
        'is_perangkat_ready',
        'perangkat_file_path',
        'company_address',
        'mentor_name',
        'mentor_phone',
        'start_date',
        'end_date',
        'teacher_id',
        'status',
        'signed_token',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_perangkat_ready' => 'boolean',
    ];

    public function dudi()
    {
        return $this->belongsTo(Dudi::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function logs()
    {
        return $this->hasMany(PklLog::class, 'pkl_placement_id');
    }

    public function grade()
    {
        return $this->hasOne(PklGrade::class, 'pkl_placement_id');
    }

    /**
     * Scope query penempatan PKL aktif pada tanggal tertentu.
     * Memberikan kelonggaran/grace period hingga 30 hari pasca end_date selama status
     * penempatan di sistem masih 'active' (belum diubah menjadi 'completed' atau 'selesai').
     */
    public function scopeActiveOnDate($query, $date = null)
    {
        $targetDate = $date ? \Carbon\Carbon::parse($date)->toDateString() : now('Asia/Jakarta')->toDateString();
        $graceDate = \Carbon\Carbon::parse($targetDate)->subDays(30)->toDateString();

        return $query->whereIn('status', ['active', 'aktif', 'approved', 'ongoing', 'berjalan'])
            ->where(function ($q) use ($targetDate) {
                $q->whereNull('start_date')
                  ->orWhereDate('start_date', '<=', $targetDate);
            })
            ->where(function ($q) use ($graceDate) {
                $q->whereNull('end_date')
                  ->orWhereDate('end_date', '>=', $graceDate);
            });
    }
}

