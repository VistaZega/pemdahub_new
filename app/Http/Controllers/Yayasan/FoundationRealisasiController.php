<?php

namespace App\Http\Controllers\Yayasan;

use App\Http\Controllers\Controller;
use App\Models\StudentStatusHistory;
use App\Models\StudentBill;
use App\Models\School;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\StudentClass;
use App\Models\Classroom;
use App\Models\Employee;
use App\Models\PaymentType;
use App\Models\SchoolContribution;
use App\Services\EmployeeAssignmentService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class FoundationRealisasiController extends Controller
{
    protected EmployeeAssignmentService $assignmentService;

    public function __construct(EmployeeAssignmentService $assignmentService)
    {
        $this->assignmentService = $assignmentService;
    }

    /**
     * Tampilkan Halaman Realisasi Anggaran Belanja Yayasan
     */
    public function index(Request $request)
    {
        $academicYearId = $request->input('academic_year_id');
        $month = $request->input('month', date('n'));
        $year = $request->input('year', date('Y'));
        $periodMode = $request->input('period_mode', 'monthly'); // 'monthly' atau 'annual'

        $data = $this->getRealisasiData($academicYearId, (int) $month, (int) $year, $periodMode);

        return view('yayasan.realisasi.index', $data);
    }

    /**
     * Export Realisasi Anggaran Belanja ke PDF
     */
    public function exportPdf(Request $request)
    {
        $academicYearId = $request->input('academic_year_id');
        $month = $request->input('month', date('n'));
        $year = $request->input('year', date('Y'));
        $periodMode = $request->input('period_mode', 'monthly');

        $data = $this->getRealisasiData($academicYearId, (int) $month, (int) $year, $periodMode);

        $pdf = Pdf::loadView('yayasan.realisasi.pdf', $data);
        $pdf->setPaper('A4', 'landscape');

        $fileName = 'Realisasi-Anggaran-Yayasan-' . ($data['activeYear']->year ?? 'Tahun-Aktif') . '.pdf';

        return $pdf->stream($fileName);
    }

    /**
     * Helper untuk menghimpun data Realisasi Anggaran seluruh unit sekolah & yayasan
     */
    private function getRealisasiData(?int $academicYearId = null, int $month = 8, int $year = 2026, string $periodMode = 'monthly'): array
    {
        $academicYears = AcademicYear::orderBy('year', 'desc')->get();
        $activeYear = $academicYearId 
            ? AcademicYear::find($academicYearId) 
            : AcademicYear::where('is_active', true)->first();

        if (!$activeYear && $academicYears->count() > 0) {
            $activeYear = $academicYears->first();
        }

        $activeSemester = Semester::where('is_active', true)->first();
        if (!$activeSemester) {
            $activeSemester = Semester::first();
        }

        $schools = School::schoolsOnly()->where('is_active', true)->orderBy('name')->get();
        $allSchools = School::where('is_active', true)->orderByRaw("CASE WHEN type = 'yayasan' THEN 2 ELSE 1 END, name ASC")->get();
        
        $multiplier = ($periodMode === 'annual') ? 12 : 1;

        $operationalAccounts = FoundationRabController::OPERATIONAL_ACCOUNTS;

        $grandTotalIncome = 0;
        $grandTotalSalary = 0;
        $grandTotalOperational = 0;
        $grandTotalExpense = 0;
        $grandTotalBalance = 0;

        $incomeData = [];
        $expenseData = [];
        $totalStudentsAll = 0;

        // 1. REALISASI PENDAPATAN SPP (Hanya Sekolah)
        foreach ($schools as $school) {
            $contribution = SchoolContribution::where('school_id', $school->id)
                ->where('academic_year_id', $activeYear->id ?? 0)
                ->first();

            $defaultSppType = PaymentType::where('school_id', $school->id)
                ->where(function ($q) {
                    $q->where('type_code', 'SPP')
                      ->orWhere('type_name', 'LIKE', '%SPP%')
                      ->orWhere('type_name', 'LIKE', '%Uang Sekolah%');
                })
                ->where('is_active', true)
                ->first();

            $masterSppAmount = (float) ($defaultSppType->yayasan_share_amount ?? $defaultSppType->amount ?? 0);

            $levels = $school->getGradeLevels();
            $levelBreakdown = [];
            $schoolTotalIncomeMonthly = 0;
            $schoolTotalStudents = 0;

            foreach ($levels as $level) {
                // 1. Ambil SEMUA ID kelas (Reguler + Industri) untuk jenjang ini untuk menghitung SELURUH siswa aktif
                $allClassroomIds = Classroom::where('school_id', $school->id)
                    ->where('grade_level', $level)
                    ->pluck('id');

                $allStudentIds = StudentClass::whereIn('classroom_id', $allClassroomIds)
                    ->where('academic_year_id', $activeYear->id ?? 0)
                    ->whereHas('student', function ($q) {
                        $q->whereIn('status', StudentStatusHistory::ACTIVE_STATUSES);
                    })
                    ->distinct('student_id')
                    ->pluck('student_id')
                    ->toArray();

                $studentCount = count($allStudentIds); // Total SELURUH siswa (Reguler + Industri)

                // 2. Tentukan Tarif SPP Reguler sebagai acuan (Kelebihan biaya di kelas Industri tidak masuk laporan Yayasan)
                $regulerClassroomIds = Classroom::where('school_id', $school->id)
                    ->where('grade_level', $level)
                    ->where(function($q) {
                        $q->where('class_type', 'reguler')
                          ->orWhereNull('class_type')
                          ->orWhere('class_type', '')
                          ->orWhere('class_type', '!=', 'industri');
                    })
                    ->pluck('id');

                $regulerStudentIds = StudentClass::whereIn('classroom_id', $regulerClassroomIds)
                    ->where('academic_year_id', $activeYear->id ?? 0)
                    ->distinct('student_id')
                    ->pluck('student_id')
                    ->toArray();

                $sppMonthly = round($masterSppAmount);
                $sppSource = $defaultSppType ? 'Master SPP Bendahara' : 'Belum Set';

                if (!empty($regulerStudentIds) && $defaultSppType) {
                    $regulerBill = StudentBill::whereIn('student_id', $regulerStudentIds)
                        ->where('academic_year_id', $activeYear->id ?? 0)
                        ->where('payment_type_id', $defaultSppType->id)
                        ->selectRaw('amount, COUNT(*) as cnt')
                        ->groupBy('amount')
                        ->orderBy('cnt', 'desc')
                        ->value('amount');

                    if ($regulerBill && $regulerBill > 0) {
                        $sppMonthly = round((float) $regulerBill);
                        $sppSource = 'Tarif SPP Reguler';
                    }
                } elseif (!empty($allStudentIds) && $defaultSppType) {
                    $minBill = StudentBill::whereIn('student_id', $allStudentIds)
                        ->where('academic_year_id', $activeYear->id ?? 0)
                        ->where('payment_type_id', $defaultSppType->id)
                        ->min('amount');

                    if ($minBill && $minBill > 0) {
                        $sppMonthly = round((float) $minBill);
                        $sppSource = 'Tarif SPP Reguler';
                    }
                }

                $incomeMonthly = round($studentCount * $sppMonthly);
                $incomeTotal = $incomeMonthly * $multiplier;

                $levelBreakdown[] = [
                    'level' => $level,
                    'student_count' => $studentCount,
                    'spp_monthly' => $sppMonthly,
                    'spp_source' => $sppSource,
                    'income_monthly' => $incomeMonthly,
                    'income_total' => $incomeTotal,
                ];

                $schoolTotalIncomeMonthly += $incomeMonthly;
                $schoolTotalStudents += $studentCount;
            }

            $schoolTotalIncome = $schoolTotalIncomeMonthly * $multiplier;
            $grandTotalIncome += $schoolTotalIncome;
            $totalStudentsAll += $schoolTotalStudents;

            $incomeData[$school->id] = [
                'school' => $school,
                'levels' => $levelBreakdown,
                'total_students' => $schoolTotalStudents,
                'income_monthly' => $schoolTotalIncomeMonthly,
                'income_total' => $schoolTotalIncome,
            ];
        }

        // 2 & 3. REALISASI GAJI & OPERASIONAL (Semua Unit termasuk Yayasan)
        foreach ($allSchools as $school) {
            // Gaji
            $employees = Employee::where('school_id', $school->id)->where('is_active', true)->get();
            $empCount = $employees->count();
            
            $sumSalaryMonthly = 0;
            if ($activeYear && $activeSemester) {
                foreach ($employees as $emp) {
                    $salData = $this->assignmentService->calculateFullSalary(
                        $emp,
                        $activeYear,
                        $activeSemester,
                        $school->type,
                        $school->id
                    );
                    $sumSalaryMonthly += (float) ($salData['gross_pay'] ?? 0);
                }
            }
            $sumSalaryPeriod = $sumSalaryMonthly * $multiplier;
            $grandTotalSalary += $sumSalaryPeriod;

            // Operasional Pagu (RAB)
            $contribution = SchoolContribution::where('school_id', $school->id)
                ->where('academic_year_id', $activeYear->id ?? 0)
                ->first();

            $rawSavedDetails = $contribution->expense_details ?? [];
            $sumOpsMonthlyRab = 0;
            
            $opsBreakdown = [];
            foreach ($operationalAccounts as $code => $acc) {
                $savedItem = $rawSavedDetails[$code] ?? null;
                if (is_array($savedItem)) {
                    $amt = (float) ($savedItem['amount'] ?? 0);
                } elseif (is_numeric($savedItem)) {
                    $amt = (float) $savedItem;
                } else {
                    $amt = 0;
                }
                $sumOpsMonthlyRab += $amt;
                $opsBreakdown[$code] = [
                    'name' => $acc['name'],
                    'amount' => $amt * $multiplier
                ];
            }
            $sumOpsPeriodRab = $sumOpsMonthlyRab * $multiplier;

            // Operasional Riil (Realisasi Pengeluaran Riil Sekolah)
            $realizedOpsQuery = \App\Models\OperationalExpense::where('school_id', $school->id);
            if ($periodMode === 'annual') {
                if ($activeYear) {
                    $realizedOpsQuery->where('academic_year_id', $activeYear->id);
                }
            } else {
                $realizedOpsQuery->whereMonth('expense_date', $month)->whereYear('expense_date', $year);
            }
            $sumOpsPeriodRealized = (float) $realizedOpsQuery->sum('amount');
            
            // Ops yang digunakan untuk total pengeluaran kas:
            // Jika ada transaksi riil operational_expenses, gunakan nilai riil. Jika belum ada, gunakan pagu RAB sebagai acuan.
            $effectiveOpsPeriod = $sumOpsPeriodRealized > 0 ? $sumOpsPeriodRealized : $sumOpsPeriodRab;

            $grandTotalOperational += $effectiveOpsPeriod;

            $totalExpensePeriod = $sumSalaryPeriod + $effectiveOpsPeriod;
            $grandTotalExpense += $totalExpensePeriod;

            $schoolIncome = $incomeData[$school->id]['income_total'] ?? 0;
            $schoolBalance = $schoolIncome - $totalExpensePeriod;

            $expenseData[] = [
                'school' => $school,
                'emp_count' => $empCount,
                'salary_monthly' => $sumSalaryMonthly,
                'salary_period' => $sumSalaryPeriod,
                'ops_rab_period' => $sumOpsPeriodRab,
                'ops_realized_period' => $sumOpsPeriodRealized,
                'ops_period' => $effectiveOpsPeriod,
                'ops_variance' => $sumOpsPeriodRab - $sumOpsPeriodRealized,
                'ops_breakdown' => $opsBreakdown,
                'total_expense' => $totalExpensePeriod,
                'income' => $schoolIncome,
                'balance' => $schoolBalance,
                'student_count' => $incomeData[$school->id]['total_students'] ?? 0
            ];
        }

        $grandTotalBalance = $grandTotalIncome - $grandTotalExpense;

        return [
            'academicYears' => $academicYears,
            'activeYear' => $activeYear,
            'activeSemester' => $activeSemester,
            'month' => $month,
            'year' => $year,
            'periodMode' => $periodMode,
            'multiplier' => $multiplier,
            'operationalAccounts' => $operationalAccounts,
            'incomeData' => $incomeData,
            'expenseData' => $expenseData,
            'grandTotalIncome' => $grandTotalIncome,
            'grandTotalSalary' => $grandTotalSalary,
            'grandTotalOperational' => $grandTotalOperational,
            'grandTotalExpense' => $grandTotalExpense,
            'grandTotalBalance' => $grandTotalBalance,
            'totalStudentsAll' => $totalStudentsAll,
        ];
    }
}
