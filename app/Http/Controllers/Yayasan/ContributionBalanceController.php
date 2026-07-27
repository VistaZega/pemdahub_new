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

    public const OPERATIONAL_EXPENSE_ACCOUNTS = [
        '5.1.01' => [
            'code' => '5.1.01',
            'name' => 'Belanja Jasa Internet & Telekomunikasi',
            'icon' => 'fa-wifi',
            'category' => 'Layanan Utama',
        ],
        '5.1.02' => [
            'code' => '5.1.02',
            'name' => 'Belanja Jasa Listrik (PLN)',
            'icon' => 'fa-bolt',
            'category' => 'Layanan Utama',
        ],
        '5.1.03' => [
            'code' => '5.1.03',
            'name' => 'Belanja Jasa Air (PDAM / Sumur)',
            'icon' => 'fa-faucet-drip',
            'category' => 'Layanan Utama',
        ],
        '5.1.04' => [
            'code' => '5.1.04',
            'name' => 'Belanja Subsidi & Beasiswa Siswa/Pegawai',
            'icon' => 'fa-hand-holding-heart',
            'category' => 'Subsidi & Bantuan',
        ],
        '5.1.05' => [
            'code' => '5.1.05',
            'name' => 'Belanja Pemeliharaan Sarpras & Perbaikan',
            'icon' => 'fa-screwdriver-wrench',
            'category' => 'Pemeliharaan',
        ],
        '5.1.06' => [
            'code' => '5.1.06',
            'name' => 'Belanja Kegiatan Sosial, Keagamaan & Duka',
            'icon' => 'fa-ribbon',
            'category' => 'Sosial & Humas',
        ],
        '5.1.07' => [
            'code' => '5.1.07',
            'name' => 'Belanja Konsumsi, Makan dan Minum Rapat/Tamu',
            'icon' => 'fa-utensils',
            'category' => 'Konsumsi',
        ],
        '5.1.08' => [
            'code' => '5.1.08',
            'name' => 'Belanja Kesehatan, Obat-Obatan & P3K',
            'icon' => 'fa-notes-medical',
            'category' => 'Kesehatan',
        ],
        '5.1.09' => [
            'code' => '5.1.09',
            'name' => 'Belanja Perjalanan Dinas & Transport',
            'icon' => 'fa-car-side',
            'category' => 'Operasional',
        ],
        '5.1.10' => [
            'code' => '5.1.10',
            'name' => 'Belanja Barang, ATK & Cetak Dokumen',
            'icon' => 'fa-box-archive',
            'category' => 'Barang & Jasa',
        ],
        '5.1.11' => [
            'code' => '5.1.11',
            'name' => 'Belanja Sewa Peralatan & Kebersihan',
            'icon' => 'fa-broom',
            'category' => 'Sarana & Umum',
        ],
        '5.1.12' => [
            'code' => '5.1.12',
            'name' => 'Belanja Promosi, Iklan & Brosur Publikasi',
            'icon' => 'fa-bullhorn',
            'category' => 'Sosial & Humas',
        ],
        '5.1.13' => [
            'code' => '5.1.13',
            'name' => 'Belanja Pajak, Perizinan & Administrasi Hukum',
            'icon' => 'fa-scale-balanced',
            'category' => 'Hukum & Legal',
        ],
        '5.1.14' => [
            'code' => '5.1.14',
            'name' => 'Belanja Operasional Lain-Lain',
            'icon' => 'fa-ellipsis-h',
            'category' => 'Lain-Lain',
        ],
    ];

    /**
     * Simpan / Update Belanja Otorisasi & Tarif SPP per Level
     */
    public function store(Request $request)
    {
        $request->validate([
            'school_id' => 'required|exists:schools,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'authorized_expense' => 'nullable|numeric|min:0',
            'expense_details' => 'nullable|array',
            'expense_details.*' => 'nullable|numeric|min:0',
            'spp_rates' => 'nullable|array',
            'spp_rates.*' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $schoolId = $request->input('school_id');
        $academicYearId = $request->input('academic_year_id');
        $sppRates = $request->input('spp_rates', []);
        $expenseDetails = $request->input('expense_details', []);
        $notes = $request->input('notes');

        // Clean & cast spp_rates to float
        $cleanedSpp = [];
        if (is_array($sppRates)) {
            foreach ($sppRates as $level => $rate) {
                $cleanedSpp[$level] = (float) $rate;
            }
        }

        // Clean & cast expense_details to float and calculate sum
        $cleanedExpenseDetails = [];
        $calculatedExpenseSum = 0;
        if (is_array($expenseDetails)) {
            foreach ($expenseDetails as $code => $amount) {
                $val = (float) $amount;
                if ($val > 0) {
                    $cleanedExpenseDetails[$code] = $val;
                    $calculatedExpenseSum += $val;
                }
            }
        }

        $authorizedExpense = ($calculatedExpenseSum > 0) 
            ? $calculatedExpenseSum 
            : (float) $request->input('authorized_expense', 0);

        SchoolContribution::updateOrCreate(
            [
                'school_id' => $schoolId,
                'academic_year_id' => $academicYearId,
            ],
            [
                'authorized_expense' => $authorizedExpense,
                'expense_details' => $cleanedExpenseDetails,
                'spp_rates' => $cleanedSpp,
                'notes' => $notes,
            ]
        );

        return back()->with('success', 'Data Rincian Belanja Operasional & Tarif SPP unit berhasil diperbarui.');
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

        // Ambil unit sekolah aktif (hanya unit pendidikan/sekolah)
        $schools = School::schoolsOnly()->where('is_active', true)->orderBy('name')->get();

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

            if ($school->type === 'yayasan') {
                $savedExpenseDetails = $contribution->expense_details ?? [];
                $expenseDetailsSum = array_sum($savedExpenseDetails);
                $authorizedExpenseMonthly = $expenseDetailsSum > 0 ? $expenseDetailsSum : (float) ($contribution->authorized_expense ?? 0);
                $authorizedExpenseTotal = $authorizedExpenseMonthly * $multiplier;
            } else {
                $savedExpenseDetails = [];
                $authorizedExpenseMonthly = 0;
                $authorizedExpenseTotal = 0;
            }

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
                'expense_details' => $savedExpenseDetails,
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
            'expenseAccounts' => self::OPERATIONAL_EXPENSE_ACCOUNTS,
            'grandTotalIncome' => $grandTotalIncome,
            'grandTotalGaji' => $grandTotalGaji,
            'grandTotalOtorisasi' => $grandTotalOtorisasi,
            'grandTotalExpense' => $grandTotalExpense,
            'grandTotalSaldo' => $grandTotalSaldo,
        ];
    }
}
