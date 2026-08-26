<?php

namespace App\Http\Controllers\Yayasan;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Employee;
use App\Models\PaymentType;
use App\Models\School;
use App\Models\SchoolContribution;
use App\Models\Semester;
use App\Models\StudentClass;
use App\Services\EmployeeAssignmentService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class FinancialRecapController extends Controller
{
    protected EmployeeAssignmentService $assignmentService;

    public function __construct(EmployeeAssignmentService $assignmentService)
    {
        $this->assignmentService = $assignmentService;
    }

    /**
     * Tampilan Halaman Rekapitulasi Keuangan Yayasan (Halaman 3)
     */
    public function index(Request $request)
    {
        $data = $this->getFinancialRecapData($request);
        return view('yayasan.financial_recap.index', $data);
    }

    /**
     * Export PDF Rekapitulasi Keuangan Yayasan
     */
    public function exportPdf(Request $request)
    {
        $data = $this->getFinancialRecapData($request);
        $pdf = Pdf::loadView('yayasan.financial_recap.pdf', $data);
        $yearSafe = str_replace(['/', '\\'], '-', $data['currentYear']->year ?? 'TP');
        $fileName = 'Rekapitulasi_Keuangan_Yayasan_' . $yearSafe . '.pdf';
        return $pdf->download($fileName);
    }

    /**
     * Kalkulasi Data Konsolidasi Finansial
     */
    protected function getFinancialRecapData(Request $request): array
    {
        $allYears = AcademicYear::orderBy('id', 'desc')->get();
        $selectedYearId = $request->query('academic_year_id');

        $currentYear = $selectedYearId
            ? AcademicYear::find($selectedYearId)
            : (AcademicYear::where('is_active', true)->first() ?? AcademicYear::first());

        $periodMode = $request->query('period_mode', 'annual');
        $viewMode = $request->query('view_mode', 'cash'); // 'cash' (Realisasi Kas) atau 'accrual' (Potensi Teoretis)
        $multiplier = ($periodMode === 'monthly') ? 1 : 12;

        $currentSemester = Semester::where('academic_year_id', $currentYear->id ?? 0)
            ->where('is_active', true)
            ->first()
            ?? Semester::where('academic_year_id', $currentYear->id ?? 0)->first()
            ?? Semester::first();

        // 1. DITARIK DARI HALAMAN 1: Total Pendapatan Seluruh Unit Sekolah (Cash vs Accrual)
        $schools = School::schoolsOnly()->where('is_active', true)->orderBy('name')->get();
        $schoolSppData = [];
        $grandTotalIncome = 0;
        $grandTotalIncomeMonthly = 0;

        foreach ($schools as $school) {
            $contribution = SchoolContribution::where('school_id', $school->id)
                ->where('academic_year_id', $currentYear->id ?? 0)
                ->first();

            $savedSppRates = $contribution->spp_rates ?? [];
            $defaultSppType = PaymentType::where('school_id', $school->id)
                ->where('type_code', 'SPP')
                ->where('is_active', true)
                ->first();

            $masterSppAmount = (float) ($defaultSppType->yayasan_share_amount ?? $defaultSppType->amount ?? 0);
            $levels = $school->getGradeLevels();
            $schoolPotentialIncomeMonthly = 0;
            $totalStudentsInSchool = 0;

            foreach ($levels as $level) {
                $classroomIds = Classroom::where('school_id', $school->id)
                    ->where('grade_level', $level)
                    ->pluck('id');

                $studentIds = StudentClass::whereIn('classroom_id', $classroomIds)
                    ->where('academic_year_id', $currentYear->id ?? 0)
                    ->distinct('student_id')
                    ->pluck('student_id')
                    ->toArray();

                $studentCount = count($studentIds);

                if (isset($savedSppRates[(string)$level]) && $savedSppRates[(string)$level] > 0) {
                    $sppMonthly = (float)$savedSppRates[(string)$level];
                } else {
                    $sppMonthly = $masterSppAmount;
                }

                $schoolPotentialIncomeMonthly += ($studentCount * $sppMonthly);
                $totalStudentsInSchool += $studentCount;
            }

            // Realisasi Uang Kas Masuk Nyata (Verified Payments)
            $actualRealizedPayment = (float) \App\Models\Payment::whereHas('student', function($q) use ($school) {
                $q->where('school_id', $school->id);
            })->whereHas('bill', function($q) use ($currentYear) {
                $q->where('academic_year_id', $currentYear->id ?? 0);
            })->where('is_verified', true)->sum('amount_paid');

            if ($viewMode === 'cash') {
                $schoolTotalIncomePeriod = $actualRealizedPayment;
                $schoolTotalIncomeMonthly = $schoolTotalIncomePeriod / ($multiplier ?: 1);
            } else {
                $schoolTotalIncomeMonthly = $schoolPotentialIncomeMonthly;
                $schoolTotalIncomePeriod = $schoolPotentialIncomeMonthly * $multiplier;
            }

            $schoolSppData[] = [
                'school' => $school,
                'total_students' => $totalStudentsInSchool,
                'income_monthly' => $schoolTotalIncomeMonthly,
                'income_total' => $schoolTotalIncomePeriod,
                'realized_actual' => $actualRealizedPayment,
                'potential_accrual' => $schoolPotentialIncomeMonthly * $multiplier,
            ];

            $grandTotalIncomeMonthly += $schoolTotalIncomeMonthly;
            $grandTotalIncome += $schoolTotalIncomePeriod;
        }

        // 2. DITARIK DARI HALAMAN 2: Total Belanja Pegawai & Total Belanja Operasional
        $allSchools = School::where('is_active', true)
            ->orderByRaw("CASE WHEN type = 'yayasan' THEN 2 ELSE 1 END, name ASC")
            ->get();

        $totalGajiLembagaMonthly = 0;
        $schoolSalaryData = [];
        foreach ($allSchools as $sch) {
            $employees = Employee::where('school_id', $sch->id)->where('is_active', true)->get();
            $schoolGajiMonthly = 0;
            $empCount = 0;
            if ($currentYear && $currentSemester) {
                foreach ($employees as $emp) {
                    $sal = $this->assignmentService->calculateFullSalary($emp, $currentYear, $currentSemester, $sch->type, $sch->id);
                    $schoolGajiMonthly += (float) ($sal['gross_pay'] ?? 0);
                    $empCount++;
                }
            }
            $schoolSalaryData[] = [
                'school' => $sch,
                'employee_count' => $empCount,
                'salary_monthly' => $schoolGajiMonthly,
                'salary_total' => $schoolGajiMonthly * $multiplier,
            ];
            $totalGajiLembagaMonthly += $schoolGajiMonthly;
        }
        $totalGajiLembagaPeriod = $totalGajiLembagaMonthly * $multiplier;

        $yayasanSchool = School::where('type', 'yayasan')->first();
        $yayasanContribution = $yayasanSchool
            ? SchoolContribution::where('school_id', $yayasanSchool->id)->where('academic_year_id', $currentYear->id ?? 0)->first()
            : null;

        $savedDetails = $yayasanContribution->expense_details ?? [];
        $monthlyBelanjaOps = 0;
        
        if (is_array($savedDetails) && count($savedDetails) > 0) {
            foreach ($savedDetails as $code => $item) {
                if (is_array($item)) {
                    $monthlyBelanjaOps += (float) ($item['amount'] ?? ($item['tariff'] ?? 0) * ($item['volume'] ?? 1));
                } elseif (is_numeric($item)) {
                    $monthlyBelanjaOps += (float) $item;
                }
            }
        }

        if ($monthlyBelanjaOps == 0 && $yayasanContribution) {
            $monthlyBelanjaOps = (float) ($yayasanContribution->authorized_expense ?? 0);
        }

        $operationalExpenseItems = [];
        foreach (FoundationExpenseController::OPERATIONAL_ACCOUNTS as $code => $account) {
            $amount = 0;
            if (isset($savedDetails[$code])) {
                $item = $savedDetails[$code];
                if (is_array($item)) {
                    $amount = (float) ($item['amount'] ?? ($item['tariff'] ?? 0) * ($item['volume'] ?? 1));
                } elseif (is_numeric($item)) {
                    $amount = (float) $item;
                }
            }
            $operationalExpenseItems[] = [
                'code' => $code,
                'name' => $account['name'],
                'icon' => $account['icon'],
                'amount_monthly' => $amount,
                'amount_total' => $amount * $multiplier,
            ];
        }

        $totalBelanjaOpsPeriod = $monthlyBelanjaOps * $multiplier;
        $grandTotalBelanjaMonthly = $totalGajiLembagaMonthly + $monthlyBelanjaOps;
        $grandTotalBelanjaPeriod = $totalGajiLembagaPeriod + $totalBelanjaOpsPeriod;

        // 3. Saldo Bersih Akhir Perguruan
        $grandTotalSaldoAkhirMonthly = $grandTotalIncomeMonthly - $grandTotalBelanjaMonthly;
        $grandTotalSaldoAkhir = $grandTotalIncome - $grandTotalBelanjaPeriod;

        return [
            'currentYear' => $currentYear,
            'allYears' => $allYears,
            'periodMode' => $periodMode,
            'viewMode' => $viewMode,
            'multiplier' => $multiplier,
            'schoolSppData' => $schoolSppData,
            'grandTotalIncome' => $grandTotalIncome,
            'grandTotalIncomeMonthly' => $grandTotalIncomeMonthly,
            'totalGajiLembagaPeriod' => $totalGajiLembagaPeriod,
            'totalGajiLembagaMonthly' => $totalGajiLembagaMonthly,
            'totalBelanjaOpsPeriod' => $totalBelanjaOpsPeriod,
            'totalBelanjaOpsMonthly' => $monthlyBelanjaOps,
            'grandTotalBelanjaMonthly' => $grandTotalBelanjaMonthly,
            'grandTotalBelanjaPeriod' => $grandTotalBelanjaPeriod,
            'grandTotalSaldoAkhirMonthly' => $grandTotalSaldoAkhirMonthly,
            'grandTotalSaldoAkhir' => $grandTotalSaldoAkhir,
            'savedExpenseDetails' => $savedDetails,
            'expenseAccounts' => FoundationExpenseController::OPERATIONAL_ACCOUNTS,
            'schoolSalaryData' => $schoolSalaryData,
            'operationalExpenseItems' => $operationalExpenseItems,
        ];
    }
}
