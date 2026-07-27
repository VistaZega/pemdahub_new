<?php

namespace App\Http\Controllers\Yayasan;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\StudentBill;
use App\Models\Classroom;
use App\Models\Employee;
use App\Models\PaymentType;
use App\Models\SchoolContribution;
use App\Services\EmployeeAssignmentService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class ContributionBalanceController extends Controller
{
    protected EmployeeAssignmentService $assignmentService;

    public function __construct(EmployeeAssignmentService $assignmentService)
    {
        $this->assignmentService = $assignmentService;
    }

    /**
     * Tampilkan Halaman Saldo Kontribusi Unit Sekolah
     */
    public function index(Request $request)
    {
        $academicYearId = $request->input('academic_year_id');
        $periodMode = $request->input('period_mode', 'annual'); // 'annual' (12 bulan) atau 'monthly' (1 bulan)

        $data = $this->getContributionData($academicYearId, $periodMode);

        return view('yayasan.contribution_balance.index', $data);
    }

    /**
     * Simpan / Update Belanja Otorisasi & Tarif SPP per Level
     */
    public function store(Request $request)
    {
        $request->validate([
            'school_id' => 'required|exists:schools,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'authorized_expense' => 'nullable|numeric|min:0',
            'spp_rates' => 'nullable|array',
            'spp_rates.*' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $schoolId = $request->input('school_id');
        $academicYearId = $request->input('academic_year_id');
        $authorizedExpense = (float) $request->input('authorized_expense', 0);
        $sppRates = $request->input('spp_rates', []);
        $notes = $request->input('notes');

        // Clean & cast spp_rates to float
        $cleanedSpp = [];
        if (is_array($sppRates)) {
            foreach ($sppRates as $level => $rate) {
                $cleanedSpp[$level] = (float) $rate;
            }
        }

        SchoolContribution::updateOrCreate(
            [
                'school_id' => $schoolId,
                'academic_year_id' => $academicYearId,
            ],
            [
                'authorized_expense' => $authorizedExpense,
                'spp_rates' => $cleanedSpp,
                'notes' => $notes,
            ]
        );

        return back()->with('success', 'Data Belanja Otorisasi & Tarif SPP unit sekolah berhasil diperbarui.');
    }

    /**
     * Export Laporan Saldo Kontribusi ke PDF
     */
    public function exportPdf(Request $request)
    {
        $academicYearId = $request->input('academic_year_id');
        $periodMode = $request->input('period_mode', 'annual');

        $data = $this->getContributionData($academicYearId, $periodMode);

        $pdf = Pdf::loadView('yayasan.contribution_balance.pdf', $data)
            ->setPaper('a4', 'landscape');

        $periodLabel = $periodMode === 'monthly' ? 'Bulanan' : 'Tahunan_12Bulan';
        $fileName = 'Laporan_Saldo_Kontribusi_Unit_' . str_replace('/', '_', $data['currentYear']->year ?? '2026_2027') . '_' . $periodLabel . '.pdf';

        return $pdf->download($fileName);
    }

    /**
     * Helper untuk menghitung Pendapatan, Pengeluaran, dan Saldo per Unit Sekolah
     */
    private function getContributionData($academicYearId = null, string $periodMode = 'annual'): array
    {
        // 1. Ambil Tahun Pelajaran
        if ($academicYearId) {
            $currentYear = AcademicYear::find($academicYearId);
        } else {
            $currentYear = AcademicYear::where('is_active', true)->first()
                ?? AcademicYear::where('year', 'like', '%2026/2027%')->first()
                ?? AcademicYear::orderBy('year', 'desc')->first();
        }

        $allYears = AcademicYear::orderBy('year', 'desc')->get();

        // 2. Ambil Semester Aktif/Default untuk perhitungan gaji
        $currentSemester = Semester::where('academic_year_id', $currentYear->id ?? 0)
            ->where('is_active', true)
            ->first()
            ?? Semester::where('academic_year_id', $currentYear->id ?? 0)->first()
            ?? Semester::first();

        // Ambil seluruh unit aktif (termasuk Unit Yayasan)
        $schools = School::where('is_active', true)
            ->orderByRaw("CASE WHEN type = 'yayasan' THEN 2 ELSE 1 END, name ASC")
            ->get();

        $multiplier = ($periodMode === 'monthly') ? 1 : 12;

        $schoolData = [];
        $grandTotalIncome = 0;
        $grandTotalGaji = 0;
        $grandTotalOtorisasi = 0;
        $grandTotalExpense = 0;
        $grandTotalSaldo = 0;

        foreach ($schools as $school) {
            // Ambil record contribution jika ada
            $contribution = SchoolContribution::where('school_id', $school->id)
                ->where('academic_year_id', $currentYear->id ?? 0)
                ->first();

            $savedSppRates = $contribution->spp_rates ?? [];
            $authorizedExpenseMonthly = (float) ($contribution->authorized_expense ?? 0);
            $authorizedExpenseTotal = $authorizedExpenseMonthly * $multiplier;

            // Default SPP dari master payment_types
            $defaultSppType = PaymentType::where('school_id', $school->id)
                ->where('type_code', 'SPP')
                ->where('is_active', true)
                ->first();

            $masterSppAmount = (float) ($defaultSppType->amount ?? 0);

            // Tingkat kelas per jenis sekolah
            $levels = $school->getGradeLevels();
            $levelBreakdown = [];
            $schoolTotalIncome = 0;
            $schoolTotalIncomeMonthly = 0;
            $totalStudentsInSchool = 0;

            foreach ($levels as $level) {
                // Rombel untuk grade level ini
                $classroomIds = Classroom::where('school_id', $school->id)
                    ->where('grade_level', $level)
                    ->pluck('id');

                $studentIds = StudentClass::whereIn('classroom_id', $classroomIds)
                    ->where('academic_year_id', $currentYear->id ?? 0)
                    ->distinct('student_id')
                    ->pluck('student_id')
                    ->toArray();

                $studentCount = count($studentIds);

                // ════════════════ DITARIK LANGSUNG DARI TABEL STUDENT_BILLS ════════════════
                $billsQuery = StudentBill::whereIn('student_id', $studentIds)
                    ->whereHas('paymentType', function ($q) {
                        $q->where('type_code', 'SPP');
                    })
                    ->when($currentYear, function ($q) use ($currentYear) {
                        $q->where('academic_year_id', $currentYear->id);
                    });

                $sumBilledAnnual = (float) $billsQuery->sum('amount');
                $sumPaidAnnual = (float) $billsQuery->sum('paid_amount');
                $avgBillMonthly = (float) $billsQuery->avg('amount');

                // Tarif SPP resmi per tingkat (Prioritaskan Setting Tarif Yayasan -> Master PaymentType)
                if (isset($savedSppRates[(string)$level]) && $savedSppRates[(string)$level] > 0) {
                    $sppMonthly = (float)$savedSppRates[(string)$level];
                    $sppSource = 'Setting Tarif Yayasan';
                } else {
                    $sppMonthly = $masterSppAmount;
                    $sppSource = 'Master SPP (payment_types)';
                }

                $incomeMonthly = $studentCount * $sppMonthly;
                $incomeTotal = $incomeMonthly * $multiplier;

                $levelBreakdown[] = [
                    'level' => $level,
                    'student_count' => $studentCount,
                    'spp_monthly' => $sppMonthly,
                    'spp_period' => $sppMonthly * $multiplier,
                    'spp_source' => $sppSource,
                    'income_monthly' => $sppMonthly * $studentCount,
                    'income_total' => $incomeTotal,
                    'paid_total' => ($periodMode === 'monthly') ? ($sumPaidAnnual / 12) : $sumPaidAnnual,
                ];

                $schoolTotalIncome += $incomeTotal;
                $schoolTotalIncomeMonthly += ($sppMonthly * $studentCount);
                $totalStudentsInSchool += $studentCount;
            }

            // ════════════════ HITUNG GAJI GURU & PEGAWAI UNIT ════════════════
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
            $schoolTotalExpense = $totalSalaryPeriod + $authorizedExpenseTotal;
            $saldo = $schoolTotalIncome - $schoolTotalExpense;

            $schoolData[] = [
                'school' => $school,
                'contribution' => $contribution,
                'levels' => $levelBreakdown,
                'total_students' => $totalStudentsInSchool,
                'income_monthly' => $schoolTotalIncomeMonthly,
                'income_total' => $schoolTotalIncome,
                'employee_count' => $employees->count(),
                'salary_monthly' => $monthlySalarySum,
                'salary_total' => $totalSalaryPeriod,
                'authorized_expense_monthly' => $authorizedExpenseMonthly,
                'authorized_expense_total' => $authorizedExpenseTotal,
                'expense_total' => $schoolTotalExpense,
                'saldo' => $saldo,
                'is_surplus' => $saldo >= 0,
            ];

            $grandTotalIncome += $schoolTotalIncome;
            $grandTotalGaji += $totalSalaryPeriod;
            $grandTotalOtorisasi += $authorizedExpenseTotal;
            $grandTotalExpense += $schoolTotalExpense;
            $grandTotalSaldo += $saldo;
        }

        return [
            'currentYear' => $currentYear,
            'allYears' => $allYears,
            'periodMode' => $periodMode,
            'multiplier' => $multiplier,
            'schoolData' => $schoolData,
            'grandTotalIncome' => $grandTotalIncome,
            'grandTotalGaji' => $grandTotalGaji,
            'grandTotalOtorisasi' => $grandTotalOtorisasi,
            'grandTotalExpense' => $grandTotalExpense,
            'grandTotalSaldo' => $grandTotalSaldo,
        ];
    }
}
