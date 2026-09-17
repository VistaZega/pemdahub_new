<?php

namespace App\Services;

use App\Models\CbtExam;
use App\Models\CbtExamDispensation;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\StudentBill;
use App\Models\StudentClass;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CbtTuitionComplianceService
{
    /**
     * Periksa kepatuhan pembayaran uang sekolah dan dispensasi seorang siswa untuk ujian tertentu.
     *
     * @return array
     */
    public function checkStudentCompliance(CbtExam $exam, Student $student): array
    {
        // 1. Jika ujian tidak mensyaratkan pembayaran uang sekolah, langsung lolos
        if (!$exam->requires_tuition_payment) {
            return [
                'allowed' => true,
                'is_required' => false,
                'reason' => 'not_required',
                'is_paid' => true,
                'has_dispensation' => false,
                'dispensation' => null,
                'target_month' => null,
                'target_year' => null,
                'month_label' => '',
                'bill' => null,
                'unpaid_amount' => 0,
                'homeroom_teacher' => null,
                'homeroom_name' => null,
                'homeroom_phone' => null,
                'whatsapp_url' => null,
                'message' => 'Ujian tidak mensyaratkan pembayaran uang sekolah.',
            ];
        }

        // 2. Dapatkan target bulan dan tahun evaluasi
        $period = $exam->getTargetTuitionPeriod();
        $targetMonth = $period['month'];
        $targetYear = $period['year'];
        $monthLabel = $period['label'];

        // 3. Ambil data wali kelas dari kelas aktif siswa
        $activeClassroom = $student->currentClassroom()->first() ?? $student->classroom;
        $homeroomTeacher = $activeClassroom?->homeroomTeacher;
        $homeroomName = $homeroomTeacher?->full_name ?: ($homeroomTeacher?->user?->name ?? 'Wali Kelas');
        $homeroomPhone = $homeroomTeacher?->phone ?? null;

        // Siapkan link WhatsApp jika ada kontak
        $whatsappUrl = null;
        if ($homeroomPhone) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $homeroomPhone);
            if (str_starts_with($cleanPhone, '0')) {
                $cleanPhone = '62' . substr($cleanPhone, 1);
            }
            $examTitle = $exam->exam_title;
            $studentName = $student->full_name ?: $student->name;
            $className = $activeClassroom ? $activeClassroom->class_name : 'Siswa';
            $text = "Halo Bapak/Ibu {$homeroomName}, saya {$studentName} (Kelas {$className}) ingin berkonsultasi mengenai dispensasi mengikuti ujian CBT '{$examTitle}' terkait penyelesaian uang sekolah bulan {$monthLabel}.";
            $whatsappUrl = 'https://wa.me/' . $cleanPhone . '?text=' . rawurlencode($text);
        }

        // 4. Mekanisme 2: Periksa Otorisasi Dispensasi dari Wali Kelas
        $dispensation = CbtExamDispensation::where('cbt_exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->active()
            ->with('granter')
            ->first();

        if ($dispensation) {
            return [
                'allowed' => true,
                'is_required' => true,
                'reason' => 'dispensation_granted',
                'is_paid' => false,
                'has_dispensation' => true,
                'dispensation' => $dispensation,
                'target_month' => $targetMonth,
                'target_year' => $targetYear,
                'month_label' => $monthLabel,
                'bill' => null,
                'unpaid_amount' => 0,
                'homeroom_teacher' => $homeroomTeacher,
                'homeroom_name' => $homeroomName,
                'homeroom_phone' => $homeroomPhone,
                'whatsapp_url' => $whatsappUrl,
                'message' => 'Dispensasi ujian aktif diberikan oleh ' . ($dispensation->granter?->name ?? 'Wali Kelas') . ($dispensation->reason ? " (Catatan: {$dispensation->reason})" : ''),
            ];
        }

        // 5. Mekanisme 1: Periksa Pembayaran Uang Sekolah (SPP) pada bulan berkenaan
        $bills = StudentBill::where('student_id', $student->id)
            ->where('month', $targetMonth)
            ->where('year', $targetYear)
            ->whereHas('paymentType', function ($q) {
                $q->where('is_recurring', true)
                  ->orWhere('type_code', 'SPP')
                  ->orWhere('type_name', 'like', '%SPP%')
                  ->orWhere('type_name', 'like', '%Uang Sekolah%');
            })
            ->with('paymentType')
            ->get();

        if ($bills->isNotEmpty()) {
            $unpaidBills = $bills->filter(function ($b) {
                return $b->status !== 'lunas' && $b->getRemainingAmount() > 0 && $b->amount > 0;
            });

            $firstBill = $bills->first();
            $unpaidAmount = (float) $unpaidBills->sum(fn($b) => max(0, $b->amount - $b->paid_amount));

            if ($unpaidBills->isEmpty()) {
                return [
                    'allowed' => true,
                    'is_required' => true,
                    'reason' => 'paid',
                    'is_paid' => true,
                    'has_dispensation' => false,
                    'dispensation' => null,
                    'target_month' => $targetMonth,
                    'target_year' => $targetYear,
                    'month_label' => $monthLabel,
                    'bill' => $firstBill,
                    'unpaid_amount' => 0,
                    'homeroom_teacher' => $homeroomTeacher,
                    'homeroom_name' => $homeroomName,
                    'homeroom_phone' => $homeroomPhone,
                    'whatsapp_url' => $whatsappUrl,
                    'message' => "Uang sekolah bulan {$monthLabel} telah lunas.",
                ];
            }

            return [
                'allowed' => false,
                'is_required' => true,
                'reason' => 'unpaid',
                'is_paid' => false,
                'has_dispensation' => false,
                'dispensation' => null,
                'target_month' => $targetMonth,
                'target_year' => $targetYear,
                'month_label' => $monthLabel,
                'bill' => $firstBill,
                'unpaid_amount' => $unpaidAmount,
                'homeroom_teacher' => $homeroomTeacher,
                'homeroom_name' => $homeroomName,
                'homeroom_phone' => $homeroomPhone,
                'whatsapp_url' => $whatsappUrl,
                'message' => "Uang sekolah bulan {$monthLabel} belum lunas (sisa tagihan: Rp " . number_format($unpaidAmount, 0, ',', '.') . "). Silakan selesaikan pembayaran atau hubungi Wali Kelas.",
            ];
        }

        // Jika belum ada tagihan SPP spesifik di bulan tersebut, periksa apakah ada tunggakan di tahun ajaran aktif
        $hasAnyOverdue = StudentBill::where('student_id', $student->id)
            ->where('academic_year_id', $exam->academic_year_id)
            ->where('status', '!=', 'lunas')
            ->where(function ($q) use ($targetYear, $targetMonth) {
                $q->where('year', '<', $targetYear)
                  ->orWhere(function ($q2) use ($targetYear, $targetMonth) {
                      $q2->where('year', '=', $targetYear)
                         ->where('month', '<=', $targetMonth);
                  });
            })
            ->whereHas('paymentType', function ($q) {
                $q->where('is_recurring', true)
                  ->orWhere('type_code', 'SPP');
            })
            ->first();

        if ($hasAnyOverdue) {
            $unpaidAmount = (float) max(0, $hasAnyOverdue->amount - $hasAnyOverdue->paid_amount);
            return [
                'allowed' => false,
                'is_required' => true,
                'reason' => 'unpaid',
                'is_paid' => false,
                'has_dispensation' => false,
                'dispensation' => null,
                'target_month' => $targetMonth,
                'target_year' => $targetYear,
                'month_label' => $monthLabel,
                'bill' => $hasAnyOverdue,
                'unpaid_amount' => $unpaidAmount,
                'homeroom_teacher' => $homeroomTeacher,
                'homeroom_name' => $homeroomName,
                'homeroom_phone' => $homeroomPhone,
                'whatsapp_url' => $whatsappUrl,
                'message' => "Terdapat tunggakan uang sekolah hingga bulan {$monthLabel}. Silakan selesaikan pembayaran atau hubungi Wali Kelas.",
            ];
        }

        // Tidak ada tagihan sama sekali (misal beasiswa penuh / tagihan belum dibuat)
        return [
            'allowed' => true,
            'is_required' => true,
            'reason' => 'paid',
            'is_paid' => true,
            'has_dispensation' => false,
            'dispensation' => null,
            'target_month' => $targetMonth,
            'target_year' => $targetYear,
            'month_label' => $monthLabel,
            'bill' => null,
            'unpaid_amount' => 0,
            'homeroom_teacher' => $homeroomTeacher,
            'homeroom_name' => $homeroomName,
            'homeroom_phone' => $homeroomPhone,
            'whatsapp_url' => $whatsappUrl,
            'message' => 'Tidak ditemukan tagihan tertunggak untuk siswa ini.',
        ];
    }

    /**
     * Dapatkan rekapitulasi kepatuhan untuk seluruh siswa di satu rombel/kelas.
     */
    public function getClassroomComplianceOverview(CbtExam $exam, Classroom $classroom): array
    {
        $students = $classroom->students()
            ->wherePivot('status', 'aktif')
            ->when($exam->academic_year_id, fn($q) => $q->wherePivot('academic_year_id', $exam->academic_year_id))
            ->orderBy('students.name')
            ->get();

        $overview = [
            'total_students' => $students->count(),
            'paid_count' => 0,
            'dispensation_count' => 0,
            'blocked_count' => 0,
            'students' => collect(),
        ];

        foreach ($students as $student) {
            $compliance = $this->checkStudentCompliance($exam, $student);

            if ($compliance['has_dispensation']) {
                $overview['dispensation_count']++;
            } elseif ($compliance['allowed']) {
                $overview['paid_count']++;
            } else {
                $overview['blocked_count']++;
            }

            $overview['students']->push([
                'student' => $student,
                'compliance' => $compliance,
            ]);
        }

        return $overview;
    }

    /**
     * Berikan dispensasi ujian kepada siswa.
     */
    public function grantDispensation(CbtExam $exam, Student $student, ?string $reason, int $userId): CbtExamDispensation
    {
        $activeClassroom = $student->currentClassroom()->first() ?? $student->classroom;

        return CbtExamDispensation::updateOrCreate(
            [
                'cbt_exam_id' => $exam->id,
                'student_id' => $student->id,
            ],
            [
                'classroom_id' => $activeClassroom?->id,
                'granted_by' => $userId,
                'status' => CbtExamDispensation::STATUS_GRANTED,
                'reason' => $reason,
                'granted_at' => now(),
                'revoked_at' => null,
            ]
        );
    }

    /**
     * Cabut dispensasi ujian siswa.
     */
    public function revokeDispensation(CbtExam $exam, Student $student, ?int $userId = null): bool
    {
        $dispensation = CbtExamDispensation::where('cbt_exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->first();

        if ($dispensation) {
            $dispensation->revoke($userId);
            return true;
        }

        return false;
    }
}
