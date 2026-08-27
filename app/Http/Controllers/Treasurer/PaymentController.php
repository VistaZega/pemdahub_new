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
        
        $bills = StudentBill::where('status', '!=', 'lunas')
            ->whereHas('student', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            })
            ->with('student', 'paymentType')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('treasurer.payments.create', compact('students', 'bills', 'selectedStudentId'));
    }

    public function store(StorePaymentRequest $request)
    {
        $validated = $request->validated();

        // Verify student belongs to treasurer's school
        $student = Student::findOrFail($validated['student_id']);
        if ($student->school_id != auth()->user()->school_id) {
            abort(403, 'Unauthorized');
        }

        try {
            return DB::transaction(function () use ($validated) {
                // Lock bill for update if bill_id exists and verify status
                if (!empty($validated['bill_id'])) {
                    $bill = StudentBill::where('id', $validated['bill_id'])->lockForUpdate()->first();
                    if ($bill) {
                        if ($bill->status === 'lunas' || $bill->paid_amount >= $bill->amount) {
                            return redirect()->route('treasurer.payments.index')
                                ->with('warning', 'Tagihan ini sudah lunas.');
                        }
                    }
                }

                // Anti-Duplicate Window Check (30 seconds)
                $recentDuplicate = Payment::where('student_id', $validated['student_id'])
                    ->when(!empty($validated['bill_id']), fn($q) => $q->where('bill_id', $validated['bill_id']))
                    ->where('amount_paid', $validated['amount_paid'])
                    ->where('created_at', '>=', now()->subSeconds(30))
                    ->first();

                if ($recentDuplicate) {
                    return redirect()->route('treasurer.payments.index')
                        ->with('warning', 'Pembayaran yang sama baru saja dicatat beberapa detik lalu (mencegah pembayaran ganda).');
                }

                $adminId = \App\Models\User::where('school_id', auth()->user()->school_id)
                    ->where('role', 'admin')
                    ->where('is_active', true)
                    ->first()?->id;

                $validated['is_verified'] = true;
                $validated['verified_by'] = $adminId;
                $validated['verified_at'] = now();
                $validated['processed_by'] = auth()->id();
                $validated['receipt_number'] = $this->generateReceiptNumber();

                $payment = Payment::create($validated);

                if ($payment->bill_id) {
                    $bill = StudentBill::find($payment->bill_id);
                    $totalPaid = Payment::where('bill_id', $bill->id)
                        ->where('is_verified', true)
                        ->sum('amount_paid');

                    $bill->paid_amount = $totalPaid;

                    if ($bill->paid_amount >= $bill->amount) {
                        $bill->status = 'lunas';
                    } elseif ($bill->paid_amount > 0) {
                        $bill->status = 'cicilan';
                    }

                    $bill->save();
                }

                return redirect()->route('treasurer.payments.index')
                    ->with('success', 'Pembayaran berhasil dicatat.')
                    ->with('payment_id', $payment->id);
            });
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan pembayaran: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal menyimpan pembayaran. Silakan coba lagi.');
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
            $schoolId = auth()->user()->school_id;
            $academicYearId = $request->academic_year_id;
            $classroomId = $request->classroom_id;
            $paymentTypeId = $request->payment_type_id;
            $month = $request->month;

            // Verify classroom belongs to treasurer's school
            $classroom = Classroom::findOrFail($classroomId);
            if ($classroom->school_id != $schoolId) {
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
                ->whereHas('student', function ($q) use ($schoolId) {
                    $q->where('school_id', $schoolId);
                })
                ->where('academic_year_id', $academicYearId)
                ->where('payment_type_id', $paymentTypeId)
                ->whereIn('status', ['belum_bayar', 'cicilan']);

            // Filter by month if provided (for SPP)
            if ($month) {
                $query->where('month', $month);
            }

            $bills = $query->orderBy('student_id')->get();

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
            $schoolId = auth()->user()->school_id;
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
                if (!$bill || $bill->student->school_id != $schoolId || $bill->status === 'lunas' || $bill->paid_amount >= $bill->amount) {
                    continue;
                }

                // Anti-duplicate check (30 sec window)
                $recentDuplicate = Payment::where('bill_id', $bill->id)
                    ->where('created_at', '>=', now()->subSeconds(30))
                    ->exists();

                if ($recentDuplicate) {
                    continue;
                }

                // Calculate amount to pay (remaining amount + late fee)
                $remainingAmount = $bill->amount - $bill->paid_amount;
                $lateFee = $bill->late_fee;
                $totalAmount = $remainingAmount + $lateFee;

                // Get admin for auto-verification
                $adminId = \App\Models\User::where('school_id', $schoolId)
                    ->where('role', 'admin')
                    ->where('is_active', true)
                    ->first()?->id;

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
                if ($bill->paid_amount >= $bill->amount) {
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

        $schoolId = auth()->user()->school_id;

        // Verify student belongs to treasurer's school
        $student = Student::findOrFail($validated['student_id']);
        if ($student->school_id != $schoolId) {
            abort(403, 'Unauthorized');
        }

        $billIds = explode(',', $validated['bill_ids']);
        $bills = StudentBill::whereIn('id', $billIds)
            ->whereHas('student', function($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            })
            ->get();

        if ($bills->pluck('student_id')->unique()->count() > 1) {
            return back()->withErrors(['error' => 'Semua tagihan harus dari siswa yang sama!']);
        }

        try {
            return DB::transaction(function () use ($validated, $bills, $schoolId) {
                $successCount = 0;
                $totalAmountPaid = 0;
                $monthNames = [
                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                ];

                $adminId = \App\Models\User::where('school_id', $schoolId)
                    ->where('role', 'admin')
                    ->where('is_active', true)
                    ->first()?->id;

                foreach ($bills as $billItem) {
                    $bill = StudentBill::where('id', $billItem->id)->lockForUpdate()->first();
                    if (!$bill || $bill->status === 'lunas' || $bill->paid_amount >= $bill->amount) {
                        continue;
                    }

                    // Anti-duplicate check (30 sec window)
                    $recentDuplicate = Payment::where('bill_id', $bill->id)
                        ->where('created_at', '>=', now()->subSeconds(30))
                        ->exists();

                    if ($recentDuplicate) {
                        continue;
                    }

                    // Calculate amount to pay (remaining amount + late fee)
                    $remainingAmount = $bill->amount - $bill->paid_amount;
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
                    if ($bill->paid_amount >= $bill->amount) {
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
