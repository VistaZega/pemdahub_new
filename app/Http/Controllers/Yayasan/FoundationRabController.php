<?php

namespace App\Http\Controllers\Yayasan;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentStatusHistory;
use App\Models\Employee;
use App\Models\PaymentType;
use App\Models\SchoolContribution;
use App\Services\EmployeeAssignmentService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class FoundationRabController extends Controller
{
    protected EmployeeAssignmentService $assignmentService;

    public function __construct(EmployeeAssignmentService $assignmentService)
    {
        $this->assignmentService = $assignmentService;
    }

    /**
     * 8 Item Belanja Operasional Sekolah resmi
     */
    public const OPERATIONAL_ITEMS = [
        'otorisasi' => [
            'key' => 'otorisasi',
            'name' => 'Otorisasi',
            'icon' => 'fa-stamp',
            'desc' => 'Belanja Otorisasi Yayasan untuk Unit Sekolah',
        ],
        'subsidi_bendahara' => [
            'key' => 'subsidi_bendahara',
            'name' => 'Subsidi Tugas Tambahan Bendahara',
            'icon' => 'fa-hand-holding-dollar',
            'desc' => 'Subsidi insentif tugas tambahan bendahara unit',
        ],
        'honor_admin' => [
            'key' => 'honor_admin',
            'name' => 'Honor Administrator',
            'icon' => 'fa-user-gear',
            'desc' => 'Honorarium administrator sistem & IT unit',
        ],
        'honor_pj_usaha' => [
            'key' => 'honor_pj_usaha',
            'name' => 'Honor Penanggungjawab Unit Usaha',
            'icon' => 'fa-store',
            'desc' => 'Honor pengelola / penanggungjawab unit usaha',
        ],
        'dana_sosial' => [
            'key' => 'dana_sosial',
            'name' => 'Dana Sosial',
            'icon' => 'fa-ribbon',
            'desc' => 'Dana sosial, keagamaan, kemanusiaan & duka',
        ],
        'iuran_internet' => [
            'key' => 'iuran_internet',
            'name' => 'Iuran Internet',
            'icon' => 'fa-wifi',
            'desc' => 'Biaya langganan jaringan internet & telekomunikasi',
        ],
        'honor_kontrak_khusus' => [
            'key' => 'honor_kontrak_khusus',
            'name' => 'Honor Tenaga Kontrak Khusus',
            'icon' => 'fa-file-signature',
            'desc' => 'Honorarium tenaga pendukung / profesional khusus',
        ],
        'iuran_bpjs' => [
            'key' => 'iuran_bpjs',
            'name' => 'Iuran BPJS',
            'icon' => 'fa-shield-heart',
            'desc' => 'Subsidi / Iuran BPJS Kesehatan & Ketenagakerjaan',
        ],
    ];

    /**
     * Tampilkan Halaman Rencana Anggaran Belanja (RAB) Yayasan
     */
    public function index(Request $request)
    {
        $academicYearId = $request->input('academic_year_id');
        $periodMode = $request->input('period_mode', 'annual'); // 'annual' (12 bulan) atau 'monthly' (1 bulan)

        $data = $this->getRabData($academicYearId, $periodMode);

        return view('yayasan.rab.index', $data);
    }

    /**
     * Simpan / Update Pengaturan RAB (Tarif SPP & 8 Item Belanja Operasional Unit)
     */
    public function store(Request $request)
    {
        $request->validate([
            'school_id' => 'required|exists:schools,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'spp_rates' => 'nullable|array',
            'spp_rates.*' => 'nullable|numeric|min:0',
            'expense_details' => 'nullable|array',
            'expense_details.*' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $schoolId = $request->input('school_id');
        $academicYearId = $request->input('academic_year_id');
        $sppRates = $request->input('spp_rates', []);
        $expenseDetails = $request->input('expense_details', []);
        $notes = $request->input('notes');

        $cleanedSpp = [];
        if (is_array($sppRates)) {
            foreach ($sppRates as $key => $rate) {
                $cleanedSpp[$key] = (float) $rate;
            }
        }

        $cleanedExpenseDetails = [];
        $totalOperationalSum = 0;
        if (is_array($expenseDetails)) {
            foreach (self::OPERATIONAL_ITEMS as $itemKey => $itemInfo) {
                $val = (float) ($expenseDetails[$itemKey] ?? 0);
                if ($val >= 0) {
                    $cleanedExpenseDetails[$itemKey] = $val;
                    $totalOperationalSum += $val;
                }
            }
        }

        SchoolContribution::updateOrCreate(
            [
                'school_id' => $schoolId,
                'academic_year_id' => $academicYearId,
            ],
            [
                'authorized_expense' => $totalOperationalSum,
                'expense_details' => $cleanedExpenseDetails,
                'spp_rates' => $cleanedSpp,
                'notes' => $notes,
            ]
        );

        return back()->with('success', 'Rencana Anggaran Belanja (RAB) unit berhasil diperbarui.');
    }

    /**
     * Export RAB Yayasan ke PDF
     */
    public function exportPdf(Request $request)
    {
        $academicYearId = $request->input('academic_year_id');
        $periodMode = $request->input('period_mode', 'annual');

        $data = $this->getRabData($academicYearId, $periodMode);

        $pdf = Pdf::loadView('yayasan.rab.pdf', $data);
        $pdf->setPaper('A4', 'landscape');

        $yearName = $data['activeYear']->year ?? 'Tahun-Aktif';
        $fileName = 'RAB-Yayasan-Pembda-' . str_replace('/', '-', $yearName) . '.pdf';

        return $pdf->stream($fileName);
    }

    /**
     * Helper untuk menghimpun data RAB seluruh unit sekolah & yayasan
     */
    private function getRabData(?int $academicYearId = null, string $periodMode = 'annual'): array
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

        $schools = School::where('is_active', true)->orderBy('name', 'asc')->get();
        $multiplier = ($periodMode === 'monthly') ? 1 : 12;

        $items = self::OPERATIONAL_ITEMS;
        $schoolRabList = [];

        $totalConsolidatedIncome = 0;
        $totalConsolidatedSalary = 0;
        $totalConsolidatedOperational = 0;
        $totalConsolidatedExpense = 0;
        $totalConsolidatedBalance = 0;
        $totalConsolidatedStudents = 0;

        // Ambil nominal SPP default dari Master PaymentType (SPP)
        $masterSppType = PaymentType::where('type_code', 'SPP')
            ->orWhere('type_name', 'LIKE', '%SPP%')
            ->orWhere('type_name', 'LIKE', '%Uang Sekolah%')
            ->first();
        $masterSppAmount = (float) ($masterSppType->amount ?? 350000);

        foreach ($schools as $school) {
            $contribution = SchoolContribution::where('school_id', $school->id)
                ->where('academic_year_id', $activeYear->id ?? 0)
                ->first();

            $savedSppRates = $contribution->spp_rates ?? [];
            $savedExpenseDetails = $contribution->expense_details ?? [];

            // 1. SISWA AKTIF & RENCANA PENDAPATAN SPP
            $studentCount = Student::where('school_id', $school->id)
                ->whereIn('status', StudentStatusHistory::ACTIVE_STATUSES)
                ->count();
            $totalConsolidatedStudents += $studentCount;

            $levelKey = strtolower($school->type ?? 'smk');
            $sppMonthlyRate = isset($savedSppRates[$levelKey]) && $savedSppRates[$levelKey] > 0
                ? (float) $savedSppRates[$levelKey]
                : $masterSppAmount;

            $rabIncomeMonthly = $studentCount * $sppMonthlyRate;
            $rabIncomePeriod = $rabIncomeMonthly * $multiplier;
            $totalConsolidatedIncome += $rabIncomePeriod;

            // 2. RENCANA BELANJA GAJI & BEBAN KERJA (HONOR + TUNJANGAN JABATAN)
            $employees = Employee::with(['activePositions', 'teacher', 'school'])
                ->where('is_active', true)
                ->where(function ($q) use ($school, $activeYear) {
                    $q->where('school_id', $school->id)
                      ->orWhereHas('activePositions', function ($posQ) use ($school, $activeYear) {
                          $posQ->where('positions.school_id', $school->id)
                               ->where('employee_positions.academic_year_id', $activeYear->id);
                      })
                      ->orWhereHas('teacher.teachingAssignments', function ($teachQ) use ($school, $activeYear) {
                          $teachQ->where('academic_year_id', $activeYear->id)
                                 ->where('is_active', true)
                                 ->whereHas('classroom', fn($cQ) => $cQ->where('school_id', $school->id));
                      });
                })
                ->get();

            $schoolSalaryMonthly = 0;
            foreach ($employees as $emp) {
                $sal = $this->assignmentService->calculateFullSalary($emp, $activeYear, $activeSemester, $school->type, $school->id);
                $schoolSalaryMonthly += ($sal['thp'] ?? 0);
            }
            $rabSalaryPeriod = $schoolSalaryMonthly * $multiplier;
            $totalConsolidatedSalary += $rabSalaryPeriod;

            // 3. RENCANA BELANJA OPERASIONAL (8 ITEMS)
            $itemisedMonthly = [];
            $schoolOperationalMonthly = 0;
            foreach ($items as $itemKey => $itemInfo) {
                $val = (float) ($savedExpenseDetails[$itemKey] ?? 0);
                $itemisedMonthly[$itemKey] = $val;
                $schoolOperationalMonthly += $val;
            }
            $rabOperationalPeriod = $schoolOperationalMonthly * $multiplier;
            $totalConsolidatedOperational += $rabOperationalPeriod;

            // TOTAL RENCANA BELANJA & SALDO RENCANA
            $rabTotalExpensePeriod = $rabSalaryPeriod + $rabOperationalPeriod;
            $totalConsolidatedExpense += $rabTotalExpensePeriod;

            $rabBalancePeriod = $rabIncomePeriod - $rabTotalExpensePeriod;
            $totalConsolidatedBalance += $rabBalancePeriod;

            $schoolRabList[] = [
                'school' => $school,
                'contribution' => $contribution,
                'student_count' => $studentCount,
                'spp_monthly_rate' => $sppMonthlyRate,
                'rab_income_monthly' => $rabIncomeMonthly,
                'rab_income_period' => $rabIncomePeriod,
                'rab_salary_monthly' => $schoolSalaryMonthly,
                'rab_salary_period' => $rabSalaryPeriod,
                'itemised_monthly' => $itemisedMonthly,
                'rab_operational_monthly' => $schoolOperationalMonthly,
                'rab_operational_period' => $rabOperationalPeriod,
                'rab_total_expense_period' => $rabTotalExpensePeriod,
                'rab_balance_period' => $rabBalancePeriod,
            ];
        }

        return [
            'academicYears' => $academicYears,
            'activeYear' => $activeYear,
            'activeSemester' => $activeSemester,
            'periodMode' => $periodMode,
            'multiplier' => $multiplier,
            'operationalItems' => $items,
            'schoolRabList' => $schoolRabList,
            'masterSppAmount' => $masterSppAmount,
            'summary' => [
                'total_students' => $totalConsolidatedStudents,
                'total_income' => $totalConsolidatedIncome,
                'total_salary' => $totalConsolidatedSalary,
                'total_operational' => $totalConsolidatedOperational,
                'total_expense' => $totalConsolidatedExpense,
                'total_balance' => $totalConsolidatedBalance,
            ]
        ];
    }
}
