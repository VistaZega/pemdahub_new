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

    public const OPERATIONAL_EXPENSE_ACCOUNTS = self::OPERATIONAL_ACCOUNTS;

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
     * Kalkulasi Data Rencana Belanja Pegawai & Belanja Operasional Hierarkis
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

        // 1. Hirarki Belanja Pegawai Otomatis per Unit Pendidikan & Yayasan
        $allSchools = School::where('is_active', true)
            ->orderByRaw("CASE WHEN type = 'yayasan' THEN 2 ELSE 1 END, name ASC")
            ->get();

        $hierarchicalSalaryData = [];
        $salarySubAccounts = [];
        $totalGajiPerguruanMonthly = 0;
        $totalPegawaiCount = 0;
        $subAccountIndex = 1;

        foreach ($allSchools as $sch) {
            $employees = Employee::where('school_id', $sch->id)->where('is_active', true)->get();
            $empCount = $employees->count();
            $totalPegawaiCount += $empCount;

            $schGajiPokokSum = 0;
            $schHonorMengajarSum = 0;
            $schJamHonorSum = 0;
            $schHonorRateAvg = 0;
            $schTunjanganJabatanSum = 0;
            $schTunjanganKeluargaSum = 0;
            $schBpjsSum = 0;
            $empGtyPtyCount = 0;

            if ($currentYear && $currentSemester) {
                foreach ($employees as $emp) {
                    $salData = $this->assignmentService->calculateFullSalary(
                        $emp,
                        $currentYear,
                        $currentSemester,
                        $sch->type,
                        $sch->id
                    );

                    $schGajiPokokSum += (float) ($salData['gaji_pokok'] ?? 0);
                    
                    // Honor mengajar: keys langsung di root level (BUKAN nested)
                    $honorTotal = (float) ($salData['honor_mengajar'] ?? 0);
                    $jamMengajar = (int) ($salData['jam_mengajar'] ?? 0);
                    $jamHonor = (int) ($salData['jam_honor'] ?? 0);
                    $honorRate = (float) ($salData['honor_per_jam'] ?? 0);

                    $schHonorMengajarSum += $honorTotal;
                    $schJamHonorSum += $jamHonor;
                    if ($honorRate > 0) {
                        $schHonorRateAvg = $honorRate;
                    }

                    $schTunjanganJabatanSum += (float) ($salData['tunjangan_jabatan'] ?? 0);

                    // Tunjangan keluarga: keys langsung di root level (BUKAN nested)
                    $tKeluarga = (float) ($salData['tunjangan_keluarga'] ?? 0);
                    $tAnak = (float) ($salData['tunjangan_anak'] ?? 0);
                    $tBeras = (float) ($salData['tunjangan_beras'] ?? 0);
                    $schTunjanganKeluargaSum += ($tKeluarga + $tAnak + $tBeras);

                    // BPJS potongan
                    $schBpjsSum += (float) ($salData['potongan_bpjs_kesehatan'] ?? 0);
                    $schBpjsSum += (float) ($salData['potongan_bpjs_ketenagakerjaan'] ?? 0);

                    // Hitung pegawai GTY/PTY
                    $statusLower = strtolower($emp->employment_status ?? '');
                    if (in_array($statusLower, ['yayasan', 'gty', 'pty'])) {
                        $empGtyPtyCount++;
                    }
                }
            }

            $unitTotalSalary = $schGajiPokokSum + $schHonorMengajarSum + $schTunjanganJabatanSum + $schTunjanganKeluargaSum;
            $totalGajiPerguruanMonthly += $unitTotalSalary;

            $schoolItems = [];

            // Urutan sesuai permintaan pengguna — SELALU tampilkan 4 komponen per unit:
            // 1. Belanja Gaji Pokok & PTY/GTY
            $code = '5.1.00.' . sprintf('%02d', $subAccountIndex++);
            $item = [
                'code' => $code,
                'name' => 'Belanja Gaji Pokok & Pegawai Tetap Yayasan (PTY/GTY)',
                'icon' => 'fa-money-bill-wave',
                'category' => 'Belanja Pegawai',
                'volume' => $empGtyPtyCount > 0 ? $empGtyPtyCount : $empCount,
                'unit' => 'Orang/Bln',
                'tariff' => ($empGtyPtyCount > 0 ? ($empGtyPtyCount > 0 ? ($schGajiPokokSum / $empGtyPtyCount) : 0) : ($empCount > 0 ? ($schGajiPokokSum / $empCount) : 0)),
                'amount' => $schGajiPokokSum,
                'is_automatic' => true,
            ];
            $schoolItems[] = $item;
            $salarySubAccounts[$code] = $item;

            // 2. Belanja Tunjangan Jabatan
            $code = '5.1.00.' . sprintf('%02d', $subAccountIndex++);
            $item = [
                'code' => $code,
                'name' => 'Belanja Tunjangan Jabatan',
                'icon' => 'fa-award',
                'category' => 'Belanja Pegawai',
                'volume' => 1,
                'unit' => 'Paket',
                'tariff' => $schTunjanganJabatanSum,
                'amount' => $schTunjanganJabatanSum,
                'is_automatic' => true,
            ];
            $schoolItems[] = $item;
            $salarySubAccounts[$code] = $item;

            // 3. Belanja Jasa Pendidikan (Honor Jam Mengajar / Les)
            $code = '5.1.00.' . sprintf('%02d', $subAccountIndex++);
            $item = [
                'code' => $code,
                'name' => 'Belanja Jasa Pendidikan (Honor Mengajar/Les)',
                'icon' => 'fa-chalkboard-user',
                'category' => 'Belanja Pegawai',
                'volume' => $schJamHonorSum > 0 ? $schJamHonorSum : ($empCount > 0 ? $empCount : 1),
                'unit' => 'Jam/Les',
                'tariff' => $schHonorRateAvg > 0 ? $schHonorRateAvg : ($schJamHonorSum > 0 ? ($schHonorMengajarSum / $schJamHonorSum) : 0),
                'amount' => $schHonorMengajarSum,
                'is_automatic' => true,
            ];
            $schoolItems[] = $item;
            $salarySubAccounts[$code] = $item;

            // 4. Belanja Tunjangan Keluarga PTY/GTY
            $code = '5.1.00.' . sprintf('%02d', $subAccountIndex++);
            $item = [
                'code' => $code,
                'name' => 'Belanja Tunjangan Keluarga, Anak & Beras PTY/GTY',
                'icon' => 'fa-people-roof',
                'category' => 'Belanja Pegawai',
                'volume' => 1,
                'unit' => 'Paket',
                'tariff' => $schTunjanganKeluargaSum,
                'amount' => $schTunjanganKeluargaSum,
                'is_automatic' => true,
            ];
            $schoolItems[] = $item;
            $salarySubAccounts[$code] = $item;

            $hierarchicalSalaryData[] = [
                'school' => $sch,
                'school_name' => $sch->name,
                'school_type' => $sch->type,
                'employee_count' => $empCount,
                'total_monthly' => $unitTotalSalary,
                'total_period' => $unitTotalSalary * $multiplier,
                'items' => $schoolItems,
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

        // Masukkan Rincian Belanja Pegawai (5.1.00.01 dst)
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
            'hierarchicalSalaryData' => $hierarchicalSalaryData,
            'salarySubAccounts' => $salarySubAccounts,
            'parsedExpenseDetails' => $parsedExpenseDetails,
            'totalOpsMonthly' => $totalOpsMonthly,
            'totalOpsPeriod' => $totalOpsPeriod,
            'grandTotalBelanjaMonthly' => $grandTotalBelanjaMonthly,
            'grandTotalBelanjaPeriod' => $grandTotalBelanjaPeriod,
        ];
    }
}
