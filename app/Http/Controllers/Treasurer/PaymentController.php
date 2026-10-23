<?php

namespace App\Http\Controllers\Treasurer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\StorePaymentRequest;
use App\Http\Requests\Payment\BulkPaymentRequest;
use App\Http\Requests\Payment\BatchPaymentRequest;
use App\Models\Payment;
use App\Models\StudentBill;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\PaymentType;
use App\Exports\PaymentsExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $schoolId = auth()->user()->school_id;

        $query = Payment::with(['student', 'bill.paymentType'])
            ->whereHas('student', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            });

        // Filter by verification status
        if ($request->filled('is_verified')) {
            $query->where('is_verified', $request->is_verified);
        }

        // Filter by payment method
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // Search by student name or reference number
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('receipt_number', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%")
                  ->orWhereHas('student', function ($sq) use ($search) {
                      $sq->where('full_name', 'like', "%{$search}%")
                         ->orWhere('nisn', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('payment_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('payment_date', '<=', $request->end_date);
        }

        // Filter by classroom
        if ($request->filled('classroom_id')) {
            $classroomId = $request->classroom_id;
            $query->whereHas('student.studentClasses', function ($q) use ($classroomId) {
                $q->where('classroom_id', $classroomId);
            });
        }

        // Filter by payment type
        if ($request->filled('payment_type_id')) {
            $paymentTypeId = $request->payment_type_id;
            $query->whereHas('bill', function ($q) use ($paymentTypeId) {
                $q->where('payment_type_id', $paymentTypeId);
            });
        }

        $payments = $query->orderBy('payment_date', 'desc')->paginate(20)->withQueryString();

        $activeAcademicYear = AcademicYear::where('is_active', true)->first();

        $classrooms = Classroom::where('school_id', $schoolId)
            ->when($activeAcademicYear, function ($q) use ($activeAcademicYear) {
                $q->where('academic_year_id', $activeAcademicYear->id);
            })
            ->where('is_active', true)
            ->orderBy('class_name')
            ->get();

        $paymentTypes = PaymentType::where('school_id', $schoolId)
            ->where('is_active', true)
            ->orderBy('type_name')
            ->get();

        return view('treasurer.payments.index', compact('payments', 'classrooms', 'paymentTypes'));
    }

    public function create(Request $request)
    {
        $schoolId = auth()->user()->school_id;

        $students = Student::where('school_id', $schoolId)
            ->where('status', 'aktif')
            ->orderBy('full_name')
            ->get();
        
        $selectedStudentId = $request->get('student_id');
        $selectedBillId = $request->get('bill_id');
        
        $academicMonthOrder = [7, 8, 9, 10, 11, 12, 1, 2, 3, 4, 5, 6];

        $bills = StudentBill::where('status', '!=', 'lunas')
            ->whereHas('student', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            })
            ->with('student', 'paymentType')
            ->get()
            ->unique(function ($b) {
                return $b->student_id . '_' . $b->payment_type_id . '_' . $b->academic_year_id . '_' . ($b->month ?? ('id_' . $b->id));
            })
            ->sortBy(function ($b) use ($academicMonthOrder) {
                $pos = array_search((int) $b->month, $academicMonthOrder);
                return sprintf('%06d_%02d_%08d', (int) $b->academic_year_id, $pos === false ? 99 : $pos, (int) $b->id);
            })
            ->values();

        return view('treasurer.payments.create', compact('students', 'bills', 'selectedStudentId', 'selectedBillId'));
    }

    public function store(StorePaymentRequest $request)
    {
        $validated = $request->validated();
        if (empty($validated['bill_id'])) {
            $validated['bill_id'] = null;
        }

        // Verify student belongs to treasurer's school (or user is superadmin/yayasan)
        $user = auth()->user();
        $student = Student::findOrFail($validated['student_id']);
        if (!$user->isSuperAdmin() && !$user->isYayasan() && $user->school_id && $student->school_id != $user->school_id) {
            abort(403, 'Unauthorized: Anda tidak memiliki akses ke data siswa sekolah ini.');
        }

        $fromBillsPage = $request->boolean('from_bills_page') || str_contains((string) $request->headers->get('referer'), '/bills');

        try {
            return DB::transaction(function () use ($validated, $request, $student, $fromBillsPage) {
                $billService = app(\App\Services\StudentBillService::class);
                $academicMonthOrder = [7, 8, 9, 10, 11, 12, 1, 2, 3, 4, 5, 6];
                $monthNames = [
                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                ];

                // If bill_id was omitted in Catat Pembayaran, auto-attach to the earliest unpaid bill in chronological academic order
                if (empty($validated['bill_id'])) {
                    $activeAcademicYearId = AcademicYear::where('is_active', true)->value('id');
                    $candidateBills = StudentBill::where('student_id', $student->id)
                        ->where('status', '!=', 'lunas')
                        ->when($activeAcademicYearId, fn($q) => $q->where('academic_year_id', $activeAcademicYearId))
                        ->get()
                        ->sortBy(function ($b) use ($academicMonthOrder) {
                            $pos = array_search((int) $b->month, $academicMonthOrder);
                            return $pos === false ? 99 : $pos;
                        });

                    if ($candidateBills->isEmpty()) {
                        $candidateBills = StudentBill::where('student_id', $student->id)
                            ->where('status', '!=', 'lunas')
                            ->get()
                            ->sortBy(function ($b) use ($academicMonthOrder) {
                                $pos = array_search((int) $b->month, $academicMonthOrder);
                                return $pos === false ? 99 : $pos;
                            });
                    }

                    $autoBill = $candidateBills->first();
                    if ($autoBill) {
                        $validated['bill_id'] = $autoBill->id;
                    }
                }

                // If target_month was explicitly chosen in Quick Pay modal and differs from the clicked bill's month
                if (!empty($validated['bill_id']) && $request->filled('target_month')) {
                    $clickedBill = StudentBill::find($validated['bill_id']);
                    $targetMonth = (int) $request->input('target_month');
                    if ($clickedBill && $clickedBill->month && (int) $clickedBill->month !== $targetMonth) {
                        $monthBill = StudentBill::where('student_id', $student->id)
                            ->where('payment_type_id', $clickedBill->payment_type_id)
                            ->where('academic_year_id', $clickedBill->academic_year_id)
                            ->where('month', $targetMonth)
                            ->orderByRaw("CASE WHEN status = 'lunas' THEN 0 WHEN paid_amount > 0 THEN 1 ELSE 2 END")
                            ->first();
                        if ($monthBill) {
                            $validated['bill_id'] = $monthBill->id;
                        }
                    }
                }

                // If applying discount carryover from Quick Pay / store form
                $carriedCount = 0;
                $carryScope = $request->input('carryover_scope', 'semester');
                if ($request->boolean('apply_discount_carryover') && !empty($validated['bill_id'])) {
                    $targetBill = StudentBill::find($validated['bill_id']);
                    if ($targetBill) {
                        $newBillAmount = (float) $targetBill->paid_amount + (float) $validated['amount_paid'];
                        $discountAmount = max((float) $targetBill->discount_amount, (float) $targetBill->amount - $newBillAmount);
                        $discountReason = $request->input('discount_reason') ?: ($targetBill->discount_reason ?: 'Penyesuaian Diskon / Beasiswa SPP');

                        $targetBill->update([
                            'amount' => $newBillAmount,
                            'discount_amount' => $discountAmount,
                            'discount_reason' => $discountReason,
                        ]);

                        $carriedCount = $billService->carryOverDiscountToSubsequentMonths(
                            $targetBill,
                            $newBillAmount,
                            $discountAmount,
                            $discountReason,
                            $validated['notes'] ?? null,
                            auth()->id(),
                            $carryScope
                        );
                    }
                }

                // Lock bill for update if bill_id exists and verify status
                $bill = null;
                if (!empty($validated['bill_id'])) {
                    $bill = StudentBill::where('id', $validated['bill_id'])->lockForUpdate()->first();
                    if ($bill) {
                        $totalOwed = max(0, (float) $bill->amount - (float) $bill->paid_amount) + (float) $bill->late_fee;
                        if (!$request->boolean('apply_discount_carryover') && ($bill->status === 'lunas' || ($totalOwed <= 0 && $bill->paid_amount > 0))) {
                            $redirectRoute = $fromBillsPage ? 'treasurer.bills.index' : 'treasurer.payments.index';
                            return redirect()->route($redirectRoute)
                                ->with('warning', 'Tagihan bulan ini sudah berstatus Lunas.');
                        }
                    }
                }

                // Anti-Duplicate Window Check (10 seconds)
                $recentDuplicate = Payment::where('student_id', $validated['student_id'])
                    ->when(!empty($validated['bill_id']), fn($q) => $q->where('bill_id', $validated['bill_id']))
                    ->where('amount_paid', $validated['amount_paid'])
                    ->where('created_at', '>=', now()->subSeconds(10))
                    ->first();

                if ($recentDuplicate) {
                    $redirectRoute = $fromBillsPage ? 'treasurer.bills.index' : 'treasurer.payments.index';
                    return redirect()->route($redirectRoute)
                        ->with('warning', 'Pembayaran yang sama baru saja dicatat beberapa detik lalu (mencegah pembayaran ganda).');
                }

                $adminId = \App\Models\User::where('school_id', $student->school_id)
                    ->whereIn('role', ['admin', 'admin_sekolah'])
                    ->where('is_active', true)
                    ->first()?->id ?? auth()->id();

                $validated['is_verified'] = true;
                $validated['verified_by'] = $adminId;
                $validated['verified_at'] = now();
                $validated['processed_by'] = auth()->id();
                $validated['receipt_number'] = $this->generateReceiptNumber();

                $payment = Payment::create($validated);

                $periodLabel = '';
                if ($payment->bill_id) {
                    $bill = StudentBill::find($payment->bill_id);
                    if ($bill) {
                        $totalPaid = (float) Payment::where('bill_id', $bill->id)
                            ->where('is_verified', true)
                            ->sum('amount_paid');

                        $bill->paid_amount = max((float) $bill->paid_amount, $totalPaid);

                        if ($bill->amount <= 0 || $bill->paid_amount >= $bill->amount) {
                            $bill->status = 'lunas';
                        } elseif ($bill->paid_amount > 0) {
                            $bill->status = 'cicilan';
                        } else {
                            $bill->status = 'belum_bayar';
                        }

                        $bill->save();

                        // Deduplicate any shadow duplicate bills for this student's academic year
                        $billService->healAndDeduplicateBills((int) $student->school_id, (int) $bill->academic_year_id);

                        $mName = $bill->month ? ($monthNames[$bill->month] ?? ('Bulan ' . $bill->month)) : '';
                        $periodLabel = trim(($bill->paymentType->type_name ?? 'Tagihan') . ($mName ? " - {$mName} {$bill->year}" : ''));
                    }
                }

                $statusInfo = ($bill && $bill->status === 'lunas') ? 'Lunas ✓' : 'Cicilan (Belum Lunas Penuh)';
                $successMsg = "Pembayaran {$student->full_name}" . ($periodLabel ? " ({$periodLabel})" : '') . " sebesar Rp " . number_format((float) $payment->amount_paid, 0, ',', '.') . " berhasil disimpan — Status: {$statusInfo}.";
                if ($carriedCount > 0) {
                    $scopeText = $carryScope === 'semester' ? 'sampai akhir semester ini' : 'sampai akhir Tahun Pelajaran';
                    $successMsg .= " Nominal baru juga diterapkan ke {$carriedCount} bulan berikutnya ({$scopeText}).";
                }

                if ($fromBillsPage) {
                    $referer = (string) $request->headers->get('referer');
                    if ($referer && str_contains($referer, '/bills')) {
                        return redirect()->to($referer)
                            ->with('success', $successMsg)
                            ->with('payment_id', $payment->id);
                    }
                    return redirect()->route('treasurer.bills.index', [
                        'academic_year_id' => $bill?->academic_year_id,
                        'payment_type_id' => $bill?->payment_type_id,
                    ])->with('success', $successMsg)->with('payment_id', $payment->id);
                }

                return redirect()->route('treasurer.payments.index')
                    ->with('success', $successMsg)
                    ->with('payment_id', $payment->id);
            });
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan pembayaran: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal menyimpan pembayaran: ' . $e->getMessage());
        }
    }

    public function bulkCreate()
    {
        $schoolId = auth()->user()->school_id;

        // Get academic years (now global)
        $academicYears = AcademicYear::orderBy('year', 'desc')->get();
        $activeAcademicYear = AcademicYear::where('is_active', true)->first();
        
        $classrooms = Classroom::where('school_id', $schoolId)
            ->when($activeAcademicYear, function ($q) use ($activeAcademicYear) {
                $q->where('academic_year_id', $activeAcademicYear->id);
            })
            ->orderBy('class_name')
            ->get();
        
        $paymentTypes = PaymentType::where('school_id', $schoolId)
            ->where('is_active', true)
            ->orderBy('type_name')
            ->get();

        return view('treasurer.payments.bulk-create', compact('academicYears', 'activeAcademicYear', 'classrooms', 'paymentTypes'));
    }

    public function fetchBills(Request $request)
    {
        try {
            $user = auth()->user();
            $schoolId = $user->school_id;
            $academicYearId = $request->academic_year_id;
            $classroomId = $request->classroom_id;
            $paymentTypeId = $request->payment_type_id;
            $month = $request->month;

            // Verify classroom belongs to treasurer's school (unless superadmin/yayasan)
            $classroom = Classroom::findOrFail($classroomId);
            if (!$user->isSuperAdmin() && !$user->isYayasan() && $classroom->school_id != $schoolId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to classroom'
                ], 403);
            }

            // Get students in the classroom
            $studentIds = DB::table('student_classes')
                ->where('classroom_id', $classroomId)
                ->pluck('student_id');

            // Build query for unpaid bills
            $query = StudentBill::with(['student', 'paymentType'])
                ->whereIn('student_id', $studentIds)
                ->when(!$user->isSuperAdmin() && !$user->isYayasan(), function ($q) use ($schoolId) {
                    $q->whereHas('student', function ($sq) use ($schoolId) {
                        $sq->where('school_id', $schoolId);
                    });
                })
                ->where('academic_year_id', $academicYearId)
                ->where('payment_type_id', $paymentTypeId)
                ->whereIn('status', ['belum_bayar', 'cicilan']);

            // Filter by month if provided (for SPP)
            if ($month) {
                $query->where('month', $month);
            }

            $bills = $query->orderBy('student_id')->get();

            // Deduplicate per student + month so duplicate bill records are unique in Pembayaran Massal UI
            $bills = $bills->unique(function($b) {
                return $b->student_id . '_' . $b->payment_type_id . '_' . ($b->month ?? '0');
            })->values();

            return response()->json([
                'success' => true,
                'bills' => $bills
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal mengambil data tagihan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada sistem. Silakan coba lagi.'
            ], 500);
        }
    }

    public function bulkStore(BulkPaymentRequest $request)
    {
        $validated = $request->validated();

        try {
            $user = auth()->user();
            $schoolId = $user->school_id;
            DB::beginTransaction();

            $billIds = $validated['bill_ids'];
            $paymentMethod = $validated['payment_method'];
            $paymentDate = $validated['payment_date'];
            $notes = $validated['notes'] ?? null;
            $userId = auth()->id();

            $successCount = 0;

            foreach ($billIds as $billId) {
                $bill = StudentBill::where('id', $billId)->lockForUpdate()->first();
                
                // Verify bill belongs to treasurer's school & not paid
                if (!$bill || (!$user->isSuperAdmin() && !$user->isYayasan() && $bill->student->school_id != $schoolId) || $bill->status === 'lunas' || ($bill->amount > 0 && $bill->paid_amount >= $bill->amount)) {
                    continue;
                }

                // Anti-duplicate check (10 sec window)
                $recentDuplicate = Payment::where('bill_id', $bill->id)
                    ->where('created_at', '>=', now()->subSeconds(10))
                    ->exists();

                if ($recentDuplicate) {
                    continue;
                }

                // Calculate amount to pay (remaining amount + late fee)
                $remainingAmount = max(0, $bill->amount - $bill->paid_amount);
                $lateFee = $bill->late_fee;
                $totalAmount = $remainingAmount + $lateFee;

                // Get admin for auto-verification
                $adminId = \App\Models\User::where('school_id', $bill->student->school_id)
                    ->whereIn('role', ['admin', 'admin_sekolah'])
                    ->where('is_active', true)
                    ->first()?->id ?? $userId;

                // Create payment record (auto-verified)
                $payment = Payment::create([
                    'bill_id' => $bill->id,
                    'student_id' => $bill->student_id,
                    'amount_paid' => $totalAmount,
                    'payment_method' => $paymentMethod,
                    'payment_date' => $paymentDate,
                    'notes' => $notes,
                    'receipt_number' => $this->generateReceiptNumber(),
                    'is_verified' => true,
                    'verified_by' => $adminId,
                    'verified_at' => now(),
                    'processed_by' => $userId,
                ]);

                // Update bill status
                $bill->paid_amount += $totalAmount;
                if ($bill->amount <= 0 || $bill->paid_amount >= $bill->amount) {
                    $bill->status = 'lunas';
                } else {
                    $bill->status = 'cicilan';
                }
                $bill->save();

                $successCount++;
            }

            // Log activity
            \App\Models\ActivityLog::create([
                'user_id' => $userId,
                'action' => 'create',
                'description' => "Bendahara memproses {$successCount} pembayaran massal via {$paymentMethod}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'logged_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('treasurer.payments.index')
                ->with('success', "Berhasil memproses {$successCount} pembayaran!");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal memproses pembayaran bulk: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan pada sistem. Silakan coba lagi.')
                ->withInput();
        }
    }

    public function batchStore(BatchPaymentRequest $request)
    {
        $validated = $request->validated();

        $user = auth()->user();
        $schoolId = $user->school_id;

        // Verify student belongs to treasurer's school (unless superadmin/yayasan)
        $student = Student::findOrFail($validated['student_id']);
        if (!$user->isSuperAdmin() && !$user->isYayasan() && $user->school_id && $student->school_id != $user->school_id) {
            abort(403, 'Unauthorized: Anda tidak memiliki akses ke siswa sekolah ini.');
        }

        $billIds = explode(',', $validated['bill_ids']);
        $bills = StudentBill::whereIn('id', $billIds)
            ->when(!$user->isSuperAdmin() && !$user->isYayasan(), function($q) use ($schoolId) {
                $q->whereHas('student', function($sq) use ($schoolId) {
                    $sq->where('school_id', $schoolId);
                });
            })
            ->get();

        if ($bills->pluck('student_id')->unique()->count() > 1) {
            return back()->withErrors(['error' => 'Semua tagihan harus dari siswa yang sama!']);
        }

        try {
            return DB::transaction(function () use ($validated, $bills, $student) {
                $successCount = 0;
                $totalAmountPaid = 0;
                $monthNames = [
                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                ];

                $adminId = \App\Models\User::where('school_id', $student->school_id)
                    ->whereIn('role', ['admin', 'admin_sekolah'])
                    ->where('is_active', true)
                    ->first()?->id ?? auth()->id();

                foreach ($bills as $billItem) {
                    $bill = StudentBill::where('id', $billItem->id)->lockForUpdate()->first();
                    if (!$bill || $bill->status === 'lunas' || ($bill->amount > 0 && $bill->paid_amount >= $bill->amount)) {
                        continue;
                    }

                    // Anti-duplicate check (10 sec window)
                    $recentDuplicate = Payment::where('bill_id', $bill->id)
                        ->where('created_at', '>=', now()->subSeconds(10))
                        ->exists();

                    if ($recentDuplicate) {
                        continue;
                    }

                    // Calculate amount to pay (remaining amount + late fee)
                    $remainingAmount = max(0, $bill->amount - $bill->paid_amount);
                    $lateFee = $bill->late_fee;
                    $totalBillAmount = $remainingAmount + $lateFee;

                    $periodStr = $bill->month ? ($monthNames[$bill->month] . ' ' . $bill->year) : $bill->year;

                    $payment = Payment::create([
                        'bill_id' => $bill->id,
                        'student_id' => $bill->student_id,
                        'amount_paid' => $totalBillAmount,
                        'payment_method' => $validated['payment_method'],
                        'reference_number' => $validated['reference_number'] ?? null,
                        'payment_date' => $validated['payment_date'],
                        'is_verified' => true,
                        'verified_by' => $adminId,
                        'verified_at' => now(),
                        'processed_by' => auth()->id(),
                        'receipt_number' => $this->generateReceiptNumber(),
                        'notes' => 'Pembayaran batch untuk ' . ($bill->paymentType->type_name ?? 'Tagihan') . ' Periode ' . $periodStr,
                    ]);

                    // Update bill status
                    $bill->paid_amount += $totalBillAmount;
                    if ($bill->amount <= 0 || $bill->paid_amount >= $bill->amount) {
                        $bill->status = 'lunas';
                    } else {
                        $bill->status = 'cicilan';
                    }
                    $bill->save();

                    $successCount++;
                    $totalAmountPaid += $totalBillAmount;
                }

                return redirect()->route('treasurer.bills.index')
                    ->with('success', "Pembayaran batch berhasil! {$successCount} tagihan telah dibayar (Total: Rp " . number_format($totalAmountPaid, 0, ',', '.') . ")");
            });
        } catch (\Exception $e) {
            Log::error('Gagal memproses pembayaran batch: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal memproses pembayaran batch. Silakan coba lagi.');
        }
    }

    public function show(Payment $payment)
    {
        $schoolId = auth()->user()->school_id;

        // Verify payment belongs to treasurer's school
        if ($payment->student->school_id != $schoolId) {
            abort(403, 'Unauthorized');
        }

        $payment->load(['student', 'bill.paymentType', 'verifiedBy', 'processedBy']);
        
        return view('treasurer.payments.show', compact('payment'));
    }

    public function downloadReceipt(Payment $payment)
    {
        $schoolId = auth()->user()->school_id;

        // Verify payment belongs to treasurer's school
        if ($payment->student->school_id != $schoolId) {
            abort(403, 'Unauthorized');
        }

        $payment->load(['student.classroom', 'student.school', 'bill.paymentType', 'bill.academicYear', 'processedBy']);
        
        $pdf = \PDF::loadView('treasurer.payments.receipt', compact('payment'));
        
        $fileName = 'Kwitansi-' . ($payment->receipt_number ?? $payment->id) . '.pdf';
        
        return $pdf->download($fileName);
    }

    public function export(Request $request)
    {
        $schoolId = auth()->user()->school_id;
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $paymentMethod = $request->payment_method;
        $isVerified = $request->is_verified;
        $classroomId = $request->classroom_id;
        $paymentTypeId = $request->payment_type_id;

        $fileName = 'Pembayaran_' . now()->format('Y-m-d_His') . '.xlsx';
        
        return Excel::download(
            new PaymentsExport($startDate, $endDate, $paymentMethod, $isVerified, $schoolId, $classroomId, $paymentTypeId),
            $fileName
        );
    }

    private function generateReceiptNumber()
    {
        $date = now()->format('Ymd');
        $prefix = 'KWT-' . $date . '-';
        
        $lastReceipt = Payment::where('receipt_number', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('receipt_number')
            ->value('receipt_number');
        
        $lastNum = $lastReceipt ? intval(substr($lastReceipt, -4)) : 0;
        
        return $prefix . str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Tampilkan halaman diagnostik & pembersihan pembayaran ganda
     */
    public function detectDuplicates(Request $request)
    {
        $schoolId = auth()->user()->school_id;

        // Cari pembayaran ganda berdasarkan bill_id
        $duplicatePayments = Payment::with(['student', 'bill.paymentType', 'processedBy'])
            ->whereHas('student', fn($q) => $q->where('school_id', $schoolId))
            ->whereIn('bill_id', function($sub) use ($schoolId) {
                $sub->select('bill_id')
                    ->from('payments')
                    ->join('students', 'payments.student_id', '=', 'students.id')
                    ->where('students.school_id', $schoolId)
                    ->whereNotNull('bill_id')
                    ->groupBy('bill_id')
                    ->havingRaw('COUNT(*) > 1');
            })
            ->orderBy('bill_id')
            ->orderBy('created_at', 'asc')
            ->get();

        $groupedDuplicates = $duplicatePayments->groupBy('bill_id');

        return view('treasurer.payments.duplicates', compact('groupedDuplicates'));
    }

    /**
     * Eksekusi pembersihan pembayaran ganda (selective or bulk)
     */
    public function fixDuplicates(Request $request)
    {
        $schoolId = auth()->user()->school_id;

        $paymentIds = $request->input('payment_ids', []);
        $billId = $request->input('bill_id');

        $deletedPaymentCount = 0;
        $affectedBillIds = collect();

        DB::transaction(function () use ($paymentIds, $billId, $schoolId, &$deletedPaymentCount, &$affectedBillIds) {
            if (!empty($paymentIds)) {
                // Hapus payment ID yang dipilih secara spesifik oleh Bendahara
                $paymentsToDelete = Payment::whereIn('id', $paymentIds)
                    ->whereHas('student', fn($q) => $q->where('school_id', $schoolId))
                    ->get();

                foreach ($paymentsToDelete as $p) {
                    if ($p->bill_id) {
                        $affectedBillIds->push($p->bill_id);
                    }
                    $p->delete();
                    $deletedPaymentCount++;
                }
            } elseif ($billId) {
                // Hapus duplikat pada 1 tagihan spesifik (sisakan yang paling awal)
                $payments = Payment::where('bill_id', $billId)
                    ->whereHas('student', fn($q) => $q->where('school_id', $schoolId))
                    ->orderBy('created_at', 'asc')
                    ->get();

                if ($payments->count() > 1) {
                    $affectedBillIds->push($billId);
                    foreach ($payments->slice(1) as $dup) {
                        $dup->delete();
                        $deletedPaymentCount++;
                    }
                }
            } else {
                // Hapus seluruh duplikat otomatis (sisakan yang pertama)
                $duplicateBillIds = Payment::whereHas('student', fn($q) => $q->where('school_id', $schoolId))
                    ->whereNotNull('bill_id')
                    ->groupBy('bill_id')
                    ->havingRaw('COUNT(*) > 1')
                    ->pluck('bill_id');

                $affectedBillIds = collect($duplicateBillIds);

                foreach ($duplicateBillIds as $bId) {
                    $payments = Payment::where('bill_id', $bId)->orderBy('created_at', 'asc')->get();
                    if ($payments->count() > 1) {
                        foreach ($payments->slice(1) as $dup) {
                            $dup->delete();
                            $deletedPaymentCount++;
                        }
                    }
                }
            }

            // Hitung ulang total pembayaran & status tagihan untuk seluruh bill yang terpengaruh
            $uniqueBillIds = $affectedBillIds->unique()->filter();
            foreach ($uniqueBillIds as $bId) {
                $bill = StudentBill::find($bId);
                if ($bill) {
                    $totalPaid = (float) Payment::where('bill_id', $bId)->where('is_verified', true)->sum('amount_paid');
                    $bill->paid_amount = $totalPaid;
                    if ($bill->paid_amount >= $bill->amount) {
                        $bill->status = 'lunas';
                    } elseif ($bill->paid_amount > 0) {
                        $bill->status = 'cicilan';
                    } else {
                        $bill->status = 'belum_bayar';
                    }
                    $bill->save();
                }
            }
        });

        if ($deletedPaymentCount === 0) {
            return redirect()->route('treasurer.payments.duplicates')
                ->with('warning', 'Tidak ada transaksi pembayaran ganda yang dipilih untuk dihapus.');
        }

        return redirect()->route('treasurer.payments.duplicates')
            ->with('success', "Berhasil membersihkan {$deletedPaymentCount} record pembayaran ganda!");
    }
}
