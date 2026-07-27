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
use App\Models\StudentBill;
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
     * Tampilan Halaman Rekapitulasi Pendapatan & Belanja Yayasan (Halaman 3)
     */
    public function index(Request $request)
    {
        $data = $this->getFinancialRecapData($request);
        return view('yayasan.financial_recap.index', $data);
    }

    /**
     * Export PDF Rekapitulasi Pendapatan & Belanja Yayasan
     */
    public function exportPdf(Request $request)
    {
        $data = $this->getFinancialRecapData($request);
        $pdf = Pdf::loadView('yayasan.financial_recap.pdf', $data);
        return $pdf->download('Rekapitulasi_Keuangan_Yayasan_' . ($data['currentYear']->year ?? 'TP') . '.pdf');
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
        $multiplier = ($periodMode === 'monthly') ? 1 : 12;

        $currentSemester = Semester::where('academic_year_id', $currentYear->id ?? 0)
            ->where('is_active', true)
            ->first()
            ?? Semester::where('academic_year_id', $currentYear->id ?? 0)->first()
            ?? Semester::first();

        // 1. Ambil Unit Sekolah
        $schools = School::schoolsOnly()->where('is_active', true)->orderBy('name')->get();

        $schoolData = [];
        $grandTotalIncome = 0;
        $grandTotalGajiSekolah = 0;

        foreach ($schools as $school) {
            $contribution = SchoolContribution::where('school_id', $school->id)
                ->where('academic_year_id', $currentYear->id ?? 0)
                ->first();

            $savedSppRates = $contribution->spp_rates ?? [];
            $defaultSppType = PaymentType::where('school_id', $school->id)
                ->where('type_code', 'SPP')
                ->where('is_active', true)
                ->first();

            $masterSppAmount = (float) ($defaultSppType->amount ?? 0);

            $levels = $school->getGradeLevels();
            $schoolTotalIncome = 0;
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

                $incomeTotal = ($studentCount * $sppMonthly) * $multiplier;
                $schoolTotalIncome += $incomeTotal;
                $totalStudentsInSchool += $studentCount;
            }

            // Gaji Pegawai Sekolah
            $employees = Employee::where('school_id', $school->id)
                ->where('is_active', true)
                ->get();

            $monthlySalarySum = 0;
            if ($currentYear && $currentSemester) {
                foreach ($employees as $employee) {
                    $salary = $this->assignmentService->calculateFullSalary(
                        $employee,
                        $currentYear,
                        $currentSemester,
                        $school->type,
                        $school->id
                    );
                    $monthlySalarySum += (float) ($salary['thp'] ?? 0);
                }
            }

            $totalSalaryPeriod = $monthlySalarySum * $multiplier;

            $schoolData[] = [
                'school' => $school,
                'total_students' => $totalStudentsInSchool,
                'income_total' => $schoolTotalIncome,
                'employee_count' => $employees->count(),
                'salary_total' => $totalSalaryPeriod,
                'surplus_kontribusi' => $schoolTotalIncome - $totalSalaryPeriod,
            ];

            $grandTotalIncome += $schoolTotalIncome;
            $grandTotalGajiSekolah += $totalSalaryPeriod;
        }

        // 2. Data Unit Yayasan (Gaji Staf Yayasan & Belanja Operasional Terpusat)
        $yayasanSchool = School::where('type', 'yayasan')->first();
        $yayasanEmployees = $yayasanSchool
            ? Employee::where('school_id', $yayasanSchool->id)->where('is_active', true)->get()
            : collect();

        $monthlyYayasanSalary = 0;
        if ($currentYear && $currentSemester && $yayasanSchool) {
            foreach ($yayasanEmployees as $emp) {
                $sal = $this->assignmentService->calculateFullSalary($emp, $currentYear, $currentSemester, 'yayasan', $yayasanSchool->id);
                $monthlyYayasanSalary += (float) ($sal['thp'] ?? 0);
            }
        }
        $totalYayasanSalary = $monthlyYayasanSalary * $multiplier;

        $yayasanContribution = $yayasanSchool
            ? SchoolContribution::where('school_id', $yayasanSchool->id)->where('academic_year_id', $currentYear->id ?? 0)->first()
            : null;

        $savedDetails = $yayasanContribution->expense_details ?? [];
        $monthlyBelanjaOps = array_sum($savedDetails);
        if ($monthlyBelanjaOps == 0 && $yayasanContribution) {
            $monthlyBelanjaOps = (float) ($yayasanContribution->authorized_expense ?? 0);
        }
        $totalBelanjaOpsYayasan = $monthlyBelanjaOps * $multiplier;

        // 3. Grand Total Rekapitulasi Konsolidasi
        $grandTotalGajiLembaga = $grandTotalGajiSekolah + $totalYayasanSalary;
        $grandTotalPengeluaran = $grandTotalGajiLembaga + $totalBelanjaOpsYayasan;
        $grandTotalSaldoAkhir = $grandTotalIncome - $grandTotalPengeluaran;

        return [
            'currentYear' => $currentYear,
            'allYears' => $allYears,
            'periodMode' => $periodMode,
            'multiplier' => $multiplier,
            'schoolData' => $schoolData,
            'yayasanSchool' => $yayasanSchool,
            'yayasanEmployeeCount' => $yayasanEmployees->count(),
            'totalYayasanSalary' => $totalYayasanSalary,
            'totalBelanjaOpsYayasan' => $totalBelanjaOpsYayasan,
            'savedExpenseDetails' => $savedDetails,
            'expenseAccounts' => FoundationExpenseController::OPERATIONAL_EXPENSE_ACCOUNTS,
            'grandTotalIncome' => $grandTotalIncome,
            'grandTotalGajiSekolah' => $grandTotalGajiSekolah,
            'grandTotalGajiLembaga' => $grandTotalGajiLembaga,
            'grandTotalPengeluaran' => $grandTotalPengeluaran,
            'grandTotalSaldoAkhir' => $grandTotalSaldoAkhir,
        ];
    }
}
