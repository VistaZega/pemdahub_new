<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinalProject extends Model
{
    use HasFactory;

    protected $table = 'final_projects';

    // Stages Constants
    public const STAGE_PROPOSAL = 'proposal';
    public const STAGE_BAB1 = 'bab1';
    public const STAGE_BAB2 = 'bab2';
    public const STAGE_BAB3 = 'bab3';
    public const STAGE_BAB4 = 'bab4';
    public const STAGE_BAB5 = 'bab5';
    public const STAGE_SIDANG = 'sidang';
    public const STAGE_COMPLETED = 'completed';

    protected $fillable = [
        'student_id',
        'academic_year_id',
        'type',
        'title',
        'abstract',
        'advisor_id',
        'status',
        'current_stage',
        'rejection_reason',
        'exam_date',
        'exam_location',
        'examiner_id',
        'examiner2_id',
        'grade',
        'grade_notes',
    ];

    /**
     * Get detailed info for all guidance stages
     */
    public static function getStages(): array
    {
        return [
            self::STAGE_BAB1 => [
                'name' => 'Bab I: Pendahuluan',
                'label' => 'Bab I',
                'description' => 'Latar Belakang Masalah, Rumusan Masalah, Tujuan, dan Manfaat Penelitian/Project.',
                'next' => self::STAGE_BAB2,
            ],
            self::STAGE_BAB2 => [
                'name' => 'Bab II: Kajian Pustaka',
                'label' => 'Bab II',
                'description' => 'Kajian Teori, Penelitian Terdahulu, Kerangka Berpikir, dan Hipotesis (jika ada).',
                'next' => self::STAGE_BAB3,
            ],
            self::STAGE_BAB3 => [
                'name' => 'Bab III: Metodologi / Perancangan',
                'label' => 'Bab III',
                'description' => 'Metode Penelitian, Waktu & Tempat, Instrumen Pengumpulan Data, atau Perancangan Desain/Sistem.',
                'next' => self::STAGE_BAB4,
            ],
            self::STAGE_BAB4 => [
                'name' => 'Bab IV: Hasil & Pembahasan',
                'label' => 'Bab IV',
                'description' => 'Penyajian Data Penelitian, Analisis Hasil Penelitian, atau Implementasi & Pengujian Sistem.',
                'next' => self::STAGE_BAB5,
            ],
            self::STAGE_BAB5 => [
                'name' => 'Bab V: Penutup',
                'label' => 'Bab V',
                'description' => 'Kesimpulan dari seluruh pembahasan penelitian/project dan Saran untuk pengembangan.',
                'next' => self::STAGE_SIDANG,
            ],
            self::STAGE_SIDANG => [
                'name' => 'Siap Sidang / Ujian Akhir',
                'label' => 'Sidang',
                'description' => 'Penyusunan laporan lengkap, persiapan materi presentasi, dan pengajuan jadwal sidang.',
                'next' => self::STAGE_COMPLETED,
            ],
            self::STAGE_COMPLETED => [
                'name' => 'Lulus & Selesai',
                'label' => 'Lulus',
                'description' => 'Kelompok Tugas Akhir telah menyelesaikan semua tahapan revisi sidang dan dinyatakan lulus.',
                'next' => null,
            ],
        ];
    }

    /**
     * Get active stage name
     */
    public function getCurrentStageNameAttribute(): string
    {
        $stages = self::getStages();
        return $stages[$this->current_stage]['name'] ?? 'Penyusunan Proposal';
    }

    protected $casts = [
        'exam_date' => 'datetime',
        'grade' => 'decimal:2',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function advisor()
    {
        return $this->belongsTo(Teacher::class, 'advisor_id');
    }

    public function examiner()
    {
        return $this->belongsTo(Teacher::class, 'examiner_id');
    }

    public function examiner2()
    {
        return $this->belongsTo(Teacher::class, 'examiner2_id');
    }

    public function logs()
    {
        return $this->hasMany(FinalProjectLog::class, 'final_project_id');
    }

    public function members()
    {
        return $this->hasMany(FinalProjectMember::class, 'final_project_id');
    }
}
