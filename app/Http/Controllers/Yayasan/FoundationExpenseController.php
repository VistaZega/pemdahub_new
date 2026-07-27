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
     * Master Rekening Belanja Operasional Non-Gaji Yayasan (RAPBY)
     */
    public const OPERATIONAL_ACCOUNTS = [
        '5.1.01' => [
            'code' => '5.1.01',
            'name' => 'Belanja Jasa Internet & Telekomunikasi',
            'icon' => 'fa-wifi',
            'category' => 'Layanan Utama',
            'default_unit' => 'Bulan',
        ],
        '5.1.02' => [
            'code' => '5.1.02',
            'name' => 'Belanja Jasa Listrik (PLN)',
            'icon' => 'fa-bolt',
            'category' => 'Layanan Utama',
            'default_unit' => 'Bulan',
        ],
        '5.1.03' => [
            'code' => '5.1.03',
            'name' => 'Belanja Jasa Air (PDAM / Sumur)',
            'icon' => 'fa-faucet-drip',
            'category' => 'Layanan Utama',
            'default_unit' => 'Bulan',
        ],
        '5.1.04' => [
            'code' => '5.1.04',
            'name' => 'Belanja Subsidi & Beasiswa Siswa/Pegawai',
            'icon' => 'fa-hand-holding-heart',
            'category' => 'Subsidi & Bantuan',
            'default_unit' => 'Paket',
        ],
        '5.1.05' => [
            'code' => '5.1.05',
            'name' => 'Belanja Pemeliharaan Sarpras & Perbaikan',
            'icon' => 'fa-screwdriver-wrench',
            'category' => 'Pemeliharaan',
            'default_unit' => 'Kegiatan',
        ],
        '5.1.06' => [
            'code' => '5.1.06',
            'name' => 'Belanja Kegiatan Sosial, Keagamaan & Duka',
            'icon' => 'fa-ribbon',
            'category' => 'Sosial & Humas',
            'default_unit' => 'Kegiatan',
        ],
        '5.1.07' => [
            'code' => '5.1.07',
            'name' => 'Belanja Konsumsi, Makan dan Minum Rapat/Tamu',
            'icon' => 'fa-utensils',
            'category' => 'Konsumsi',
            'default_unit' => 'Paket',
        ],
        '5.1.08' => [
            'code' => '5.1.08',
            'name' => 'Belanja Kesehatan, Obat-Obatan & P3K',
            'icon' => 'fa-notes-medical',
            'category' => 'Kesehatan',
            'default_unit' => 'Paket',
        ],
        '5.1.09' => [
            'code' => '5.1.09',
            'name' => 'Belanja Perjalanan Dinas & Transport',
            'icon' => 'fa-car-side',
            'category' => 'Operasional',
            'default_unit' => 'Perjalanan',
        ],
        '5.1.10' => [
            'code' => '5.1.10',
            'name' => 'Belanja Barang, ATK & Cetak Dokumen',
            'icon' => 'fa-box-archive',
            'category' => 'Barang & Jasa',
            'default_unit' => 'Paket',
        ],
        '5.1.11' => [
            'code' => '5.1.11',
            'name' => 'Belanja Sewa Peralatan & Kebersihan',
            'icon' => 'fa-broom',
            'category' => 'Sarana & Umum',
            'default_unit' => 'Bulan',
        ],
        '5.1.12' => [
            'code' => '5.1.12',
            'name' => 'Belanja Promosi, Iklan & Brosur Publikasi',
            'icon' => 'fa-bullhorn',
            'category' => 'Sosial & Humas',
            'default_unit' => 'Kegiatan',
        ],
        '5.1.13' => [
            'code' => '5.1.13',
            'name' => 'Belanja Pajak, Perizinan & Administrasi Hukum',
            'icon' => 'fa-scale-balanced',
            'category' => 'Hukum & Legal',
            'default_unit' => 'Tahun',
        ],
        '5.1.14' => [
            'code' => '5.1.14',
            'name' => 'Belanja Operasional Lain-Lain',
            'icon' => 'fa-ellipsis-h',
            'category' => 'Lain-Lain',
            'default_unit' => 'Paket',
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
     * Simpan / Update Rencana Belanja Operasional (Jumlah, Satuan, Tarif)
     */
    public function store(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'expense_details' => 'nullable|array',
            'notes' => 'nullable|string|max:500',
        ]);

        $academicYearId = $request->input('academic_year_id');
        $expenseDetailsRaw = $request->input('expense_details', []);
        $notes = $request->input('notes');

        $yayasanSchool = School::where('type', 'yayasan')->first()
            ?? School::firstOrCreate(
                ['type' => 'yayasan'],
                ['name' => 'Yayasan Perguruan Pembda Nias', 'is_active' => true]
            );

        $cleanedExpenseDetails = [];
        $calculatedExpenseSum = 0;

        if (is_array($expenseDetailsRaw)) {
            foreach ($expenseDetailsRaw as $code => $itemData) {
                // Abaikan sub-rekening Belanja Pegawai (5.1.00.*) karena otomatis dari Penugasan/Payroll
                if (str_starts_with($code, '5.1.00')) {
                    continue;
                }

                if (is_array($itemData)) {
                    $vol = (float) ($itemData['volume'] ?? 1);
                    $unit = trim($itemData['unit'] ?? 'Paket');
                    $tariff = (float) ($itemData['tariff'] ?? 0);
                    $amount = $vol * $tariff;

                    if ($amount > 0 || $tariff > 0) {
                        $cleanedExpenseDetails[$code] = [
                            'volume' => $vol,
                            'unit' => $unit,
                            'tariff' => $tariff,
                            'amount' => $amount,
                        ];
                        $calculatedExpenseSum += $amount;
                    }
                } elseif (is_numeric($itemData) && (float)$itemData > 0) {
                    $val = (float)$itemData;
                    $cleanedExpenseDetails[$code] = [
                        'volume' => 1,
                        'unit' => 'Paket',
                        'tariff' => $val,
                        'amount' => $val,
                    ];
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

        return back()->with('success', 'Rencana Belanja Yayasan berhasil disimpan.');
    }

    /**
     * Export PDF Rencana Belanja Yayasan
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

        // 1. Sub-Rekening Belanja Pegawai Otomatis per Unit (5.1.00.01, 5.1.00.02, ...)
        $allSchools = School::where('is_active', true)
            ->orderByRaw("CASE WHEN type = 'yayasan' THEN 2 ELSE 1 END, name ASC")
            ->get();

        $salarySubAccounts = [];
        $totalGajiPerguruanMonthly = 0;
        $totalPegawaiCount = 0;

        foreach ($allSchools as $idx => $sch) {
            $employees = Employee::where('school_id', $sch->id)->where('is_active', true)->get();
            $empCount = $employees->count();
            $totalPegawaiCount += $empCount;
            $sumSalary = 0;
            if ($currentYear && $currentSemester) {
                foreach ($employees as $emp) {
                    $sal = $this->assignmentService->calculateFullSalary($emp, $currentYear, $currentSemester, $sch->type, $sch->id);
                    $sumSalary += (float) ($sal['thp'] ?? 0);
                }
            }
            $totalGajiPerguruanMonthly += $sumSalary;

            $subCode = '5.1.00.' . sprintf('%02d', $idx + 1);
            $salarySubAccounts[$subCode] = [
                'code' => $subCode,
                'name' => 'Belanja Gaji & Tunjangan Pegawai ' . $sch->name,
                'icon' => $sch->type === 'yayasan' ? 'fa-user-tie' : 'fa-users-gear',
                'category' => 'Belanja Pegawai',
                'volume' => $empCount,
                'unit' => 'Orang/Bln',
                'tariff' => $empCount > 0 ? ($sumSalary / $empCount) : 0,
                'amount' => $sumSalary,
                'is_automatic' => true,
            ];
        }

        $totalGajiPerguruanPeriod = $totalGajiPerguruanMonthly * $multiplier;

        // 2. Sub-Rekening Belanja Operasional Non-Gaji (5.1.01 s/d 5.1.14)
        $yayasanSchool = School::where('type', 'yayasan')->first();
        $contribution = $yayasanSchool
            ? SchoolContribution::where('school_id', $yayasanSchool->id)->where('academic_year_id', $currentYear->id ?? 0)->first()
            : null;

        $rawSavedDetails = $contribution->expense_details ?? [];

        $parsedExpenseDetails = [];
        $totalOpsMonthly = 0;

        // Masukkan Belanja Pegawai Sub-Rekening (5.1.00.01 dst)
        foreach ($salarySubAccounts as $subCode => $salItem) {
            $parsedExpenseDetails[$subCode] = $salItem;
        }

        // Masukkan Belanja Operasional (5.1.01 s/d 5.1.14)
        foreach (self::OPERATIONAL_ACCOUNTS as $code => $acc) {
            $savedItem = $rawSavedDetails[$code] ?? null;
            if (is_array($savedItem)) {
                $vol = (float) ($savedItem['volume'] ?? 1);
                $unit = $savedItem['unit'] ?? ($acc['default_unit'] ?? 'Paket');
                $tariff = (float) ($savedItem['tariff'] ?? 0);
                $amt = (float) ($savedItem['amount'] ?? ($vol * $tariff));
            } elseif (is_numeric($savedItem)) {
                $vol = 1;
                $unit = $acc['default_unit'] ?? 'Paket';
                $tariff = (float) $savedItem;
                $amt = (float) $savedItem;
            } else {
                $vol = 1;
                $unit = $acc['default_unit'] ?? 'Paket';
                $tariff = 0;
                $amt = 0;
            }

            $parsedExpenseDetails[$code] = [
                'code' => $code,
                'name' => $acc['name'],
                'icon' => $acc['icon'],
                'category' => $acc['category'],
                'volume' => $vol,
                'unit' => $unit,
                'tariff' => $tariff,
                'amount' => $amt,
                'is_automatic' => false,
            ];

            $totalOpsMonthly += $amt;
        }

        $totalOpsPeriod = $totalOpsMonthly * $multiplier;
        $grandTotalBelanjaMonthly = $totalGajiPerguruanMonthly + $totalOpsMonthly;
        $grandTotalBelanjaPeriod = $grandTotalBelanjaMonthly * $multiplier;

        return [
            'currentYear' => $currentYear,
            'allYears' => $allYears,
            'periodMode' => $periodMode,
            'multiplier' => $multiplier,
            'yayasanSchool' => $yayasanSchool,
            'contribution' => $contribution,
            'totalPegawaiCount' => $totalPegawaiCount,
            'totalGajiPerguruanMonthly' => $totalGajiPerguruanMonthly,
            'totalGajiPerguruanPeriod' => $totalGajiPerguruanPeriod,
            'salarySubAccounts' => $salarySubAccounts,
            'parsedExpenseDetails' => $parsedExpenseDetails,
            'totalOpsMonthly' => $totalOpsMonthly,
            'totalOpsPeriod' => $totalOpsPeriod,
            'grandTotalBelanjaMonthly' => $grandTotalBelanjaMonthly,
            'grandTotalBelanjaPeriod' => $grandTotalBelanjaPeriod,
        ];
    }
}
