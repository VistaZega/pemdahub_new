<?php

namespace App\Http\Controllers\Yayasan;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Employee;
use App\Models\School;
use App\Models\SchoolContribution;
use App\Models\Semester;
use App\Services\EmployeeAssignmentService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class FoundationExpenseController extends Controller
{
    protected EmployeeAssignmentService $assignmentService;

    public function __construct(EmployeeAssignmentService $assignmentService)
    {
        $this->assignmentService = $assignmentService;
    }

    /**
     * Master Kode Rekening Belanja Operasional Yayasan (RAPBY)
     */
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
     * Tampilan Halaman Rencana Belanja Operasional & Pegawai (Halaman 2)
     */
    public function index(Request $request)
    {
        $data = $this->getFoundationExpenseData($request);
        return view('yayasan.operational_expenses.index', $data);
    }

    /**
     * Simpan / Update Rencana Belanja Operasional Non-Gaji
     */
    public function store(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'expense_details' => 'nullable|array',
            'expense_details.*' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $academicYearId = $request->input('academic_year_id');
        $expenseDetails = $request->input('expense_details', []);
        $notes = $request->input('notes');

        $yayasanSchool = School::where('type', 'yayasan')->first()
            ?? School::firstOrCreate(
                ['type' => 'yayasan'],
                ['name' => 'Yayasan Perguruan Pembda Nias', 'is_active' => true]
            );

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

        SchoolContribution::updateOrCreate(
            [
                'school_id' => $yayasanSchool->id,
                'academic_year_id' => $academicYearId,
            ],
            [
                'authorized_expense' => $calculatedExpenseSum,
                'expense_details' => $cleanedExpenseDetails,
                'notes' => $notes,
            ]
        );

        return back()->with('success', 'Rencana Belanja Operasional Yayasan berhasil disimpan.');
    }

    /**
     * Export PDF Rencana Belanja Operasional & Pegawai Yayasan
     */
    public function exportPdf(Request $request)
    {
        $data = $this->getFoundationExpenseData($request);
        $pdf = Pdf::loadView('yayasan.operational_expenses.pdf', $data);
        return $pdf->download('Rencana_Belanja_Yayasan_' . ($data['currentYear']->year ?? 'TP') . '.pdf');
    }

    /**
     * Kalkulasi Data Rencana Belanja Pegawai & Belanja Operasional
     */
    public function getFoundationExpenseData(Request $request): array
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

        // 1. Kalkulasi Belanja Pegawai Seluruh Unit Perguruan (Sekolah + Yayasan)
        $allSchools = School::where('is_active', true)
            ->orderByRaw("CASE WHEN type = 'yayasan' THEN 2 ELSE 1 END, name ASC")
            ->get();

        $salaryBreakdown = [];
        $totalGajiPerguruanMonthly = 0;

        foreach ($allSchools as $sch) {
            $employees = Employee::where('school_id', $sch->id)->where('is_active', true)->get();
            $sumSalary = 0;
            if ($currentYear && $currentSemester) {
                foreach ($employees as $emp) {
                    $sal = $this->assignmentService->calculateFullSalary($emp, $currentYear, $currentSemester, $sch->type, $sch->id);
                    $sumSalary += (float) ($sal['thp'] ?? 0);
                }
            }
            $periodSalary = $sumSalary * $multiplier;
            $salaryBreakdown[] = [
                'school' => $sch,
                'employee_count' => $employees->count(),
                'salary_monthly' => $sumSalary,
                'salary_period' => $periodSalary,
            ];
            $totalGajiPerguruanMonthly += $sumSalary;
        }

        $totalGajiPerguruanPeriod = $totalGajiPerguruanMonthly * $multiplier;

        // 2. Kalkulasi Rencana Belanja Operasional Non-Gaji Terpusat Yayasan
        $yayasanSchool = School::where('type', 'yayasan')->first();
        $contribution = $yayasanSchool
            ? SchoolContribution::where('school_id', $yayasanSchool->id)->where('academic_year_id', $currentYear->id ?? 0)->first()
            : null;

        $savedExpenseDetails = $contribution->expense_details ?? [];
        $totalOpsMonthly = array_sum($savedExpenseDetails);
        if ($totalOpsMonthly == 0 && $contribution) {
            $totalOpsMonthly = (float) ($contribution->authorized_expense ?? 0);
        }

        $totalOpsPeriod = $totalOpsMonthly * $multiplier;

        // 3. Grand Total Rencana Belanja Perguruan (Pegawai + Operasional)
        $grandTotalBelanjaMonthly = $totalGajiPerguruanMonthly + $totalOpsMonthly;
        $grandTotalBelanjaPeriod = $grandTotalBelanjaMonthly * $multiplier;

        return [
            'currentYear' => $currentYear,
            'allYears' => $allYears,
            'periodMode' => $periodMode,
            'multiplier' => $multiplier,
            'yayasanSchool' => $yayasanSchool,
            'contribution' => $contribution,
            'salaryBreakdown' => $salaryBreakdown,
            'totalGajiPerguruanMonthly' => $totalGajiPerguruanMonthly,
            'totalGajiPerguruanPeriod' => $totalGajiPerguruanPeriod,
            'savedExpenseDetails' => $savedExpenseDetails,
            'totalOpsMonthly' => $totalOpsMonthly,
            'totalOpsPeriod' => $totalOpsPeriod,
            'grandTotalBelanjaMonthly' => $grandTotalBelanjaMonthly,
            'grandTotalBelanjaPeriod' => $grandTotalBelanjaPeriod,
            'expenseAccounts' => self::OPERATIONAL_EXPENSE_ACCOUNTS,
        ];
    }
}
