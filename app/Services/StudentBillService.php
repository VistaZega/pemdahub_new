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
     * Extract 4-digit base year from an AcademicYear record (e.g. "TP. 2026/2027" -> 2026).
     */
    public function parseAcademicBaseYear(?AcademicYear $academicYear): int
    {
        if (!$academicYear || !$academicYear->year) {
            return (int) date('Y');
        }

        if (preg_match('/\d{4}/', $academicYear->year, $matches)) {
            return (int) $matches[0];
        }

        $yearParts = explode('/', $academicYear->year);
        $baseYearStr = preg_replace('/[^0-9]/', '', $yearParts[0]);
        $baseYear = (int) $baseYearStr;

        if ($baseYear > 0 && $baseYear < 100) {
            return $baseYear + 2000;
        }

        return $baseYear > 0 ? $baseYear : (int) date('Y');
    }

    /**
     * Self-healing & deduplication for student_bills:
     * 1. Fix corrupted year (< 2000) caused by (int) $academicYear->year on "TP. 2026/2027".
     * 2. Merge duplicate monthly bills for the same (student_id, payment_type_id, academic_year_id, month).
     * 3. Fix Shandyani Lafau's swapped September -> August payment if August is unpaid while September is lunas.
     * 4. Reconcile paid_amount & status when a discounted payment left a bill in 'cicilan'.
     */
    public function healAndDeduplicateBills(?int $schoolId = null, ?int $academicYearId = null): void
    {
        try {
            DB::transaction(function () use ($schoolId, $academicYearId) {
                // 1. Fix corrupted year (< 2000)
                $corruptedBills = StudentBill::with('academicYear')
                    ->where(function ($q) {
                        $q->whereNull('year')->orWhere('year', '<', 2000);
                    })
                    ->when($academicYearId, fn($q) => $q->where('academic_year_id', $academicYearId))
                    ->when($schoolId, fn($q) => $q->whereHas('student', fn($sq) => $sq->where('school_id', $schoolId)))
                    ->get();

                foreach ($corruptedBills as $cb) {
                    $baseYear = $this->parseAcademicBaseYear($cb->academicYear);
                    $correctYear = ($cb->month && $cb->month < 7) ? ($baseYear + 1) : $baseYear;
                    $cb->update(['year' => $correctYear]);
                }

                // 2. Deduplicate monthly bills for the same (student_id, payment_type_id, academic_year_id, month)
                $dupGroups = DB::table('student_bills')
                    ->join('students', 'student_bills.student_id', '=', 'students.id')
                    ->whereNotNull('student_bills.month')
                    ->when($academicYearId, fn($q) => $q->where('student_bills.academic_year_id', $academicYearId))
                    ->when($schoolId, fn($q) => $q->where('students.school_id', $schoolId))
                    ->select('student_bills.student_id', 'student_bills.payment_type_id', 'student_bills.academic_year_id', 'student_bills.month')
                    ->groupBy('student_bills.student_id', 'student_bills.payment_type_id', 'student_bills.academic_year_id', 'student_bills.month')
                    ->havingRaw('COUNT(*) > 1')
                    ->get();

                foreach ($dupGroups as $grp) {
                    $dupBills = StudentBill::where('student_id', $grp->student_id)
                        ->where('payment_type_id', $grp->payment_type_id)
                        ->where('academic_year_id', $grp->academic_year_id)
                        ->where('month', $grp->month)
                        ->orderByRaw("CASE WHEN status = 'lunas' THEN 0 WHEN paid_amount > 0 THEN 1 ELSE 2 END")
                        ->orderByDesc('paid_amount')
                        ->orderBy('id')
                        ->get();

                    if ($dupBills->count() <= 1) {
                        continue;
                    }

                    $keeper = $dupBills->first();
                    $others = $dupBills->slice(1);

                    foreach ($others as $other) {
                        // Move any payments attached to the duplicate bill over to the keeper bill
                        \App\Models\Payment::where('bill_id', $other->id)->update(['bill_id' => $keeper->id]);

                        // If the duplicate bill had a discounted amount and keeper was not discounted yet
                        if ($other->amount > 0 && $other->amount < $keeper->amount && $keeper->status !== 'lunas') {
                            $keeper->amount = $other->amount;
                            $keeper->discount_amount = max((float) $keeper->discount_amount, (float) $other->discount_amount);
                            $keeper->discount_reason = $keeper->discount_reason ?: $other->discount_reason;
                        }

                        $other->delete();
                    }

                    $verifiedSum = (float) \App\Models\Payment::where('bill_id', $keeper->id)
                        ->where('is_verified', true)
                        ->sum('amount_paid');
                    $keeper->paid_amount = max((float) $keeper->paid_amount, $verifiedSum);

                    if ($keeper->amount <= 0 || $keeper->paid_amount >= $keeper->amount) {
                        $keeper->status = 'lunas';
                    } elseif ($keeper->paid_amount > 0) {
                        $keeper->status = 'cicilan';
                    } else {
                        $keeper->status = 'belum_bayar';
                    }
                    $keeper->save();
                }

                // 3. Specific heal for Shandyani Lafau if September (9) was marked lunas while August (8) is still belum_bayar
                $shandyaniStudents = Student::where('full_name', 'like', '%Shandyani Lafau%')
                    ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
                    ->pluck('id');

                foreach ($shandyaniStudents as $shId) {
                    $shBills = StudentBill::where('student_id', $shId)
                        ->when($academicYearId, fn($q) => $q->where('academic_year_id', $academicYearId))
                        ->whereIn('month', [7, 8, 9, 10, 11, 12])
                        ->get()
                        ->groupBy(fn($b) => $b->academic_year_id . '_' . $b->payment_type_id);

                    foreach ($shBills as $groupBills) {
                        $augBill = $groupBills->firstWhere('month', 8);
                        $sepBill = $groupBills->firstWhere('month', 9);
                        $octBill = $groupBills->firstWhere('month', 10);

                        if ($augBill && $sepBill && $augBill->status === 'belum_bayar' && $augBill->paid_amount == 0 && $sepBill->status === 'lunas' && (!$octBill || $octBill->status !== 'lunas')) {
                            // Move payment from September to August
                            \App\Models\Payment::where('bill_id', $sepBill->id)->update(['bill_id' => $augBill->id]);

                            $augBill->amount = $sepBill->amount;
                            $augBill->discount_amount = $sepBill->discount_amount;
                            $augBill->discount_reason = $sepBill->discount_reason;
                            $augBill->paid_amount = $sepBill->paid_amount;
                            $augBill->status = 'lunas';
                            $augBill->save();

                            $sepBill->paid_amount = 0;
                            $sepBill->status = 'belum_bayar';
                            $sepBill->save();

                            // Ensure discount/nominal carries over through end of semester (Desember)
                            $this->carryOverDiscountToSubsequentMonths(
                                $augBill,
                                (float) $augBill->amount,
                                (float) $augBill->discount_amount,
                                $augBill->discount_reason,
                                $augBill->notes,
                                null,
                                'semester'
                            );
                        }
                    }
                }

                // 4. Reconcile bills stuck in 'cicilan' or 'belum_bayar' when verified payments or sibling semester discounts exist
                $partialOrOutOfSyncBills = StudentBill::where('status', '!=', 'lunas')
                    ->where(function ($q) {
                        $q->where('paid_amount', '>', 0)
                          ->orWhereHas('payments', fn($pq) => $pq->where('is_verified', true));
                    })
                    ->when($academicYearId, fn($q) => $q->where('academic_year_id', $academicYearId))
                    ->when($schoolId, fn($q) => $q->whereHas('student', fn($sq) => $sq->where('school_id', $schoolId)))
                    ->get();

                foreach ($partialOrOutOfSyncBills as $pBill) {
                    $verifiedPaid = (float) \App\Models\Payment::where('bill_id', $pBill->id)
                        ->where('is_verified', true)
                        ->sum('amount_paid');
                    $effectivePaid = max((float) $pBill->paid_amount, $verifiedPaid);

                    // Check if another bill in the same semester for the same student & payment_type has a discounted tariff <= effectivePaid
                    if ($effectivePaid > 0 && $effectivePaid < (float) $pBill->amount && $pBill->month) {
                        $semesterMonths = $pBill->month >= 7 ? [7, 8, 9, 10, 11, 12] : [1, 2, 3, 4, 5, 6];
                        $discountedSibling = StudentBill::where('student_id', $pBill->student_id)
                            ->where('payment_type_id', $pBill->payment_type_id)
                            ->where('academic_year_id', $pBill->academic_year_id)
                            ->whereIn('month', $semesterMonths)
                            ->where('id', '!=', $pBill->id)
                            ->where('amount', '>', 0)
                            ->where('amount', '<=', $effectivePaid)
                            ->first();

                        if ($discountedSibling) {
                            $oldAmount = (float) $pBill->amount;
                            $pBill->amount = (float) $discountedSibling->amount;
                            $pBill->discount_amount = max((float) $pBill->discount_amount, $oldAmount - (float) $discountedSibling->amount);
                            $pBill->discount_reason = $pBill->discount_reason ?: ($discountedSibling->discount_reason ?: 'Penyesuaian Diskon/Beasiswa Semester');
                        }
                    }

                    $pBill->paid_amount = $effectivePaid;
                    if ($pBill->amount <= 0 || $effectivePaid >= (float) $pBill->amount) {
                        $pBill->status = 'lunas';
                    } elseif ($effectivePaid > 0) {
                        $pBill->status = 'cicilan';
                    } else {
                        $pBill->status = 'belum_bayar';
                    }
                    $pBill->save();
                }
            });
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('StudentBillService::healAndDeduplicateBills warning: ' . $e->getMessage());
        }
    }

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
        $baseYear = $this->parseAcademicBaseYear($academicYear);

        $paymentType = PaymentType::find($validated['payment_type_id']);

        // Use custom amount if provided, otherwise fallback to payment type default or bill amount
        $yayasanShareAmount = null;
        if (array_key_exists('monthly_yayasan_share_amount', $validated) && $validated['monthly_yayasan_share_amount'] !== null && $validated['monthly_yayasan_share_amount'] !== '') {
            $yayasanShareAmount = (float) $validated['monthly_yayasan_share_amount'];
        } elseif ($paymentType) {
            $yayasanShareAmount = (float) ($paymentType->yayasan_share_amount ?? $paymentType->amount ?? $amount);
        } else {
            $yayasanShareAmount = (float) $amount;
        }
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

                    // Prevent duplicate monthly bills (match by academic_year_id + month regardless of year column)
                    $exists = StudentBill::where('student_id', $student->id)
                        ->where('payment_type_id', $validated['payment_type_id'])
                        ->where('academic_year_id', $validated['academic_year_id'])
                        ->where('month', $month)
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    // Auto-inherit custom amount/discount from previous bill in the same academic year
                    $prevBill = StudentBill::where('student_id', $student->id)
                        ->where('payment_type_id', $validated['payment_type_id'])
                        ->where('academic_year_id', $validated['academic_year_id'])
                        ->where('month', '!=', $month)
                        ->orderBy('created_at', 'desc')
                        ->first();

                    $billAmount = $prevBill ? (float)$prevBill->amount : $amount;
                    $billYayasanShare = ($prevBill && $prevBill->yayasan_share_amount !== null)
                        ? (float)$prevBill->yayasan_share_amount
                        : $yayasanShareAmount;

                    StudentBill::create([
                        'student_id' => $student->id,
                        'payment_type_id' => $validated['payment_type_id'],
                        'academic_year_id' => $validated['academic_year_id'],
                        'semester_id' => $validated['semester_id'] ?? null,
                        'month' => $month,
                        'year' => $year,
                        'amount' => $billAmount,
                        'yayasan_share_amount' => $billYayasanShare,
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
        $baseYear = $this->parseAcademicBaseYear($academicYear);
        
        $billYear = $singleMonth <= 6 ? $baseYear + 1 : $baseYear;
        
        $paymentType = PaymentType::find($validated['payment_type_id']);
        
        // Use custom amount if provided, otherwise fallback to payment type default or bill amount
        $yayasanShareAmount = null;
        if (array_key_exists('yayasan_share_amount', $validated) && $validated['yayasan_share_amount'] !== null && $validated['yayasan_share_amount'] !== '') {
            $yayasanShareAmount = (float) $validated['yayasan_share_amount'];
        } elseif ($paymentType) {
            $yayasanShareAmount = (float) ($paymentType->yayasan_share_amount ?? $paymentType->amount ?? $amount);
        } else {
            $yayasanShareAmount = (float) $amount;
        }

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
                    ->exists();

                if ($exists) {
                    continue;
                }

                // Auto-inherit custom amount/discount from previous bill in the same academic year
                $prevBill = StudentBill::where('student_id', $student->id)
                    ->where('payment_type_id', $validated['payment_type_id'])
                    ->where('academic_year_id', $validated['academic_year_id'])
                    ->where('month', '!=', $singleMonth)
                    ->orderBy('created_at', 'desc')
                    ->first();

                $billAmount = $prevBill ? (float)$prevBill->amount : $amount;
                $billYayasanShare = ($prevBill && $prevBill->yayasan_share_amount !== null)
                    ? (float)$prevBill->yayasan_share_amount
                    : $yayasanShareAmount;

                StudentBill::create([
                    'student_id' => $student->id,
                    'payment_type_id' => $validated['payment_type_id'],
                    'academic_year_id' => $validated['academic_year_id'],
                    'semester_id' => $validated['semester_id'] ?? null,
                    'month' => $singleMonth,
                    'year' => $billYear,
                    'amount' => $billAmount,
                    'yayasan_share_amount' => $billYayasanShare,
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

    /**
     * Bawa potongan / penyesuaian tarif ke bulan-bulan berikutnya (sampai akhir semester atau akhir tahun ajaran).
     *
     * @param StudentBill $bill
     * @param float $newAmount
     * @param float $discountAmount
     * @param string|null $discountReason
     * @param string|null $notes
     * @param int|null $userId
     * @param string $scope 'semester' (default: s.d. akhir semester) atau 'academic_year' (s.d. akhir tahun pelajaran)
     * @return int Jumlah tagihan bulan lain yang berhasil disesuaikan
     */
    public function carryOverDiscountToSubsequentMonths(
        StudentBill $bill,
        float $newAmount,
        float $discountAmount = 0,
        ?string $discountReason = null,
        ?string $notes = null,
        ?int $userId = null,
        string $scope = 'semester'
    ): int {
        if (!$bill->month) {
            return 0;
        }

        $currentMonth = (int) $bill->month;
        if ($scope === 'semester') {
            // Semester Ganjil: Juli (7) s.d. Desember (12), Semester Genap: Januari (1) s.d. Juni (6)
            $semesterOrder = $currentMonth >= 7 ? [7, 8, 9, 10, 11, 12] : [1, 2, 3, 4, 5, 6];
            $currentPos = array_search($currentMonth, $semesterOrder);
            if ($currentPos === false) {
                return 0;
            }
            $subsequentMonths = array_slice($semesterOrder, $currentPos + 1);
            // Juga sertakan bulan-bulan di semester yang sama yang masih berstatus 'cicilan' (agar jika diskon diatur di bulan setelahnya, bulan sebelumnya yang sudah dibayar sesuai nominal diskon ikut menjadi lunas)
            $sameSemesterEarlierMonths = array_slice($semesterOrder, 0, $currentPos);
        } else {
            $academicMonthOrder = [7, 8, 9, 10, 11, 12, 1, 2, 3, 4, 5, 6];
            $currentPos = array_search($currentMonth, $academicMonthOrder);
            if ($currentPos === false) {
                return 0;
            }
            $subsequentMonths = array_slice($academicMonthOrder, $currentPos + 1);
            $sameSemesterEarlierMonths = array_slice($academicMonthOrder, 0, $currentPos);
        }

        $targetMonths = array_values(array_unique(array_merge($subsequentMonths, $sameSemesterEarlierMonths)));
        if (empty($targetMonths)) {
            return 0;
        }

        $candidateBills = StudentBill::where('student_id', $bill->student_id)
            ->where('payment_type_id', $bill->payment_type_id)
            ->where('academic_year_id', $bill->academic_year_id)
            ->whereIn('month', $targetMonths)
            ->where('id', '!=', $bill->id)
            ->where('status', '!=', 'lunas')
            ->get();

        $updatedCount = 0;
        foreach ($candidateBills as $subBill) {
            $isSubsequent = in_array((int) $subBill->month, $subsequentMonths, true);
            $paidSoFar = (float) $subBill->paid_amount;

            // Untuk bulan berikutnya yang belum dibayar, atau bulan di semester yang sama yang pembayarannya <= nominal diskon baru
            if (($isSubsequent && $paidSoFar <= $newAmount) || (!$isSubsequent && $paidSoFar > 0 && $paidSoFar <= $newAmount)) {
                $effectiveDiscount = $discountAmount > 0
                    ? $discountAmount
                    : max(0, (float) $subBill->amount - $newAmount);

                $updateData = [
                    'amount' => $newAmount,
                    'discount_amount' => $effectiveDiscount,
                ];
                if ($discountReason !== null && $discountReason !== '') {
                    $updateData['discount_reason'] = $discountReason;
                }
                if ($notes !== null && $notes !== '') {
                    $updateData['notes'] = $notes;
                }

                if ($newAmount <= 0 || $paidSoFar >= $newAmount) {
                    $updateData['status'] = 'lunas';
                } elseif ($paidSoFar > 0) {
                    $updateData['status'] = 'cicilan';
                } else {
                    $updateData['status'] = 'belum_bayar';
                }

                $subBill->update($updateData);
                $updatedCount++;
            }
        }

        if ($updatedCount > 0 && $userId) {
            $scopeLabel = $scope === 'semester' ? 's.d. akhir semester' : 's.d. akhir Tahun Pelajaran';
            \App\Models\ActivityLog::create([
                'user_id' => $userId,
                'action' => 'update',
                'description' => "Menyesuaikan potongan/nominal baru Rp " . number_format($newAmount, 0, ',', '.') . " ke {$updatedCount} tagihan ({$scopeLabel}) untuk {$bill->student->full_name}.",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'logged_at' => now(),
            ]);
        }

        return $updatedCount;
    }

    /**
     * Pindahkan pembayaran dari satu bulan tagihan (mis. September) ke bulan tagihan lain (mis. Agustus)
     * untuk siswa, jenis tagihan, dan tahun ajaran yang sama.
     */
    public function movePaymentToMonth(StudentBill $sourceBill, int $targetMonth, ?int $userId = null): StudentBill
    {
        return DB::transaction(function () use ($sourceBill, $targetMonth, $userId) {
            $targetBill = StudentBill::where('student_id', $sourceBill->student_id)
                ->where('payment_type_id', $sourceBill->payment_type_id)
                ->where('academic_year_id', $sourceBill->academic_year_id)
                ->where('month', $targetMonth)
                ->orderBy('id')
                ->first();

            if (!$targetBill) {
                $baseYear = $this->parseAcademicBaseYear($sourceBill->academicYear);
                $targetYear = $targetMonth < 7 ? ($baseYear + 1) : $baseYear;
                $targetBill = StudentBill::create([
                    'student_id' => $sourceBill->student_id,
                    'payment_type_id' => $sourceBill->payment_type_id,
                    'academic_year_id' => $sourceBill->academic_year_id,
                    'semester_id' => $sourceBill->semester_id,
                    'month' => $targetMonth,
                    'year' => $targetYear,
                    'amount' => $sourceBill->amount,
                    'discount_amount' => $sourceBill->discount_amount,
                    'discount_reason' => $sourceBill->discount_reason,
                    'yayasan_share_amount' => $sourceBill->yayasan_share_amount,
                    'paid_amount' => 0,
                    'status' => 'belum_bayar',
                ]);
            }

            // Pindahkan semua record Payment dari sourceBill ke targetBill
            \App\Models\Payment::where('bill_id', $sourceBill->id)->update(['bill_id' => $targetBill->id]);

            $movedPaid = (float) $sourceBill->paid_amount;
            $verifiedTargetSum = (float) \App\Models\Payment::where('bill_id', $targetBill->id)
                ->where('is_verified', true)
                ->sum('amount_paid');

            // Jika sourceBill memiliki diskon/penyesuaian tarif, samakan ke targetBill
            if ($sourceBill->amount > 0 && $sourceBill->amount < $targetBill->amount) {
                $targetBill->amount = $sourceBill->amount;
                $targetBill->discount_amount = $sourceBill->discount_amount;
                $targetBill->discount_reason = $sourceBill->discount_reason;
            }

            $targetBill->paid_amount = max((float) $targetBill->paid_amount + $movedPaid, $verifiedTargetSum);
            if ($targetBill->amount <= 0 || $targetBill->paid_amount >= $targetBill->amount) {
                $targetBill->status = 'lunas';
            } elseif ($targetBill->paid_amount > 0) {
                $targetBill->status = 'cicilan';
            } else {
                $targetBill->status = 'belum_bayar';
            }
            $targetBill->save();

            // Reset sourceBill kembali ke belum_bayar (dengan nominal tarif yang sama)
            $sourceBill->paid_amount = 0;
            $sourceBill->status = $sourceBill->amount <= 0 ? 'lunas' : 'belum_bayar';
            $sourceBill->save();

            if ($userId) {
                $monthNames = [
                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                ];
                $fromName = $monthNames[$sourceBill->month] ?? $sourceBill->month;
                $toName = $monthNames[$targetMonth] ?? $targetMonth;
                \App\Models\ActivityLog::create([
                    'user_id' => $userId,
                    'action' => 'update',
                    'description' => "Memindahkan status lunas/pembayaran {$sourceBill->student->full_name} dari bulan {$fromName} ke bulan {$toName}.",
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'logged_at' => now(),
                ]);
            }

            return $targetBill;
        });
    }
}

