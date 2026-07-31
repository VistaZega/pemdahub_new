<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentBill;
use App\Models\Student;
use App\Models\School;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\PaymentType;
use App\Models\Semester;
use App\Models\Payment;
use App\Exports\BillsExport;
use App\Services\StudentBillService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class StudentBillController extends Controller
{
    public function __construct(
        protected StudentBillService $billService,
    ) {}

    public function index(Request $request)
    {
        // Handle filter reset
        if ($request->has('reset')) {
            session()->forget([
                'bills_filter_academic_year_id',
                'bills_filter_school_id',
                'bills_filter_payment_type_id',
                'bills_filter_classroom_id',
                'bills_filter_search'
            ]);
            return redirect()->route('admin.bills.index');
        }

        $userSchoolId = auth()->user()->school_id;

        // Sticky Filters Logic
        $academicYearId = $request->filled('academic_year_id') 
            ? $request->academic_year_id 
            : session('bills_filter_academic_year_id');
        
        if (!$academicYearId) {
            $academicYearId = AcademicYear::where('is_active', true)->first()?->id
                ?? AcademicYear::orderBy('year', 'desc')->first()?->id;
        }
        
        $user = auth()->user();
        $isSA = $user->isSuperAdmin();

        $schoolId = $isSA 
            ? ($request->filled('school_id') ? $request->school_id : ($request->has('school_id') ? null : session('bills_filter_school_id')))
            : $user->school_id;
        
        $paymentTypeId = $request->has('payment_type_id') 
            ? $request->payment_type_id 
            : session('bills_filter_payment_type_id');
            
        $classroomId = $request->has('classroom_id') 
            ? $request->classroom_id 
            : session('bills_filter_classroom_id');
            
        $search = $request->has('search') 
            ? $request->search 
            : session('bills_filter_search');

        // Store to session
        session([
            'bills_filter_academic_year_id' => $academicYearId,
            'bills_filter_school_id' => $schoolId,
            'bills_filter_payment_type_id' => $paymentTypeId,
            'bills_filter_classroom_id' => $classroomId,
            'bills_filter_search' => $search,
        ]);

        $query = StudentBill::with(['student.classrooms', 'student' => function($q) {
            $q->select('id', 'full_name', 'nisn', 'school_id', 'photo');
        }, 'paymentType', 'academicYear'])
            ->whereHas('student')
            ->where('academic_year_id', $academicYearId);

        if ($schoolId) {
            $query->whereHas('student', fn($q) => $q->where('school_id', $schoolId));
        }
        if ($paymentTypeId) {
            $query->where('payment_type_id', $paymentTypeId);
        }
        if ($classroomId) {
            $query->whereHas('student.classrooms', fn($q) => $q->where('classrooms.id', $classroomId));
        }
        if ($search) {
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        // Get everything for grouping and summary calculations
        $allBills = $query->orderBy('student_id')->orderBy('payment_type_id')->get();

        $groupedBills = collect();
        $studentGroups = $allBills->groupBy('student_id');

        foreach ($studentGroups as $studentId => $studentBills) {
            $paymentTypeGroups = $studentBills->groupBy('payment_type_id');
            $rowCount = $paymentTypeGroups->count();
            $isFirstRowForStudent = true;

            foreach ($paymentTypeGroups as $ptId => $bills) {
                $firstBill = $bills->first();
                $months = [7, 8, 9, 10, 11, 12, 1, 2, 3, 4, 5, 6];

                $monthlyData = [];
                foreach ($months as $month) {
                    $monthlyData[$month] = $bills->firstWhere('month', $month);
                }

                $totalAmount = $bills->sum('amount');
                $totalPaid = $bills->sum('paid_amount');

                $groupedBills->push([
                    'student' => $firstBill->student,
                    'payment_type' => $firstBill->paymentType,
                    'academic_year' => $firstBill->academicYear,
                    'monthly_data' => $monthlyData,
                    'total_amount' => $totalAmount,
                    'total_paid' => $totalPaid,
                    'outstanding' => $totalAmount - $totalPaid,
                    'bill_count' => $bills->count(),
                    'is_first_row' => $isFirstRowForStudent,
                    'rowspan' => $rowCount,
                    'first_bill' => $firstBill,
                ]);

                $isFirstRowForStudent = false;
            }
        }

        // Summary calculations
        $totalStudents = $studentGroups->count();
        $totalOutstanding = $allBills->sum(fn($b) => $b->amount - $b->paid_amount);
        $totalPaid = $allBills->sum('paid_amount');
        $totalBillsCount = $allBills->sum('amount');

        // Dropdown data — base on actual bills, not school filter
        $paymentTypeIds = DB::table('student_bills')
            ->where('academic_year_id', $academicYearId)
            ->distinct()->pluck('payment_type_id');
        $paymentTypes = PaymentType::whereIn('id', $paymentTypeIds)
            ->orderBy('type_name')
            ->get();

        $academicYears = AcademicYear::whereHas('studentBills')->orderBy('year', 'desc')->get();

        // Schools that actually have billed students in the selected AY
        $schoolIds = DB::table('student_bills')
            ->join('students', 'student_bills.student_id', '=', 'students.id')
            ->where('student_bills.academic_year_id', $academicYearId)
            ->distinct()->pluck('students.school_id');
        $schools = School::whereIn('id', $schoolIds)->orderBy('name')->get();

        // Get classrooms directly from classrooms table filtered by academic year and school
        $classrooms = Classroom::where('academic_year_id', $academicYearId)
            ->where('is_active', true)
            ->when($schoolId, function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            })
            ->orderBy('class_name')
            ->get();

        // Created bill groups (Jenis Tagihan + Nominal + Kelas/Tingkat + TP) for bulk update & bulk delete modals
        $createdBillGroups = StudentBill::join('students', 'student_bills.student_id', '=', 'students.id')
            ->leftJoin('student_classes', function($join) {
                $join->on('students.id', '=', 'student_classes.student_id')
                     ->on('student_bills.academic_year_id', '=', 'student_classes.academic_year_id');
            })
            ->leftJoin('classrooms', 'student_classes.classroom_id', '=', 'classrooms.id')
            ->join('payment_types', 'student_bills.payment_type_id', '=', 'payment_types.id')
            ->leftJoin('academic_years', 'student_bills.academic_year_id', '=', 'academic_years.id')
            ->where('student_bills.paid_amount', 0)
            ->when($schoolId, fn($q) => $q->where('students.school_id', $schoolId))
            ->selectRaw('
                student_bills.payment_type_id,
                payment_types.type_name,
                student_bills.amount,
                classrooms.grade_level,
                GROUP_CONCAT(DISTINCT classrooms.class_name ORDER BY classrooms.class_name SEPARATOR ", ") as class_names,
                academic_years.year as academic_year_name,
                student_bills.academic_year_id,
                count(DISTINCT student_bills.id) as total_bills
            ')
            ->groupBy('student_bills.payment_type_id', 'payment_types.type_name', 'student_bills.amount', 'classrooms.grade_level', 'academic_years.year', 'student_bills.academic_year_id')
            ->orderBy('payment_types.type_name')
            ->orderBy('classrooms.grade_level')
            ->get();

        // Unpaid bills list
        $unpaidBillsList = StudentBill::with(['student.classroom', 'paymentType', 'academicYear'])
            ->where('paid_amount', 0)
            ->when($academicYearId, fn($q) => $q->where('academic_year_id', $academicYearId))
            ->when($schoolId, fn($q) => $q->whereHas('student', fn($sq) => $sq->where('school_id', $schoolId)))
            ->when($paymentTypeId, fn($q) => $q->where('payment_type_id', $paymentTypeId))
            ->when($classroomId, fn($q) => $q->whereHas('student', fn($sq) => $sq->where('classroom_id', $classroomId)))
            ->orderBy('id', 'desc')
            ->take(200)
            ->get();

        // All master data for modals
        $allSchools = School::orderBy('name')->get();
        $allAcademicYears = AcademicYear::orderBy('year', 'desc')->get();
        $allPaymentTypes = PaymentType::orderBy('type_name')->get();
        $allClassrooms = Classroom::orderBy('class_name')->get();

        // Pagination
        $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
        $perPage = 15;
        $paginatedBills = new \Illuminate\Pagination\LengthAwarePaginator(
            $groupedBills->slice(($currentPage - 1) * $perPage, $perPage)->values(),
            $groupedBills->count(),
            $perPage,
            $currentPage,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
        );
        $paginatedBills->withQueryString();

        return view('admin.bills.index', compact(
            'paginatedBills', 'paymentTypes', 'academicYears', 'schools', 'classrooms',
            'allSchools', 'allAcademicYears', 'allPaymentTypes', 'allClassrooms',
            'unpaidBillsList', 'createdBillGroups',
            'academicYearId', 'schoolId', 'paymentTypeId', 'classroomId', 'search',
            'totalStudents', 'totalOutstanding', 'totalPaid', 'totalBillsCount'
        ));
    }

    public function create()
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403, 'Hanya Super Admin yang dapat mengubah atau menghapus data keuangan.');
        
        $schools = School::where('is_active', true)->orderBy('name')->get();
        $students = Student::orderBy('full_name')->get();
        $paymentTypes = PaymentType::where('is_active', true)->orderBy('type_name')->get()->unique('type_code')->values();
        $academicYears = AcademicYear::orderBy('year', 'desc')->get()->unique('year')->values();
        $semesters = Semester::orderBy('semester_number')->get();

        return view('admin.bills.create', compact('schools', 'students', 'paymentTypes', 'academicYears', 'semesters'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403, 'Hanya Super Admin yang dapat mengubah atau menghapus data keuangan.');
        
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

        $student = Student::findOrFail($validated['student_id']);

        try {
            if ($validated['billing_type'] === 'monthly' && $request->filled('generate_months')) {
                $billsCreated = $this->billService->generateRecurringBills(
                    collect([$student]),
                    $validated,
                    (int) $validated['generate_months'],
                    (int) ($validated['start_month'] ?? 7),
                    (float) $validated['monthly_amount'],
                    (int) ($validated['due_day'] ?? 10),
                );

                return redirect()->route('admin.bills.index')
                    ->with('success', "Berhasil membuat {$billsCreated} tagihan bulanan untuk {$student->full_name}.");
            } else {
                $billsCreated = $this->billService->generateSingleBills(
                    collect([$student]),
                    $validated,
                    (float) $validated['amount'],
                    (int) ($validated['single_month'] ?? 3),
                );

                return redirect()->route('admin.bills.index')
                    ->with('success', "Berhasil membuat {$billsCreated} tagihan untuk {$student->full_name}.");
            }
        } catch (\Exception $e) {
            Log::error('Gagal membuat tagihan: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan pada sistem: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(StudentBill $bill)
    {
        $bill->load(['student.school', 'paymentType', 'academicYear', 'semester', 'payments.processedBy']);
        
        // Fetch all bills for the same student, academic year, and payment type to show full schedule
        $relatedBills = StudentBill::where('student_id', $bill->student_id)
            ->where('academic_year_id', $bill->academic_year_id)
            ->where('payment_type_id', $bill->payment_type_id)
            ->with(['payments.processedBy'])
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $totalAmount = $relatedBills->sum('amount');
        $totalPaid = $relatedBills->sum('paid_amount');
        $totalOutstanding = $totalAmount - $totalPaid;
        
        // Get all related payments for these bills
        $allPayments = Payment::whereIn('bill_id', $relatedBills->pluck('id'))
            ->with(['bill', 'processedBy'])
            ->orderByDesc('payment_date')
            ->get();

        return view('admin.bills.show', compact('bill', 'relatedBills', 'totalAmount', 'totalPaid', 'totalOutstanding', 'allPayments'));
    }

    public function edit(StudentBill $bill)
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403, 'Hanya Super Admin yang dapat mengubah atau menghapus data keuangan.');
        
        $students = Student::orderBy('full_name')->get();
        $schools = School::where('is_active', true)->orderBy('name')->get();
        $academicYears = AcademicYear::orderBy('year', 'desc')->get();
        $paymentTypes = PaymentType::orderBy('type_name')->get();
        $semesters = Semester::orderBy('semester_name')->get();

        return view('admin.bills.edit', compact('bill', 'students', 'schools', 'academicYears', 'paymentTypes', 'semesters'));
    }

    public function update(Request $request, StudentBill $bill)
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403, 'Hanya Super Admin yang dapat mengubah atau menghapus data keuangan.');
        
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'due_date' => 'nullable|date',
            'status' => 'required|in:belum_bayar,cicilan,lunas',
            'notes' => 'nullable|string',
        ]);

        $bill->update($validated);

        return redirect()->route('admin.bills.index')
            ->with('success', 'Tagihan berhasil diperbarui.');
    }

    public function destroy(StudentBill $bill)
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403, 'Hanya Super Admin yang dapat mengubah atau menghapus data keuangan.');
        
        if ($bill->payments()->exists()) {
            return redirect()->back()
                ->with('error', 'Tagihan tidak dapat dihapus karena sudah ada pembayaran terkait.');
        }

        $billId = $bill->id;
        $bill->delete();

        $referer = request()->headers->get('referer');
        if ($referer && !str_contains($referer, "/admin/bills/{$billId}")) {
            return redirect()->back()
                ->with('success', 'Tagihan berhasil dihapus.');
        }

        return redirect()->route('admin.bills.index')
            ->with('success', 'Tagihan berhasil dihapus.');
    }

    // ──────────────────────────────────────────────
    //  Bulk Operations (delegate to service)
    // ──────────────────────────────────────────────

    public function bulkCreate()
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403, 'Hanya Super Admin yang dapat mengubah atau menghapus data keuangan.');
        
        $schools = School::where('is_active', true)->orderBy('name')->get();
        $paymentTypes = PaymentType::where('is_active', true)
            ->orderBy('type_name')
            ->get()
            ->groupBy('type_code')
            ->map(fn($group) => $group->first())
            ->values();
        $academicYears = AcademicYear::orderBy('year', 'desc')->get();
        $semesters = Semester::orderBy('semester_number')->get();
        $activeYear = AcademicYear::where('is_active', true)->first() ?? AcademicYear::orderBy('year', 'desc')->first();
        $classrooms = Classroom::where('academic_year_id', $activeYear?->id)
            ->with('school')
            ->orderBy('class_name')
            ->get();

        return view('admin.bills.bulk-create', compact('schools', 'paymentTypes', 'academicYears', 'semesters', 'classrooms'));
    }

    public function bulkStore(Request $request)
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403, 'Hanya Super Admin yang dapat mengubah atau menghapus data keuangan.');
        
        $validated = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'payment_type_id' => 'required|exists:payment_types,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester_id' => 'nullable|exists:semesters,id',
            'notes' => 'nullable|string',
            'filter_by' => 'required|in:all,classroom,grade',
            'classroom_id' => 'nullable|exists:classrooms,id',
            'grade_level' => 'nullable|integer|min:7|max:12',
            'billing_type' => 'required|in:single,monthly',
            'generate_months' => 'nullable|integer|min:1|max:12',
            'start_month' => 'nullable|integer|min:1|max:12',
            'due_day' => 'nullable|integer|min:1|max:31',
            'monthly_amount' => 'nullable|numeric|min:0',
            'amount' => 'nullable|numeric|min:0',
            'due_date' => 'nullable|date',
            'single_month' => 'nullable|integer|min:1|max:12',
        ]);

        $students = $this->billService->getFilteredStudents(
            $validated['school_id'],
            $validated['filter_by'],
            $validated['classroom_id'] ?? null,
            $validated['grade_level'] ?? null,
            $validated['academic_year_id'],
        );

        if ($students->isEmpty()) {
            $totalStudents = Student::where('school_id', $validated['school_id'])->count();
            if ($totalStudents == 0) {
                return redirect()->back()->with('error', 'Tidak ada siswa di sekolah yang dipilih.');
            }
            if ($validated['filter_by'] == 'classroom') {
                return redirect()->back()->with('error', 'Tidak ada siswa aktif di kelas yang dipilih.');
            }
            if ($validated['filter_by'] == 'grade') {
                return redirect()->back()->with('error', "Tidak ada siswa aktif di tingkat {$validated['grade_level']}.");
            }
            return redirect()->back()->with('error', 'Tidak ada siswa yang sesuai dengan filter.');
        }

        try {
            if ($validated['billing_type'] === 'monthly' && $request->filled('generate_months')) {
                $billsCreated = $this->billService->generateRecurringBills(
                    $students,
                    $validated,
                    (int) $validated['generate_months'],
                    (int) ($validated['start_month'] ?? 7),
                    (float) $validated['monthly_amount'],
                    (int) ($validated['due_day'] ?? 10),
                );

                return redirect()->route('admin.bills.index')
                    ->with('success', "Berhasil membuat {$billsCreated} tagihan ({$validated['generate_months']} bulan) untuk " . $students->count() . " siswa.");
            } else {
                $billsCreated = $this->billService->generateSingleBills(
                    $students,
                    $validated,
                    (float) $validated['amount'],
                    (int) ($validated['single_month'] ?? 3),
                );

                return redirect()->route('admin.bills.index')
                    ->with('success', "Berhasil membuat {$billsCreated} tagihan untuk " . $students->count() . " siswa.");
            }
        } catch (\Exception $e) {
            Log::error('Gagal membuat tagihan: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan pada sistem. Silakan coba lagi.');
        }
    }

    public function bulkWaiveLateFee(Request $request)
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403, 'Hanya Super Admin yang dapat mengubah atau menghapus data keuangan.');
        
        $request->validate([
            'bill_ids' => 'required|array|min:1',
            'bill_ids.*' => 'exists:student_bills,id',
            'reason' => 'required|string|max:1000',
        ], [
            'bill_ids.required' => 'Tidak ada tagihan yang dipilih',
            'bill_ids.array' => 'Format data tagihan tidak valid',
            'bill_ids.min' => 'Minimal pilih 1 tagihan',
            'reason.required' => 'Alasan penghapusan biaya administrasi wajib diisi',
            'reason.max' => 'Alasan maksimal 1000 karakter',
        ]);

        try {
            $updated = $this->billService->waiveLateFees(
                $request->bill_ids,
                $request->reason,
                auth()->id(),
            );

            return response()->json([
                'success' => true,
                'message' => "Berhasil menghapus biaya administrasi untuk {$updated} tagihan",
                'count' => $updated,
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal memproses pembebasan denda: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada sistem. Silakan coba lagi.',
            ], 500);
        }
    }

    public function bulkUpdateAmount(Request $request)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && !$user->isAdminSekolah()) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah data tagihan.');
        }

        $validated = $request->validate([
            'bill_ids' => 'nullable|array',
            'bill_ids.*' => 'exists:student_bills,id',
            'school_id' => 'nullable|exists:schools,id',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'payment_type_id' => 'nullable|exists:payment_types,id',
            'classroom_id' => 'nullable|exists:classrooms,id',
            'grade_level' => 'nullable|integer',
            'new_amount' => 'required|numeric|min:0',
        ]);

        $billIds = $request->bill_ids ?? [];

        if (empty($billIds)) {
            $query = StudentBill::query();
            
            $schoolId = $user->isSuperAdmin() ? $request->school_id : $user->school_id;
            if ($schoolId) {
                $query->whereHas('student', fn($q) => $q->where('school_id', $schoolId));
            }
            if ($request->filled('academic_year_id')) {
                $query->where('academic_year_id', $request->academic_year_id);
            }
            if ($request->filled('payment_type_id')) {
                $query->where('payment_type_id', $request->payment_type_id);
            }
            if ($request->filled('old_amount')) {
                $query->where('amount', $request->old_amount);
            }
            if ($request->filled('month')) {
                $query->where('month', $request->month);
            }
            if ($request->filled('year')) {
                $query->where('year', $request->year);
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
            $updated = $this->billService->bulkUpdateAmount($billIds, (float)$validated['new_amount'], auth()->id());

            $msg = "Berhasil memperbarui nominal menjadi Rp " . number_format($validated['new_amount'], 0, ',', '.') . " untuk {$updated} tagihan.";
            
            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => $msg, 'count' => $updated]);
            }
            return redirect()->back()->with('success', $msg);
        } catch (\Exception $e) {
            Log::error('Gagal update nominal massal: ' . $e->getMessage());
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Terjadi kesalahan sistem.'], 500);
            }
            return redirect()->back()->with('error', 'Terjadi kesalahan pada sistem.');
        }
    }

    public function bulkDelete(Request $request)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && !$user->isAdminSekolah()) {
            abort(403, 'Anda tidak memiliki hak akses untuk menghapus data tagihan.');
        }

        $validated = $request->validate([
            'bill_ids' => 'nullable|array',
            'bill_ids.*' => 'exists:student_bills,id',
            'school_id' => 'nullable|exists:schools,id',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'payment_type_id' => 'nullable|exists:payment_types,id',
            'classroom_id' => 'nullable|exists:classrooms,id',
            'grade_level' => 'nullable|integer',
        ]);

        $billIds = $request->bill_ids ?? [];

        if (empty($billIds)) {
            $query = StudentBill::query();
            
            $schoolId = $user->isSuperAdmin() ? $request->school_id : $user->school_id;
            if ($schoolId) {
                $query->whereHas('student', fn($q) => $q->where('school_id', $schoolId));
            }
            if ($request->filled('academic_year_id')) {
                $query->where('academic_year_id', $request->academic_year_id);
            }
            if ($request->filled('payment_type_id')) {
                $query->where('payment_type_id', $request->payment_type_id);
            }
            if ($request->filled('old_amount')) {
                $query->where('amount', $request->old_amount);
            }
            if ($request->filled('month')) {
                $query->where('month', $request->month);
            }
            if ($request->filled('year')) {
                $query->where('year', $request->year);
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
            $result = $this->billService->bulkDeleteBills($billIds, auth()->id());

            $msg = "Berhasil menghapus {$result['deleted']} tagihan yang belum dibayar.";
            if ($result['skipped'] > 0) {
                $msg .= " ({$result['skipped']} tagihan dilewati karena sudah ada riwayat pembayaran).";
            }

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => $msg, 'deleted' => $result['deleted'], 'skipped' => $result['skipped']]);
            }
            return redirect()->back()->with('success', $msg);
        } catch (\Exception $e) {
            Log::error('Gagal hapus massal tagihan: ' . $e->getMessage());
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Terjadi kesalahan sistem.'], 500);
            }
            return redirect()->back()->with('error', 'Terjadi kesalahan pada sistem.');
        }
    }

    public function export(Request $request)
    {
        $fileName = 'Tagihan_' . now()->format('Y-m-d_His') . '.xlsx';

        return Excel::download(
            new BillsExport($request->academic_year_id, $request->payment_type_id, $request->search),
            $fileName
        );
    }
}

