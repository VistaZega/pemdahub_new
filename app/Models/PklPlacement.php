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
     * Scope query penempatan PKL aktif pada tanggal tertentu (Opsi A - Tanpa Batasan Waktu Selesai).
     * Selama tanggal mulai PKL sudah tiba (start_date <= targetDate), siswa memiliki lokasi DUDI,
     * dan statusnya masih 'active' (belum diubah menjadi 'completed' atau 'selesai' oleh sekolah),
     * siswa bebas melakukan presensi di lokasi PKL tanpa dibatasi oleh tanggal selesai (end_date).
     */
    public function scopeActiveOnDate($query, $date = null)
    {
        $targetDate = $date ? \Carbon\Carbon::parse($date)->toDateString() : now('Asia/Jakarta')->toDateString();

        return $query->whereIn('status', ['active', 'aktif', 'approved', 'ongoing', 'berjalan'])
            ->where(function ($q) {
                $q->whereNotNull('dudi_id')
                  ->orWhereNotNull('company_name');
            })
            ->where(function ($q) use ($targetDate) {
                $q->whereNull('start_date')
                  ->orWhereDate('start_date', '<=', $targetDate);
            });
    }
}

