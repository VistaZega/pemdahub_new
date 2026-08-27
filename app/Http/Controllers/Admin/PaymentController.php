<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePaymentRequest;
use App\Http\Requests\Admin\BatchPaymentRequest;
use App\Http\Requests\Admin\BulkPaymentRequest;
use App\Models\Payment;
use App\Models\StudentBill;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\PaymentType;
use App\Exports\PaymentsExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        // Handle filter reset
        if ($request->has('reset')) {
            session()->forget([
                'payments_filter_is_verified',
                'payments_filter_payment_method',
                'payments_filter_search',
                'payments_filter_start_date',
                'payments_filter_end_date',
                'payments_filter_school_id',
                'payments_filter_classroom_id',
                'payments_filter_payment_type_id',
                'payments_filter_academic_year_id'
            ]);
            return redirect()->route('admin.payments.index');
        }

        $user = auth()->user();

        // Sticky Filters Logic
        $isVerified = $request->has('is_verified') 
            ? $request->is_verified 
            : session('payments_filter_is_verified');
            
        $paymentMethod = $request->has('payment_method') 
            ? $request->payment_method 
            : session('payments_filter_payment_method');
            
        $search = $request->has('search') 
            ? $request->search 
            : session('payments_filter_search');
            
        $startDate = $request->has('start_date') 
            ? $request->start_date 
            : session('payments_filter_start_date');
            
        $endDate = $request->has('end_date') 
            ? $request->end_date 
            : session('payments_filter_end_date');

        $schoolId = $request->has('school_id') 
            ? $request->school_id 
            : session('payments_filter_school_id');

        $classroomId = $request->has('classroom_id') 
            ? $request->classroom_id 
            : session('payments_filter_classroom_id');

        $paymentTypeId = $request->has('payment_type_id') 
            ? $request->payment_type_id 
            : session('payments_filter_payment_type_id');

        $academicYearId = $request->has('academic_year_id') 
            ? $request->academic_year_id 
            : session('payments_filter_academic_year_id');

        if (!$academicYearId) {
            $activeYear = \App\Models\AcademicYear::where('is_active', true)->first();
            $academicYearId = $activeYear ? $activeYear->id : null;
        }

        // Store to session
        session([
            'payments_filter_is_verified' => $isVerified,
            'payments_filter_payment_method' => $paymentMethod,
            'payments_filter_search' => $search,
            'payments_filter_start_date' => $startDate,
            'payments_filter_end_date' => $endDate,
            'payments_filter_school_id' => $schoolId,
            'payments_filter_classroom_id' => $classroomId,
            'payments_filter_payment_type_id' => $paymentTypeId,
            'payments_filter_academic_year_id' => $academicYearId,
        ]);

        $query = Payment::with(['student', 'bill.paymentType']);

        // Auto-filter by school_id for admin_sekolah
        if ($user && !$user->isSuperAdmin()) {
            $query->whereHas('student', function($q) use ($user) {
                $q->where('school_id', $user->school_id);
            });
        } elseif ($schoolId) {
            $query->whereHas('student', function($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            });
        }

        // Apply classroom_id filter
        if ($classroomId) {
            $query->whereHas('student.studentClasses', function($q) use ($classroomId) {
                $q->where('classroom_id', $classroomId);
            });
        }

        // Apply payment_type_id filter
        if ($paymentTypeId) {
            $query->whereHas('bill', function($q) use ($paymentTypeId) {
                $q->where('payment_type_id', $paymentTypeId);
            });
        }

        // Apply academic_year_id filter
        if ($academicYearId) {
            $query->whereHas('bill', function($q) use ($academicYearId) {
                $q->where('academic_year_id', $academicYearId);
            });
        }

        // Apply Filters
        if ($isVerified !== null && $isVerified !== '') {
            $query->where('is_verified', $isVerified);
        }

        if ($paymentMethod) {
            $query->where('payment_method', $paymentMethod);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('receipt_number', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%")
                  ->orWhereHas('student', function ($sq) use ($search) {
                      $sq->where('full_name', 'like', "%{$search}%")
                         ->orWhere('nisn', 'like', "%{$search}%");
                  });
            });
        }

        if ($startDate) {
            $query->whereDate('payment_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('payment_date', '<=', $endDate);
        }

        $payments = $query->orderBy('payment_date', 'desc')->paginate(20)->withQueryString();

        // Load options for the dropdowns
        $schools = [];
        if ($user->isSuperAdmin()) {
            $schools = \App\Models\School::where('is_active', true)->schoolsOnly()->orderBy('name')->get();
        }
        $academicYears = \App\Models\AcademicYear::orderBy('year', 'desc')->get();

        $classroomQuery = \App\Models\Classroom::where('is_active', true);
        if ($academicYearId) {
            $classroomQuery->where('academic_year_id', $academicYearId);
        }
        if (!$user->isSuperAdmin()) {
            $classroomQuery->where('school_id', $user->school_id);
        } elseif ($schoolId) {
            $classroomQuery->where('school_id', $schoolId);
        }
        $classrooms = $classroomQuery->orderBy('class_name')->get();

        $paymentTypeQuery = \App\Models\PaymentType::where('is_active', true);
        if (!$user->isSuperAdmin()) {
            $paymentTypeQuery->where('school_id', $user->school_id);
        } elseif ($schoolId) {
            $paymentTypeQuery->where('school_id', $schoolId);
        }
        $paymentTypes = $paymentTypeQuery->orderBy('type_name')->get();

        return view('admin.payments.index', compact(
            'payments', 'isVerified', 'paymentMethod', 'search', 'startDate', 'endDate',
            'schoolId', 'classroomId', 'paymentTypeId', 'schools', 'classrooms', 'paymentTypes',
            'academicYears', 'academicYearId'
        ));
    }

    public function create(Request $request)
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403, 'Hanya Super Admin yang dapat mengubah atau menghapus data keuangan.');
        
        $user = auth()->user();
        $studentsQuery = Student::orderBy('full_name');
        
        // Scope by school for non-superadmin
        if ($user && !$user->isSuperAdmin()) {
            $studentsQuery->where('school_id', $user->school_id);
        }
        
        $students = $studentsQuery->select('id', 'full_name', 'nisn', 'school_id')->get();
        $selectedStudentId = $request->get('student_id');
        $selectedBillId = $request->get('bill_id');
        
        $billsQuery = StudentBill::whereHas('student')
            ->where('status', '!=', 'lunas')
            ->with('student:id,full_name,nisn', 'paymentType:id,type_name');
        
        // Scope bills by school too
        if ($user && !$user->isSuperAdmin()) {
            $billsQuery->whereHas('student', function($q) use ($user) {
                $q->where('school_id', $user->school_id);
            });
        }
        
        $bills = $billsQuery->orderBy('created_at', 'desc')->get();

        return view('admin.payments.create', compact('students', 'bills', 'selectedStudentId', 'selectedBillId'));
    }

    public function store(StorePaymentRequest $request)
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403, 'Hanya Super Admin yang dapat mengubah atau menghapus data keuangan.');
        
        $validated = $request->validated();

        $validated['is_verified'] = true;
        $validated['verified_by'] = auth()->id();
        $validated['verified_at'] = now();
        $validated['processed_by'] = auth()->id();

        try {
            return DB::transaction(function () use ($validated) {
                // Lock bill for update if bill_id exists and verify status
                if (!empty($validated['bill_id'])) {
                    $bill = StudentBill::where('id', $validated['bill_id'])->lockForUpdate()->first();
                    if ($bill) {
                        if ($bill->status === 'lunas' || $bill->paid_amount >= $bill->amount) {
                            return redirect()->route('admin.payments.index')
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
                    return redirect()->route('admin.payments.index')
                        ->with('warning', 'Pembayaran yang sama baru saja dicatat beberapa detik lalu (mencegah pembayaran ganda).');
                }

                // Generate inside transaction so lockForUpdate() is effective
                $validated['receipt_number'] = $this->generateReceiptNumber();
                $payment = Payment::create($validated);

                if ($payment->bill_id) {
                    $this->updateBillStatus($payment->bill_id);
                    
                    // Reputation Hook: Bonus for on-time payment
                    $bill = StudentBill::find($payment->bill_id);
                    if ($bill && $bill->status === 'lunas' && $payment->payment_date->day <= 10) {
                        $student = $payment->student;
                        if ($student && $student->user_id) {
                            \App\Models\ReputationLog::log(
                                $student->user_id, 
                                40, 
                                'finance', 
                                "Pembayaran tepat waktu: " . ($bill->paymentType->type_name ?? 'Tagihan'),
                                $payment
                            );
                        }
                    }
                }

                return redirect()->route('admin.payments.index')
                    ->with('success', 'Pembayaran berhasil dicatat.')
                    ->with('payment_id', $payment->id);
            });
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan pembayaran: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal menyimpan pembayaran. Silakan coba lagi.');
        }
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

    public function batchStore(BatchPaymentRequest $request)
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403, 'Hanya Super Admin yang dapat mengubah atau menghapus data keuangan.');
        
        $validated = $request->validated();

        $billIds = explode(',', $validated['bill_ids']);
        $bills = StudentBill::whereIn('id', $billIds)->get();

        // Verify all bills belong to same student
        if ($bills->pluck('student_id')->unique()->count() > 1) {
            return back()->withErrors(['error' => 'Semua tagihan harus dari siswa yang sama!']);
        }

        try {
            return DB::transaction(function () use ($validated, $bills) {
                $successCount = 0;
                $totalAmountPaid = 0;
                $monthNames = [
                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                ];

                foreach ($bills as $bill) {
                    if ($bill->status === 'lunas') {
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
                        'verified_by' => auth()->id(),
                        'verified_at' => now(),
                        'processed_by' => auth()->id(),
                        'receipt_number' => $this->generateReceiptNumber(),
                        'notes' => 'Pembayaran batch untuk ' . ($bill->paymentType->type_name ?? 'Tagihan') . ' Periode ' . $periodStr,
                    ]);

                    // Update bill status
                    $bill->paid_amount += $totalBillAmount;
                    if ($bill->paid_amount >= $bill->amount) {
                        $bill->status = 'lunas';

                        // Reputation Hook: Bonus for on-time payment
                        if (\Carbon\Carbon::parse($validated['payment_date'])->day <= 10) {
                            $student = $bill->student;
                            if ($student && $student->user_id) {
                                \App\Models\ReputationLog::log(
                                    $student->user_id,
                                    40,
                                    'finance',
                                    "Pembayaran tepat waktu: " . ($bill->paymentType->type_name ?? 'Tagihan'),
                                    $payment
                                );
                            }
                        }
                    } else {
                        $bill->status = 'cicilan';
                    }
                    $bill->save();

                    $successCount++;
                    $totalAmountPaid += $totalBillAmount;
                }

                return redirect()->route('admin.bills.index')
                    ->with('success', "Pembayaran batch berhasil! {$successCount} tagihan telah dibayar (Total: Rp " . number_format($totalAmountPaid, 0, ',', '.') . ")");
            });
        } catch (\Exception $e) {
            Log::error('Gagal memproses pembayaran batch: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal memproses pembayaran batch. Silakan coba lagi.');
        }
    }

    public function show(Payment $payment)
    {
        $payment->load(['student', 'bill.paymentType', 'verifiedBy', 'processedBy']);
        
        return view('admin.payments.show', compact('payment'));
    }

    public function edit(Payment $payment)
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403, 'Hanya Super Admin yang dapat mengubah atau menghapus data keuangan.');
        
        $user = auth()->user();
        $studentsQuery = Student::orderBy('full_name');
        
        if ($user && !$user->isSuperAdmin()) {
            $studentsQuery->where('school_id', $user->school_id);
        }
        
        $students = $studentsQuery->select('id', 'full_name', 'nisn', 'school_id')->get();
        
        $billsQuery = StudentBill::whereHas('student')
            ->with('student:id,full_name,nisn', 'paymentType:id,type_name');
        
        if ($user && !$user->isSuperAdmin()) {
            $billsQuery->whereHas('student', function($q) use ($user) {
                $q->where('school_id', $user->school_id);
            });
        }
        
        $bills = $billsQuery->orderBy('created_at', 'desc')->get();

        return view('admin.payments.edit', compact('payment', 'students', 'bills'));
    }

    public function update(StorePaymentRequest $request, Payment $payment)
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403, 'Hanya Super Admin yang dapat mengubah atau menghapus data keuangan.');
        
        $validated = $request->validated();

        try {
            return DB::transaction(function () use ($validated, $payment) {
                $oldBillId = $payment->bill_id;
                $payment->update($validated);

                if ($oldBillId) {
                    $this->updateBillStatus($oldBillId);
                }
                if ($payment->bill_id && $payment->bill_id != $oldBillId) {
                    $this->updateBillStatus($payment->bill_id);
                }

                return redirect()->route('admin.payments.index')
                    ->with('success', 'Data pembayaran berhasil diperbarui.');
            });
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui pembayaran: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal memperbarui pembayaran. Silakan coba lagi.');
        }
    }

    public function destroy(Payment $payment)
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403, 'Hanya Super Admin yang dapat mengubah atau menghapus data keuangan.');
        
        try {
            return DB::transaction(function () use ($payment) {
                $billId = $payment->bill_id;
                $paymentId = $payment->id;
                $payment->delete();

                if ($billId) {
                    $this->updateBillStatus($billId);
                }

                $referer = request()->headers->get('referer');
                if ($referer && !str_contains($referer, "/admin/payments/{$paymentId}")) {
                    return redirect()->back()
                        ->with('success', 'Data pembayaran berhasil dihapus.');
                }

                return redirect()->route('admin.payments.index')
                    ->with('success', 'Data pembayaran berhasil dihapus.');
            });
        } catch (\Exception $e) {
            Log::error('Gagal menghapus pembayaran: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghapus pembayaran. Silakan coba lagi.');
        }
    }

    private function updateBillStatus($billId)
    {
        $bill = StudentBill::find($billId);
        if (!$bill) return;

        $totalPaid = Payment::where('bill_id', $billId)
            ->where('is_verified', true)
            ->sum('amount_paid');

        // Update paid_amount
        $bill->paid_amount = $totalPaid;

        // Update status
        if ($totalPaid >= $bill->amount) {
            $bill->status = 'lunas';
        } elseif ($totalPaid > 0) {
            $bill->status = 'cicilan';
        } else {
            $bill->status = 'belum_bayar';
        }

        $bill->save();
    }

    public function downloadReceipt(Payment $payment)
    {
        $payment->load(['student.studentClass.classroom', 'bill.paymentType', 'processedBy']);
        
        $pdf = \PDF::loadView('admin.payments.receipt', compact('payment'));
        
        $fileName = 'Kwitansi-' . ($payment->receipt_number ?? $payment->id) . '.pdf';
        
        return $pdf->download($fileName);
    }

    public function export(Request $request)
    {
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $paymentMethod = $request->payment_method;
        $isVerified = $request->is_verified;
        $classroomId = $request->classroom_id;
        $paymentTypeId = $request->payment_type_id;
        $academicYearId = $request->academic_year_id;

        $user = auth()->user();
        $schoolId = ($user && !$user->isSuperAdmin()) ? $user->school_id : $request->school_id;

        $fileName = 'Pembayaran_' . now()->format('Y-m-d_His') . '.xlsx';
        
        return Excel::download(
            new PaymentsExport($startDate, $endDate, $paymentMethod, $isVerified, $schoolId, $classroomId, $paymentTypeId, $academicYearId),
            $fileName
        );
    }

    // BULK PAYMENT METHODS
    public function bulkCreate()
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403, 'Hanya Super Admin yang dapat mengubah atau menghapus data keuangan.');
        
        // Get academic years (now global, no duplicates)
        $academicYears = AcademicYear::orderBy('year', 'desc')->get();

        $classrooms = Classroom::with('school')->orderBy('class_name')->get();

        // Get payment types - group by type_code to avoid school duplicates
        $paymentTypes = PaymentType::where('is_active', true)
            ->orderBy('type_name')
            ->get()
            ->groupBy('type_code')
            ->map(function($group) {
                return $group->first();
            })
            ->values();

        return view('admin.payments.bulk-create', compact('academicYears', 'classrooms', 'paymentTypes'));
    }

    public function fetchBills(Request $request)
    {
        try {
            $academicYearId = $request->academic_year_id;
            $classroomId = $request->classroom_id;
            $paymentTypeId = $request->payment_type_id;
            $month = $request->month;

            // Get students in the classroom
            $studentIds = DB::table('student_classes')
                ->where('classroom_id', $classroomId)
                ->pluck('student_id');

            // Build query for unpaid bills
            $query = StudentBill::with(['student', 'paymentType'])
                ->whereIn('student_id', $studentIds)
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
        abort_unless(auth()->user()->isSuperAdmin(), 403, 'Hanya Super Admin yang dapat mengubah atau menghapus data keuangan.');
        
        $request->validated();

        try {
            DB::beginTransaction();

            $billIds = $request->bill_ids;
            $paymentMethod = $request->payment_method;
            $paymentDate = $request->payment_date;
            $notes = $request->notes;
            $userId = auth()->id();

            $successCount = 0;

            foreach ($billIds as $billId) {
                $bill = StudentBill::find($billId);
                if (!$bill || $bill->status === 'lunas') {
                    continue;
                }

                // Calculate amount to pay (remaining amount + late fee)
                $remainingAmount = $bill->amount - $bill->paid_amount;
                $lateFee = $bill->late_fee;
                $totalAmount = $remainingAmount + $lateFee;

                // Create payment record
                $payment = Payment::create([
                    'bill_id' => $bill->id,
                    'student_id' => $bill->student_id,
                    'amount_paid' => $totalAmount,
                    'payment_method' => $paymentMethod,
                    'payment_date' => $paymentDate,
                    'notes' => $notes,
                    'receipt_number' => $this->generateReceiptNumber(),
                    'is_verified' => true,
                    'verified_by' => $userId,
                    'verified_at' => now(),
                    'processed_by' => $userId,
                ]);

                // Update bill status
                $bill->paid_amount += $totalAmount;
                if ($bill->paid_amount >= $bill->amount) {
                    $bill->status = 'lunas';
                    
                    // Reputation Hook: Bonus for on-time payment
                    if (\Carbon\Carbon::parse($paymentDate)->day <= 10) {
                        $student = $bill->student;
                        if ($student && $student->user_id) {
                            \App\Models\ReputationLog::log(
                                $student->user_id, 
                                40, 
                                'finance', 
                                "Pembayaran tepat waktu: " . ($bill->paymentType->type_name ?? 'Tagihan'),
                                $payment
                            );
                        }
                    }
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
                'description' => "Memproses {$successCount} pembayaran massal via {$paymentMethod}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'logged_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('admin.payments.index')
                ->with('success', "Berhasil memproses {$successCount} pembayaran!");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal memproses pembayaran bulk: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan pada sistem. Silakan coba lagi.')
                ->withInput();
        }
    }
}
