<?php

namespace App\Http\Controllers\Treasurer;

use App\Http\Controllers\Controller;
use App\Models\StudentBill;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\PaymentType;
use App\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class StudentBillController extends Controller
{
    public function index(Request $request)
    {
        $schoolId = auth()->user()->school_id;

        // Get filters - default to first academic year that has bills for this school
        $academicYearId = $request->filled('academic_year_id') 
            ? $request->academic_year_id 
            : (AcademicYear::where('is_active', true)->first()?->id 
               ?? AcademicYear::orderBy('year', 'desc')->first()?->id);
        
        $paymentTypeId = $request->payment_type_id;
        $classroomId = $request->classroom_id;
        $search = $request->search;

        // Get all bills for the academic year and this school
        $query = StudentBill::with(['student.classrooms', 'paymentType', 'academicYear'])
            ->where('academic_year_id', $academicYearId)
            ->whereHas('student', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            });

        if ($paymentTypeId) {
            $query->where('payment_type_id', $paymentTypeId);
        }

        if ($classroomId) {
            // Get student IDs that are in this classroom for this academic year
            $studentIdsInClassroom = \DB::table('student_classes')
                ->where('classroom_id', $classroomId)
                ->where('academic_year_id', $academicYearId)
                ->pluck('student_id');
            
            $query->whereIn('student_id', $studentIdsInClassroom);
        }

        if ($search) {
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        // Group bills by student and payment type (each type gets its own row)
        $allBills = $query->orderBy('student_id')->orderBy('payment_type_id')->get();
        
        $groupedBills = collect();
        $studentGroups = $allBills->groupBy('student_id');
        
        foreach ($studentGroups as $studentId => $studentBills) {
            $paymentTypeGroups = $studentBills->groupBy('payment_type_id');
            $rowCount = $paymentTypeGroups->count();
            $isFirstRow = true;
            
            foreach ($paymentTypeGroups as $paymentTypeId => $bills) {
                $firstBill = $bills->first();
                $months = [7, 8, 9, 10, 11, 12, 1, 2, 3, 4, 5, 6];
                
                // Create structure for each month
                $monthlyData = [];
                foreach ($months as $month) {
                    $monthlyData[$month] = $bills->firstWhere('month', $month);
                }
                
                // Calculate totals
                $totalAmount = $bills->sum('amount');
                $totalPaid = $bills->sum('paid_amount');
                $outstanding = $totalAmount - $totalPaid;
                
                $groupedBills->push([
                    'student' => $firstBill->student,
                    'payment_type' => $firstBill->paymentType,
                    'academic_year' => $firstBill->academicYear,
                    'monthly_data' => $monthlyData,
                    'total_amount' => $totalAmount,
                    'total_paid' => $totalPaid,
                    'outstanding' => $outstanding,
                    'bill_count' => $bills->count(),
                    'is_first_row' => $isFirstRow,
                    'rowspan' => $rowCount,
                    'first_bill' => $firstBill,
                ]);
                
                $isFirstRow = false;
            }
        }

        // Get DISTINCT payment types that actually have bills for this school
        $paymentTypeIds = \DB::table('student_bills')
            ->join('students', 'student_bills.student_id', '=', 'students.id')
            ->where('students.school_id', $schoolId)
            ->distinct()
            ->pluck('payment_type_id');
        $paymentTypes = PaymentType::where('is_active', true)
            ->where('school_id', $schoolId)
            ->whereIn('id', $paymentTypeIds)
            ->orderBy('type_name')
            ->get();
        
        // Get DISTINCT academic years that actually have bills for this school
        $academicYearIds = \DB::table('student_bills')
            ->join('students', 'student_bills.student_id', '=', 'students.id')
            ->where('students.school_id', $schoolId)
            ->distinct()
            ->pluck('academic_year_id');
        $academicYears = AcademicYear::whereIn('id', $academicYearIds)
            ->orderBy('year', 'desc')
            ->get();
        
        // Get classrooms directly from classrooms table filtered by academic year and school
        // This ensures all classes appear regardless of whether they have bills yet
        $classrooms = Classroom::where('school_id', $schoolId)
            ->where('academic_year_id', $academicYearId)
            ->where('is_active', true)
            ->orderBy('grade_level')
            ->orderBy('class_name')
            ->get();

        // Calculate summary statistics
        $totalStudents = $studentGroups->count();
        $totalOutstanding = $groupedBills->sum('outstanding');
        $totalPaid = $groupedBills->sum('total_paid');
        $totalBills = $groupedBills->sum('total_amount');

        return view('treasurer.bills.index', compact(
            'groupedBills', 
            'paymentTypes', 
            'academicYears', 
            'classrooms',
            'academicYearId', 
            'paymentTypeId', 
            'classroomId',
            'search',
            'totalStudents',
            'totalOutstanding',
            'totalPaid',
            'totalBills'
        ));
    }

    public function create()
    {
        $schoolId = auth()->user()->school_id;

        $students = Student::where('school_id', $schoolId)
            ->where('status', 'aktif')
            ->orderBy('full_name')
            ->get();
        
        $paymentTypes = PaymentType::where('school_id', $schoolId)
            ->where('is_active', true)
            ->orderBy('type_name')
            ->get();
        
        $academicYears = AcademicYear::orderBy('year', 'desc')->get();
        $activeAcademicYear = AcademicYear::where('is_active', true)->first();
        
        $semesters = Semester::orderBy('semester_name')->get();

        return view('treasurer.bills.create', compact('students', 'paymentTypes', 'academicYears', 'activeAcademicYear', 'semesters'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'payment_type_id' => 'required|exists:payment_types,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester_id' => 'nullable|exists:semesters,id',
            'notes' => 'nullable|string',
            'billing_type' => 'required|in:single,monthly',
            'generate_months' => 'nullable|integer|min:1|max:12',
            'start_month' => 'nullable|integer|min:1|max:12',
            'due_day' => 'nullable|integer|min:1|max:31',
            'monthly_amount' => 'nullable|numeric|min:0',
            'amount' => 'nullable|numeric|min:0',
            'due_date' => 'nullable|date',
            'single_month' => 'nullable|integer|min:1|max:12',
        ]);

        // Verify student belongs to treasurer's school
        $student = Student::findOrFail($validated['student_id']);
        if ($student->school_id != auth()->user()->school_id) {
            abort(403, 'Unauthorized');
        }

        $billService = app(\App\Services\StudentBillService::class);

        try {
            if ($validated['billing_type'] === 'monthly' && $request->filled('generate_months')) {
                $billsCreated = $billService->generateRecurringBills(
                    collect([$student]),
                    $validated,
                    (int) $validated['generate_months'],
                    (int) ($validated['start_month'] ?? 7),
                    (float) $validated['monthly_amount'],
                    (int) ($validated['due_day'] ?? 10),
                );

                return redirect()->route('treasurer.bills.index')
                    ->with('success', "Berhasil membuat {$billsCreated} tagihan bulanan untuk {$student->full_name}.");
            } else {
                $billsCreated = $billService->generateSingleBills(
                    collect([$student]),
                    $validated,
                    (float) $validated['amount'],
                    (int) ($validated['single_month'] ?? 3),
                );

                return redirect()->route('treasurer.bills.index')
                    ->with('success', "Berhasil membuat {$billsCreated} tagihan untuk {$student->full_name}.");
            }
        } catch (\Exception $e) {
            Log::error('Gagal membuat tagihan: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan pada sistem: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function bulkCreate()
    {
        $schoolId = auth()->user()->school_id;

        $academicYears = AcademicYear::orderBy('year', 'desc')->get();
        $activeAcademicYear = AcademicYear::where('is_active', true)->first();
        $semesters = Semester::orderBy('semester_name')->get();
        $classrooms = Classroom::where('school_id', $schoolId)
            ->when($activeAcademicYear, function ($q) use ($activeAcademicYear) {
                $q->where('academic_year_id', $activeAcademicYear->id);
            })
            ->orderBy('class_name')
            ->get();
        $paymentTypes = PaymentType::where('school_id', $schoolId)->orderBy('type_name')->get();

        return view('treasurer.bills.bulk-create', compact('academicYears', 'activeAcademicYear', 'semesters', 'classrooms', 'paymentTypes'));
    }

    public function bulkStore(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'filter_by' => 'required|in:all,classroom,grade',
            'classroom_id' => 'required_if:filter_by,classroom|nullable|exists:classrooms,id',
            'grade_level' => 'required_if:filter_by,grade|nullable|integer',
            'payment_type_id' => 'required|exists:payment_types,id',
            'billing_type' => 'required|in:monthly,single',
            'amount' => 'required_if:billing_type,single|nullable|numeric|min:0',
            'monthly_amount' => 'required_if:billing_type,monthly|nullable|numeric|min:0',
            'start_month' => 'nullable|integer|min:1|max:12',
            'generate_months' => 'nullable|integer|min:1|max:12',
            'single_month' => 'nullable|integer|min:1|max:12',
            'single_day' => 'nullable|integer|min:1|max:31',
        ]);

        try {
            $schoolId = auth()->user()->school_id;

            // Determine amount based on billing type
            $amount = $request->billing_type == 'monthly' ? $request->monthly_amount : $request->amount;

            // Get students based on filter
            $studentsQuery = Student::where('school_id', $schoolId)
                ->where('status', 'aktif');

            if ($request->filter_by == 'classroom') {
                // Verify classroom belongs to treasurer's school
                $classroom = Classroom::findOrFail($request->classroom_id);
                if ($classroom->school_id != $schoolId) {
                    abort(403, 'Unauthorized');
                }

                // Find students enrolled in this classroom (any academic year enrollment)
                $studentIds = DB::table('student_classes')
                    ->where('classroom_id', $request->classroom_id)
                    ->where('status', 'aktif')
                    ->pluck('student_id');

                $studentsQuery->whereIn('id', $studentIds);
            } elseif ($request->filter_by == 'grade') {
                // Get ALL active classrooms with this grade level for this school
                // (regardless of which academic year the classroom was created for)
                $classroomIds = Classroom::where('school_id', $schoolId)
                    ->where('grade_level', $request->grade_level)
                    ->where('is_active', true)
                    ->pluck('id');
                
                // Get students enrolled in those classrooms with active status
                $studentIds = DB::table('student_classes')
                    ->whereIn('classroom_id', $classroomIds)
                    ->where('status', 'aktif')
                    ->distinct()
                    ->pluck('student_id');
                
                $studentsQuery->whereIn('id', $studentIds);
            }

            $students = $studentsQuery->get();

            if ($students->isEmpty()) {
                return back()->with('error', 'Tidak ada siswa yang ditemukan dengan filter tersebut.');
            }

            DB::beginTransaction();

            $createdCount = 0;
            
            // Get base year from academic year
            $academicYear = AcademicYear::find($request->academic_year_id);
            $baseYear = (int) $academicYear->year; // e.g., 2025 from "2025/2026"

            // Determine months to create bills for
            if ($request->billing_type == 'monthly' && $request->start_month && $request->generate_months) {
                $months = [];
                $startMonth = (int)$request->start_month;
                $generateMonths = (int)$request->generate_months;
                
                for ($i = 0; $i < $generateMonths; $i++) {
                    $month = (($startMonth - 1 + $i) % 12) + 1;
                    $months[] = $month;
                }
            } elseif ($request->billing_type == 'single') {
                $months = [$request->single_month ?? null];
            } else {
                $months = [null];
            }

            foreach ($students as $student) {
                foreach ($months as $month) {
                    // Determine year based on month using academic year base
                    if ($month && $month >= 7) {
                        $year = $baseYear;
                    } elseif ($month && $month < 7) {
                        $year = $baseYear + 1;
                    } else {
                        $year = $baseYear;
                    }

                    // Check if bill already exists
                    $exists = StudentBill::where('student_id', $student->id)
                        ->where('payment_type_id', $request->payment_type_id)
                        ->where('academic_year_id', $request->academic_year_id)
                        ->where('year', $year)
                        ->where('month', $month)
                        ->exists();

                    if (!$exists) {
                        StudentBill::create([
                            'student_id' => $student->id,
                            'payment_type_id' => $request->payment_type_id,
                            'academic_year_id' => $request->academic_year_id,
                            'semester_id' => null,
                            'month' => $month,
                            'year' => $year,
                            'amount' => $amount,
                            'paid_amount' => 0,
                            'status' => 'belum_bayar',
                        ]);
                        $createdCount++;
                    }
                }
            }

            // Build description based on filter
            $description = "Bendahara membuat {$createdCount} tagihan massal";
            if ($request->filter_by == 'classroom') {
                $classroom = Classroom::find($request->classroom_id);
                $description .= " untuk kelas " . ($classroom->class_name ?? '-');
            } elseif ($request->filter_by == 'grade') {
                $description .= " untuk tingkat kelas " . $request->grade_level;
            } else {
                $description .= " untuk semua siswa";
            }

            // Log activity
            \App\Models\ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'create',
                'description' => $description,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'logged_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('treasurer.bills.index')
                ->with('success', "Berhasil membuat {$createdCount} tagihan!");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal membuat tagihan: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan pada sistem: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(StudentBill $bill)
    {
        $schoolId = auth()->user()->school_id;

        // Verify bill belongs to treasurer's school
        if ($bill->student->school_id != $schoolId) {
            abort(403, 'Unauthorized');
        }

        $bill->load(['student', 'paymentType', 'academicYear', 'semester', 'payments']);

        return view('treasurer.bills.show', compact('bill'));
    }

    public function edit(StudentBill $bill)
    {
        $schoolId = auth()->user()->school_id;
        if ($bill->student->school_id != $schoolId) {
            abort(403, 'Unauthorized');
        }

        if ($bill->paid_amount > 0 || $bill->payments()->exists()) {
            return redirect()->back()->with('error', 'Tagihan tidak dapat diubah karena sudah memiliki riwayat pembayaran.');
        }

        $bill->load(['student', 'paymentType', 'academicYear']);
        return view('treasurer.bills.edit', compact('bill'));
    }

    public function update(Request $request, StudentBill $bill)
    {
        $schoolId = auth()->user()->school_id;
        if ($bill->student->school_id != $schoolId) {
            abort(403, 'Unauthorized');
        }

        if ($bill->paid_amount > 0 || $bill->payments()->exists()) {
            return redirect()->back()->with('error', 'Tagihan tidak dapat diubah karena sudah memiliki riwayat pembayaran.');
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $bill->update($validated);

        return redirect()->route('treasurer.bills.index')
            ->with('success', 'Tagihan berhasil diperbarui.');
    }

    public function destroy(StudentBill $bill)
    {
        $schoolId = auth()->user()->school_id;
        if ($bill->student->school_id != $schoolId) {
            abort(403, 'Unauthorized');
        }

        if ($bill->paid_amount > 0 || $bill->payments()->exists()) {
            return redirect()->back()->with('error', 'Tagihan tidak dapat dihapus karena sudah memiliki riwayat pembayaran.');
        }

        $bill->delete();

        return redirect()->route('treasurer.bills.index')
            ->with('success', 'Tagihan berhasil dihapus.');
    }

    public function bulkUpdateAmount(Request $request)
    {
        $schoolId = auth()->user()->school_id;

        $validated = $request->validate([
            'bill_ids' => 'nullable|array',
            'bill_ids.*' => 'exists:student_bills,id',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'payment_type_id' => 'nullable|exists:payment_types,id',
            'classroom_id' => 'nullable|exists:classrooms,id',
            'grade_level' => 'nullable|integer',
            'new_amount' => 'required|numeric|min:0',
        ]);

        $billIds = $request->bill_ids ?? [];

        if (empty($billIds)) {
            $query = StudentBill::whereHas('student', fn($q) => $q->where('school_id', $schoolId));
            
            if ($request->filled('academic_year_id')) {
                $query->where('academic_year_id', $request->academic_year_id);
            }
            if ($request->filled('payment_type_id')) {
                $query->where('payment_type_id', $request->payment_type_id);
            }
            if ($request->filled('classroom_id')) {
                $query->whereHas('student.classrooms', fn($q) => $q->where('classrooms.id', $request->classroom_id));
            } elseif ($request->filled('grade_level')) {
                $query->whereHas('student.classrooms', fn($q) => $q->where('grade_level', $request->grade_level));
            }

            $billIds = $query->pluck('id')->toArray();
        }

        if (empty($billIds)) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Tidak ada tagihan yang sesuai dengan kriteria.'], 400);
            }
            return redirect()->back()->with('error', 'Tidak ada tagihan yang sesuai untuk diperbarui.');
        }

        try {
            $billService = app(\App\Services\StudentBillService::class);
            $updated = $billService->bulkUpdateAmount($billIds, (float)$validated['new_amount'], auth()->id());

            $msg = "Berhasil memperbarui nominal menjadi Rp " . number_format($validated['new_amount'], 0, ',', '.') . " untuk {$updated} tagihan.";
            
            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => $msg, 'count' => $updated]);
            }
            return redirect()->back()->with('success', $msg);
        } catch (\Exception $e) {
            Log::error('Gagal update nominal massal (Treasurer): ' . $e->getMessage());
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Terjadi kesalahan sistem.'], 500);
            }
            return redirect()->back()->with('error', 'Terjadi kesalahan pada sistem.');
        }
    }

    public function bulkDelete(Request $request)
    {
        $schoolId = auth()->user()->school_id;

        $validated = $request->validate([
            'bill_ids' => 'nullable|array',
            'bill_ids.*' => 'exists:student_bills,id',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'payment_type_id' => 'nullable|exists:payment_types,id',
            'classroom_id' => 'nullable|exists:classrooms,id',
            'grade_level' => 'nullable|integer',
        ]);

        $billIds = $request->bill_ids ?? [];

        if (empty($billIds)) {
            $query = StudentBill::whereHas('student', fn($q) => $q->where('school_id', $schoolId));
            
            if ($request->filled('academic_year_id')) {
                $query->where('academic_year_id', $request->academic_year_id);
            }
            if ($request->filled('payment_type_id')) {
                $query->where('payment_type_id', $request->payment_type_id);
            }
            if ($request->filled('classroom_id')) {
                $query->whereHas('student.classrooms', fn($q) => $q->where('classrooms.id', $request->classroom_id));
            } elseif ($request->filled('grade_level')) {
                $query->whereHas('student.classrooms', fn($q) => $q->where('grade_level', $request->grade_level));
            }

            $billIds = $query->pluck('id')->toArray();
        }

        if (empty($billIds)) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Tidak ada tagihan yang dipilih / ditemukan.'], 400);
            }
            return redirect()->back()->with('error', 'Tidak ada tagihan yang dipilih atau ditemukan.');
        }

        try {
            $billService = app(\App\Services\StudentBillService::class);
            $result = $billService->bulkDeleteBills($billIds, auth()->id());

            $msg = "Berhasil menghapus {$result['deleted']} tagihan yang belum dibayar.";
            if ($result['skipped'] > 0) {
                $msg .= " ({$result['skipped']} tagihan dilewati karena sudah ada riwayat pembayaran).";
            }

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => $msg, 'deleted' => $result['deleted'], 'skipped' => $result['skipped']]);
            }
            return redirect()->back()->with('success', $msg);
        } catch (\Exception $e) {
            Log::error('Gagal hapus massal tagihan (Treasurer): ' . $e->getMessage());
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Terjadi kesalahan sistem.'], 500);
            }
            return redirect()->back()->with('error', 'Terjadi kesalahan pada sistem.');
        }
    }

    public function export(Request $request)
    {
        $schoolId = auth()->user()->school_id;

        return Excel::download(
            new \App\Exports\BillsExport(
                $request->academic_year_id,
                $request->payment_type_id,
                $request->classroom_id,
                $request->search,
                $schoolId
            ),
            'tagihan_' . date('YmdHis') . '.xlsx'
        );
    }
}
