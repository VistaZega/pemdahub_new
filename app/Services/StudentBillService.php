<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\PaymentType;
use App\Models\Student;
use App\Models\StudentBill;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StudentBillService
{
    // ──────────────────────────────────────────────
    //  Bulk Bill Generation
    // ──────────────────────────────────────────────

    /**
     * Generate recurring (monthly SPP) bills for a set of students.
     *
     * @return int  Number of bills created.
     */
    public function generateRecurringBills(
        Collection $students,
        array $validated,
        int $generateMonths,
        int $startMonth,
        float $amount,
        int $dueDay,
    ): int {
        $academicYear = AcademicYear::find($validated['academic_year_id']);
        
        // Extract the first 4-digit number (e.g., "TP. 2024/2025" -> 2024)
        if (preg_match('/\d{4}/', $academicYear->year, $matches)) {
            $baseYear = (int)$matches[0];
        } else {
            $yearParts = explode('/', $academicYear->year);
            $baseYear = (int)preg_replace('/[^0-9]/', '', $yearParts[0]);
        }

        $paymentType = PaymentType::find($validated['payment_type_id']);
        $yayasanShareAmount = $paymentType ? $paymentType->yayasan_share_amount : null;
        $billsCreated = 0;

        DB::transaction(function () use (
            $students, $validated, $generateMonths, $startMonth, $amount, $baseYear, $yayasanShareAmount, &$billsCreated
        ) {
            foreach ($students as $student) {
                for ($i = 0; $i < $generateMonths; $i++) {
                    $month = $startMonth + $i;
                    $year = $baseYear;

                    if ($month > 12) {
                        $month -= 12;
                        $year++;
                    }

                    // Prevent duplicate monthly bills
                    $exists = StudentBill::where('student_id', $student->id)
                        ->where('payment_type_id', $validated['payment_type_id'])
                        ->where('academic_year_id', $validated['academic_year_id'])
                        ->where('month', $month)
                        ->where('year', $year)
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    StudentBill::create([
                        'student_id' => $student->id,
                        'payment_type_id' => $validated['payment_type_id'],
                        'academic_year_id' => $validated['academic_year_id'],
                        'semester_id' => $validated['semester_id'] ?? null,
                        'month' => $month,
                        'year' => $year,
                        'amount' => $amount,
                        'yayasan_share_amount' => $yayasanShareAmount,
                        'paid_amount' => 0,
                        'status' => 'belum_bayar',
                        'notes' => $validated['notes'] ?? null,
                    ]);

                    $billsCreated++;
                }
            }
        });

        return $billsCreated;
    }

    /**
     * Generate a single (non-recurring) bill for a set of students.
     *
     * @return int  Number of bills created.
     */
    public function generateSingleBills(
        Collection $students,
        array $validated,
        float $amount,
        int $singleMonth,
    ): int {
        $academicYear = AcademicYear::find($validated['academic_year_id']);
        
        // Extract the first 4-digit number
        if (preg_match('/\d{4}/', $academicYear->year, $matches)) {
            $baseYear = (int)$matches[0];
        } else {
            $yearParts = explode('/', $academicYear->year);
            $baseYear = (int)preg_replace('/[^0-9]/', '', $yearParts[0]);
        }
        
        $billYear = $singleMonth <= 6 ? $baseYear + 1 : $baseYear;
        
        $paymentType = PaymentType::find($validated['payment_type_id']);
        $yayasanShareAmount = $paymentType ? $paymentType->yayasan_share_amount : null;

        $billsCreated = 0;

        DB::transaction(function () use (
            $students, $validated, $amount, $singleMonth, $billYear, $yayasanShareAmount, &$billsCreated
        ) {
            foreach ($students as $student) {
                // Prevent duplicate single bills
                $exists = StudentBill::where('student_id', $student->id)
                    ->where('payment_type_id', $validated['payment_type_id'])
                    ->where('academic_year_id', $validated['academic_year_id'])
                    ->where('month', $singleMonth)
                    ->where('year', $billYear)
                    ->exists();

                if ($exists) {
                    continue;
                }

                StudentBill::create([
                    'student_id' => $student->id,
                    'payment_type_id' => $validated['payment_type_id'],
                    'academic_year_id' => $validated['academic_year_id'],
                    'semester_id' => $validated['semester_id'] ?? null,
                    'month' => $singleMonth,
                    'year' => $billYear,
                    'amount' => $amount,
                    'yayasan_share_amount' => $yayasanShareAmount,
                    'paid_amount' => 0,
                    'status' => 'belum_bayar',
                    'notes' => $validated['notes'] ?? null,
                ]);
                $billsCreated++;
            }
        });

        return $billsCreated;
    }

    // ──────────────────────────────────────────────
    //  Late Fee Waiver
    // ──────────────────────────────────────────────

    /**
     * Waive late fees for a batch of bills.
     *
     * @return int  Number of bills updated.
     */
    public function waiveLateFees(array $billIds, string $reason, int $userId): int
    {
        return DB::transaction(function () use ($billIds, $reason, $userId) {
            $updated = StudentBill::whereIn('id', $billIds)
                ->update([
                    'late_fee_waived' => true,
                    'waiver_reason' => $reason,
                    'waived_by' => $userId,
                    'waived_at' => now(),
                ]);

            \App\Models\ActivityLog::create([
                'user_id' => $userId,
                'activity_type' => 'waive_late_fee',
                'description' => "Menghapus biaya administrasi keterlambatan untuk {$updated} tagihan. Alasan: {$reason}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return $updated;
        });
    }

    // ──────────────────────────────────────────────
    //  Student Query Builder (shared by bulkStore filter logic)
    // ──────────────────────────────────────────────

    /**
     * Get students matching the given filter criteria.
     */
    public function getFilteredStudents(int $schoolId, string $filterBy, ?int $classroomId, ?int $gradeLevel, int $academicYearId, ?string $classType = null): Collection
    {
        $query = Student::where('school_id', $schoolId);

        if ($filterBy === 'classroom' && $classroomId) {
            $query->whereHas('classrooms', function ($q) use ($classroomId, $academicYearId) {
                $q->where('classrooms.id', $classroomId)
                  ->where('student_classes.academic_year_id', $academicYearId)
                  ->where('student_classes.status', 'aktif');
            });
        } elseif ($filterBy === 'grade' && $gradeLevel) {
            $query->whereHas('classrooms', function ($q) use ($gradeLevel, $academicYearId) {
                $q->where('grade_level', $gradeLevel)
                  ->where('student_classes.academic_year_id', $academicYearId)
                  ->where('student_classes.status', 'aktif');
            });
        } elseif ($filterBy === 'class_type' && $classType) {
            $query->whereHas('classrooms', function ($q) use ($classType, $academicYearId) {
                $q->where('class_type', $classType)
                  ->where('student_classes.academic_year_id', $academicYearId)
                  ->where('student_classes.status', 'aktif');
            });
        } else {
            // Filter all students who have active class assignments in this academic year
            $query->whereHas('classrooms', function ($q) use ($academicYearId) {
                $q->where('student_classes.academic_year_id', $academicYearId)
                  ->where('student_classes.status', 'aktif');
            });
        }

        return $query->get();
    }

    // ──────────────────────────────────────────────
    //  Bulk Update & Bulk Delete Operations
    // ──────────────────────────────────────────────

    /**
     * Update bill amount in bulk based on selected bill IDs.
     */
    public function bulkUpdateAmount(array $billIds, float $newAmount, int $userId): int
    {
        return DB::transaction(function () use ($billIds, $newAmount, $userId) {
            $updated = StudentBill::whereIn('id', $billIds)
                ->where('paid_amount', '<=', $newAmount)
                ->where('status', '!=', 'lunas')
                ->update([
                    'amount' => $newAmount,
                ]);

            \App\Models\ActivityLog::create([
                'user_id' => $userId,
                'action' => 'update',
                'description' => "Mengubah nominal tagihan massal menjadi Rp " . number_format($newAmount, 0, ',', '.') . " untuk {$updated} tagihan.",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'logged_at' => now(),
            ]);

            return $updated;
        });
    }

    /**
     * Delete bills in bulk (only bills with NO payments and paid_amount == 0).
     */
    public function bulkDeleteBills(array $billIds, int $userId): array
    {
        return DB::transaction(function () use ($billIds, $userId) {
            $bills = StudentBill::whereIn('id', $billIds)
                ->withCount('payments')
                ->get();

            $deletedCount = 0;
            $skippedCount = 0;

            foreach ($bills as $bill) {
                if ($bill->payments_count > 0 || $bill->paid_amount > 0) {
                    $skippedCount++;
                    continue;
                }

                $bill->delete();
                $deletedCount++;
            }

            if ($deletedCount > 0) {
                \App\Models\ActivityLog::create([
                    'user_id' => $userId,
                    'action' => 'delete',
                    'description' => "Menghapus massal {$deletedCount} tagihan siswa yang belum dibayar.",
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'logged_at' => now(),
                ]);
            }

            return [
                'deleted' => $deletedCount,
                'skipped' => $skippedCount,
            ];
        });
    }
}

