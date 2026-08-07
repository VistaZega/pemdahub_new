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
     * Master Rekening Belanja Operasional Rutin Yayasan & Perguruan (RAPBY)
     */
    public const OPERATIONAL_ACCOUNTS = [
        '5.1.01' => [
            'code' => '5.1.01',
            'name' => 'Subsidi Keuangan',
            'icon' => 'fa-hand-holding-dollar',
            'category' => 'Subsidi & Bantuan',
            'default_unit' => 'Bulan',
        ],
        '5.1.02' => [
            'code' => '5.1.02',
            'name' => 'Otorisasi',
            'icon' => 'fa-shield-halved',
            'category' => 'Otorisasi & Kebijakan',
            'default_unit' => 'Bulan',
        ],
        '5.1.03' => [
            'code' => '5.1.03',
            'name' => 'Operasional',
            'icon' => 'fa-boxes-packing',
            'category' => 'Operasional Utama',
            'default_unit' => 'Bulan',
        ],
        '5.1.04' => [
            'code' => '5.1.04',
            'name' => 'Tunjangan Bendahara Sekolah',
            'icon' => 'fa-wallet',
            'category' => 'Tunjangan Operasional',
            'default_unit' => 'Bulan',
        ],
        '5.1.05' => [
            'code' => '5.1.05',
            'name' => 'Operator PembdaHUB',
            'icon' => 'fa-laptop-code',
            'category' => 'Insentif Operator & Sistem',
            'default_unit' => 'Bulan',
        ],
        '5.1.06' => [
            'code' => '5.1.06',
            'name' => 'Belanja Jasa Internet & Telekomunikasi',
            'icon' => 'fa-wifi',
            'category' => 'Layanan Utama',
            'default_unit' => 'Bulan',
        ],
        '5.1.07' => [
            'code' => '5.1.07',
            'name' => 'Belanja Jasa Listrik (PLN)',
            'icon' => 'fa-bolt',
            'category' => 'Layanan Utama',
            'default_unit' => 'Bulan',
        ],
        '5.1.08' => [
            'code' => '5.1.08',
            'name' => 'Belanja Jasa Air (PDAM / Sumur)',
            'icon' => 'fa-faucet-drip',
            'category' => 'Layanan Utama',
            'default_unit' => 'Bulan',
        ],
        '5.1.09' => [
            'code' => '5.1.09',
            'name' => 'Belanja Pemeliharaan Sarpras & Perbaikan',
            'icon' => 'fa-screwdriver-wrench',
            'category' => 'Pemeliharaan',
            'default_unit' => 'Kegiatan',
        ],
        '5.1.10' => [
            'code' => '5.1.10',
            'name' => 'Belanja Barang, ATK & Cetak Dokumen',
            'icon' => 'fa-box-archive',
            'category' => 'Barang & Jasa',
            'default_unit' => 'Paket',
        ],
    ];

    public const OPERATIONAL_EXPENSE_ACCOUNTS = self::OPERATIONAL_ACCOUNTS;

    /**
     * Tampilan Halaman Rencana Belanja Operasional & Pegawai Per Unit Sekolah
     */
    public function index(Request $request)
    {
        $data = $this->getFoundationExpenseData($request);
        return view('yayasan.operational_expenses.index', $data);
    }

    /**
     * Simpan / Update Rencana Belanja Operasional dikelompokkan Per Unit Sekolah
     */
    public function store(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'expense_details' => 'nullable|array',
            'notes' => 'nullable|string|max:500',
        ]);

        $academicYearId = $request->input('academic_year_id');
        $expenseDetailsPerSchool = $request->input('expense_details', []);
        $notes = $request->input('notes');

        if (is_array($expenseDetailsPerSchool)) {
            foreach ($expenseDetailsPerSchool as $schoolId => $itemsRaw) {
                $cleanedDetails = [];
                $calculatedSum = 0;

                if (is_array($itemsRaw)) {
                    foreach ($itemsRaw as $code => $itemData) {
                        if (str_starts_with($code, '5.1.00')) {
                            continue;
                        }

                        if (is_array($itemData)) {
                            $vol = (float) ($itemData['volume'] ?? 1);
                            $unit = trim($itemData['unit'] ?? 'Bulan');
                            $tariff = (float) ($itemData['tariff'] ?? 0);
                            $amount = $vol * $tariff;

                            if ($amount > 0 || $tariff > 0) {
                                $cleanedDetails[$code] = [
                                    'volume' => $vol,
                                    'unit' => $unit,
                                    'tariff' => $tariff,
                                    'amount' => $amount,
                                ];
                                $calculatedSum += $amount;
                            }
                        }
                    }
                }

                SchoolContribution::updateOrCreate(
                    [
                        'school_id' => $schoolId,
                        'academic_year_id' => $academicYearId,
                    ],
                    [
                        'authorized_expense' => $calculatedSum,
                        'expense_details' => $cleanedDetails,
                        'notes' => $notes,
                    ]
                );
            }
        }

        return back()->with('success', 'Rencana Belanja Operasional Per Unit Sekolah berhasil disimpan!');
    }

    /**
     * Export PDF Rencana Belanja Yayasan per Unit Sekolah
     */
    public function exportPdf(Request $request)
    {
        $data = $this->getFoundationExpenseData($request);
        $pdf = Pdf::loadView('yayasan.operational_expenses.pdf', $data);
        return $pdf->download('Rencana_Belanja_Per_Unit_Sekolah_' . ($data['currentYear']->year ?? 'TP') . '.pdf');
    }

    /**
     * Kalkulasi Data Rencana Belanja Pegawai & Belanja Operasional dikelompokkan per Unit Sekolah
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

        // 1. Ambil Seluruh Unit Sekolah (SMA, SMK, SMP, Pusat Yayasan)
        $allSchools = School::where('is_active', true)
            ->orderByRaw("CASE WHEN type = 'yayasan' THEN 2 ELSE 1 END, name ASC")
            ->get();

        $schoolExpenseData = [];
        $totalGajiPerguruanMonthly = 0;
        $totalOpsPerguruanMonthly = 0;
        $totalPegawaiCount = 0;
        $subAccountIndex = 1;

        foreach ($allSchools as $sch) {
            // A. Gaji Pegawai Unit Ini
            $employees = Employee::where('school_id', $sch->id)->where('is_active', true)->get();
            $empCount = $employees->count();
            $totalPegawaiCount += $empCount;

            $sumSalaryMonthly = 0;
            if ($currentYear && $currentSemester) {
                foreach ($employees as $emp) {
                    $salData = $this->assignmentService->calculateFullSalary(
                        $emp,
                        $currentYear,
                        $currentSemester,
                        $sch->type,
                        $sch->id
                    );
                    $sumSalaryMonthly += (float) ($salData['gross_pay'] ?? 0);
                }
            }

            $totalGajiPerguruanMonthly += $sumSalaryMonthly;

            // B. Rencana Belanja Operasional Non-Gaji Unit Ini
            $contribution = SchoolContribution::where('school_id', $sch->id)
                ->where('academic_year_id', $currentYear->id ?? 0)
                ->first();

            $rawSavedDetails = $contribution->expense_details ?? [];
            $parsedOpsDetails = [];
            $sumOpsMonthly = 0;

            foreach (self::OPERATIONAL_ACCOUNTS as $code => $acc) {
                $savedItem = $rawSavedDetails[$code] ?? null;
                if (is_array($savedItem)) {
                    $vol = (float) ($savedItem['volume'] ?? 1);
                    $unit = $savedItem['unit'] ?? ($acc['default_unit'] ?? 'Bulan');
                    $tariff = (float) ($savedItem['tariff'] ?? 0);
                    $amt = (float) ($savedItem['amount'] ?? ($vol * $tariff));
                } elseif (is_numeric($savedItem)) {
                    $vol = 1;
                    $unit = $acc['default_unit'] ?? 'Bulan';
                    $tariff = (float) $savedItem;
                    $amt = (float) $savedItem;
                } else {
                    $vol = 1;
                    $unit = $acc['default_unit'] ?? 'Bulan';
                    $tariff = 0;
                    $amt = 0;
                }

                $parsedOpsDetails[$code] = [
                    'code' => $code,
                    'name' => $acc['name'],
                    'icon' => $acc['icon'],
                    'category' => $acc['category'],
                    'volume' => $vol,
                    'unit' => $unit,
                    'tariff' => $tariff,
                    'amount' => $amt,
                ];

                $sumOpsMonthly += $amt;
            }

            $totalOpsPerguruanMonthly += $sumOpsMonthly;

            $salarySubCode = '5.1.00.' . sprintf('%02d', $subAccountIndex++);
            $salaryItem = [
                'code' => $salarySubCode,
                'name' => 'Belanja Gaji & Tunjangan Pegawai ' . $sch->name,
                'icon' => $sch->type === 'yayasan' ? 'fa-building' : 'fa-school',
                'category' => 'Belanja Pegawai',
                'volume' => $empCount,
                'unit' => 'Orang/Bln',
                'tariff' => $empCount > 0 ? round($sumSalaryMonthly / $empCount) : 0,
                'amount' => $sumSalaryMonthly,
            ];

            $grandMonthly = $sumSalaryMonthly + $sumOpsMonthly;

            $schoolExpenseData[$sch->id] = [
                'school' => $sch,
                'school_id' => $sch->id,
                'school_name' => $sch->name,
                'school_type' => $sch->type,
                'employee_count' => $empCount,
                'salary_item' => $salaryItem,
                'total_salary_monthly' => $sumSalaryMonthly,
                'total_salary_period' => $sumSalaryMonthly * $multiplier,
                'ops_details' => $parsedOpsDetails,
                'total_ops_monthly' => $sumOpsMonthly,
                'total_ops_period' => $sumOpsMonthly * $multiplier,
                'grand_total_monthly' => $grandMonthly,
                'grand_total_period' => $grandMonthly * $multiplier,
                'contribution' => $contribution,
            ];
        }

        $totalGajiPerguruanPeriod = $totalGajiPerguruanMonthly * $multiplier;
        $totalOpsPerguruanPeriod = $totalOpsPerguruanMonthly * $multiplier;
        $grandTotalBelanjaMonthly = $totalGajiPerguruanMonthly + $totalOpsPerguruanMonthly;
        $grandTotalBelanjaPeriod = $grandTotalBelanjaMonthly * $multiplier;

        return [
            'currentYear' => $currentYear,
            'allYears' => $allYears,
            'periodMode' => $periodMode,
            'multiplier' => $multiplier,
            'allSchools' => $allSchools,
            'schoolExpenseData' => $schoolExpenseData,
            'totalPegawaiCount' => $totalPegawaiCount,
            'totalGajiPerguruanMonthly' => $totalGajiPerguruanMonthly,
            'totalGajiPerguruanPeriod' => $totalGajiPerguruanPeriod,
            'totalOpsPerguruanMonthly' => $totalOpsPerguruanMonthly,
            'totalOpsPerguruanPeriod' => $totalOpsPerguruanPeriod,
            'grandTotalBelanjaMonthly' => $grandTotalBelanjaMonthly,
            'grandTotalBelanjaPeriod' => $grandTotalBelanjaPeriod,
        ];
    }
}
