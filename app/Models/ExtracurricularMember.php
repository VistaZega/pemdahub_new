<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExtracurricularMember extends Model
{
    use HasFactory;

    protected $table = 'extracurricular_members';

    protected $fillable = [
        'extracurricular_id',
        'student_id',
        'academic_year_id',
        'role',
        'section',
        'status',
        'joined_date',
        'approved_by',
        'notes',
        'points_awarded',
    ];

    protected $casts = [
        'joined_date' => 'date',
        'points_awarded' => 'integer',
    ];

    public function extracurricular()
    {
        return $this->belongsTo(Extracurricular::class, 'extracurricular_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function approvedByUser()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getRoleLabelAttribute(): string
    {
        return match($this->role) {
            'ketua' => 'Ketua / Koordinator',
            'wakil_ketua' => 'Wakil Ketua',
            'sekretaris' => 'Sekretaris',
            'bendahara' => 'Bendahara',
            'sie_kegiatan' => 'Seksi Kegiatan',
            default => 'Anggota Aktif'
        };
    }

    public function getRoleBadgeColorAttribute(): string
    {
        return match($this->role) {
            'ketua' => 'from-amber-500 to-orange-600',
            'wakil_ketua' => 'from-orange-500 to-amber-600',
            'sekretaris' => 'from-blue-500 to-indigo-600',
            'bendahara' => 'from-emerald-500 to-teal-600',
            'sie_kegiatan' => 'from-purple-500 to-fuchsia-600',
            default => 'from-slate-600 to-slate-700'
        };
    }
}
