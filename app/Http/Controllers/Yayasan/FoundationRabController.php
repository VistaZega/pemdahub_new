<?php

namespace App\Http\Controllers\Yayasan;

use App\Http\Controllers\Controller;
use App\Models\StudentStatusHistory;
use App\Models\StudentBill;
use App\Models\School;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\Classroom;
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

    public function index(Request $request)
    {
        $data = $this->getRabData($request);
        return view('yayasan.rab.index', $data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'school_id' => 'required|exists:schools,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'spp_rates' => 'nullable|array',
            'expense_details' => 'nullable|array',
            'notes' => 'nullable|string|max:500',
        ]);

        $schoolId = $request->input('school_id');
        $academicYearId = $request->input('academic_year_id');
        $sppRates = $request->input('spp_rates', []);
        $expenseDetailsRaw = $request->input('expense_details', []);
        $notes = $request->input('notes');

        $cleanedSpp = [];
        if (is_array($sppRates)) {
            foreach ($sppRates as $key => $rate) {
                if (is_numeric($rate) && $rate >= 0) {
                    $cleanedSpp[$key] = (float) $rate;
                }
            }
        }

        $cleanedExpenseDetails = [];
        $totalOperationalSum = 0;

        if (is_array($expenseDetailsRaw)) {
            foreach ($expenseDetailsRaw as $code => $itemData) {
                if (is_array($itemData)) {
                    $vol = (float) ($itemData['volume'] ?? 1);
                    $unit = trim($itemData['unit'] ?? 'Bulan');
                    $tariff = (float) ($itemData['tariff'] ?? 0);
                    $amount = $vol * $tariff;

                    if ($amount > 0 || $tariff > 0) {
                        $cleanedExpenseDetails[$code] = [
                            'volume' => $vol,
                            'unit' => $unit,
                            'tariff' => $tariff,
                            'amount' => $amount,
                        ];
                        $totalOperationalSum += $amount;
                    }
                } elseif (is_numeric($itemData)) {
                    $vol = 1;
                    $unit = self::OPERATIONAL_ACCOUNTS[$code]['default_unit'] ?? 'Bulan';
                    $tariff = (float) $itemData;
                    $amount = $tariff;
                    if ($amount > 0) {
                        $cleanedExpenseDetails[$code] = [
                            'volume' => $vol,
                            'unit' => $unit,
                            'tariff' => $tariff,
                            'amount' => $amount,
                        ];
                        $totalOperationalSum += $amount;
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
                'authorized_expense' => $totalOperationalSum,
                'expense_details' => $cleanedExpenseDetails,
                'spp_rates' => $cleanedSpp,
                'notes' => $notes,
            ]
        );

        return back()->with('success', 'Rencana Anggaran Belanja (RAB) unit berhasil diperbarui.');
    }

    public function exportPdf(Request $request)
    {
        $data = $this->getRabData($request);
        $pdf = Pdf::loadView('yayasan.rab.pdf', $data);
        $pdf->setPaper('A4', 'landscape');
        
        $yearName = $data['currentYear']->year ?? 'Tahun-Aktif';
        $fileName = 'RAB-Yayasan-Pembda-' . str_replace('/', '-', $yearName) . '.pdf';

        return $pdf->stream($fileName);
    }

    private function getRabData(Request $request): array
    {
        $allYears = AcademicYear::orderBy('year', 'desc')->get();
        $selectedYearId = $request->query('academic_year_id');

        $currentYear = $selectedYearId 
            ? AcademicYear::find($selectedYearId) 
            : AcademicYear::where('is_active', true)->first();

        if (!$currentYear && $allYears->count() > 0) {
            $currentYear = $allYears->first();
        }

        $currentSemester = Semester::where('academic_year_id', $currentYear->id ?? 0)
            ->where('is_active', true)
            ->first()
            ?? Semester::where('academic_year_id', $currentYear->id ?? 0)->first()
            ?? Semester::first();

        $periodMode = $request->query('period_mode', 'annual');
        $multiplier = ($periodMode === 'monthly') ? 1 : 12;

        // A. PENDAPATAN SPP (from schoolsOnly)
        $learningSchools = School::schoolsOnly()->where('is_active', true)->get();
        $incomeData = [];
        $totalConsolidatedIncome = 0;

        foreach ($learningSchools as $school) {
            $contribution = SchoolContribution::where('school_id', $school->id)
                ->where('academic_year_id', $currentYear->id ?? 0)
                ->first();

            $masterSppType = PaymentType::where('school_id', $school->id)
                ->where(function ($q) {
                    $q->where('type_code', 'SPP')
                      ->orWhere('type_name', 'LIKE', '%SPP%')
                      ->orWhere('type_name', 'LIKE', '%Uang Sekolah%');
                })
                ->where('is_active', true)
                ->first();
                
            $defaultSppRate = (float) ($masterSppType->yayasan_share_amount ?? $masterSppType->amount ?? 0);
            
            $gradeLevels = $school->getGradeLevels();
            
            $levelsData = [];
            $schoolTotalIncomeMonthly = 0;
            $schoolTotalStudents = 0;
            
            foreach ($gradeLevels as $level) {
                // Hanya mengambil data siswa yang diinput, aktif, dan sudah punya kelas pada tahun pelajaran ini
                $classroomIds = Classroom::where('school_id', $school->id)
                    ->where('grade_level', $level)
                    ->pluck('id');
                    
                $studentIds = StudentClass::whereIn('classroom_id', $classroomIds)
                    ->where('academic_year_id', $currentYear->id ?? 0)
                    ->whereHas('student', function ($q) {
                        $q->whereIn('status', StudentStatusHistory::ACTIVE_STATUSES);
                    })
                    ->distinct('student_id')
                    ->pluck('student_id')
                    ->toArray();
                    
                $studentCount = count($studentIds);
                
                // Tarif SPP diambil dari nominal tagihan SPP resmi yang dibuat oleh Bendahara (Bilangan Bulat)
                $sppMonthlyRate = round($defaultSppRate);
                $sppSource = $masterSppType ? 'Master SPP Bendahara' : 'Belum Set';

                if (!empty($studentIds) && $masterSppType) {
                    $mostCommonBill = StudentBill::whereIn('student_id', $studentIds)
                        ->where('academic_year_id', $currentYear->id ?? 0)
                        ->where('payment_type_id', $masterSppType->id)
                        ->selectRaw('amount, COUNT(*) as cnt')
                        ->groupBy('amount')
                        ->orderBy('cnt', 'desc')
                        ->value('amount');

                    if ($mostCommonBill && $mostCommonBill > 0) {
                        $sppMonthlyRate = round((float) $mostCommonBill);
                        $sppSource = 'Tagihan SPP Bendahara';
                    }
                }

                $incomeMonthly = round($studentCount * $sppMonthlyRate);
                
                $levelsData[] = [
                    'level' => $level,
                    'student_count' => $studentCount,
                    'spp_monthly_rate' => $sppMonthlyRate,
                    'income_monthly' => $incomeMonthly,
                    'income_period' => $incomeMonthly * $multiplier,
                    'spp_source' => $sppSource,
                ];
                
                $schoolTotalIncomeMonthly += $incomeMonthly;
                $schoolTotalStudents += $studentCount;
            }
            
            $schoolTotalIncomePeriod = $schoolTotalIncomeMonthly * $multiplier;
            $totalConsolidatedIncome += $schoolTotalIncomePeriod;
            
            $incomeData[$school->id] = [
                'school' => $school,
                'levels' => $levelsData,
                'total_students' => $schoolTotalStudents,
                'total_income_monthly' => $schoolTotalIncomeMonthly,
                'total_income_period' => $schoolTotalIncomePeriod,
                'contribution' => $contribution,
            ];
        }

        // B & C. BELANJA PEGAWAI & OPERASIONAL (All schools including yayasan)
        $allSchools = School::where('is_active', true)
            ->orderByRaw("CASE WHEN type = 'yayasan' THEN 2 ELSE 1 END, name ASC")
            ->get();
            
        $expenseData = [];
        $totalConsolidatedSalary = 0;
        $totalConsolidatedOperational = 0;
        $totalConsolidatedExpense = 0;
        
        $subAccountIndex = 1;

        foreach ($allSchools as $sch) {
            // B. Gaji Pegawai
            $employees = Employee::where('school_id', $sch->id)->where('is_active', true)->get();
            $empCount = $employees->count();
            
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
            
            $totalSalaryPeriod = $sumSalaryMonthly * $multiplier;
            $totalConsolidatedSalary += $totalSalaryPeriod;
            
            $salarySubCode = '5.1.00.' . sprintf('%02d', $subAccountIndex++);
            $salaryItem = [
                'code' => $salarySubCode,
                'name' => 'Belanja Gaji & Tunjangan Pegawai ' . $sch->name,
                'icon' => $sch->type === 'yayasan' ? 'fa-building' : 'fa-school',
                'category' => 'Belanja Pegawai',
                'volume' => $empCount,
                'unit' => 'Orang/Bln',
                'tariff' => $empCount > 0 ? round($sumSalaryMonthly / max(1, $empCount)) : 0,
                'amount' => $sumSalaryMonthly,
            ];

            // C. Operasional
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
            
            $totalOpsPeriod = $sumOpsMonthly * $multiplier;
            $totalConsolidatedOperational += $totalOpsPeriod;
            
            $schoolGrandMonthly = $sumSalaryMonthly + $sumOpsMonthly;
            $schoolGrandPeriod = $schoolGrandMonthly * $multiplier;
            $totalConsolidatedExpense += $schoolGrandPeriod;
            
            $expenseData[$sch->id] = [
                'school' => $sch,
                'employee_count' => $empCount,
                'salary_item' => $salaryItem,
                'total_salary_monthly' => $sumSalaryMonthly,
                'total_salary_period' => $totalSalaryPeriod,
                'ops_details' => $parsedOpsDetails,
                'total_ops_monthly' => $sumOpsMonthly,
                'total_ops_period' => $totalOpsPeriod,
                'grand_total_monthly' => $schoolGrandMonthly,
                'grand_total_period' => $schoolGrandPeriod,
                'contribution' => $contribution,
            ];
        }
        
        $totalConsolidatedBalance = $totalConsolidatedIncome - $totalConsolidatedExpense;

        return [
            'allYears' => $allYears,
            'currentYear' => $currentYear,
            'periodMode' => $periodMode,
            'multiplier' => $multiplier,
            'learningSchools' => $learningSchools,
            'allSchools' => $allSchools,
            'incomeData' => $incomeData,
            'expenseData' => $expenseData,
            'operationalAccounts' => self::OPERATIONAL_ACCOUNTS,
            'summary' => [
                'total_income' => $totalConsolidatedIncome,
                'total_salary' => $totalConsolidatedSalary,
                'total_operational' => $totalConsolidatedOperational,
                'total_expense' => $totalConsolidatedExpense,
                'total_balance' => $totalConsolidatedBalance,
            ]
        ];
    }
}
