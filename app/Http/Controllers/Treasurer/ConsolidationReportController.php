<?php

namespace App\Http\Controllers\Treasurer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\School;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\Employee;
use App\Services\EmployeeAssignmentService;
use Illuminate\Support\Facades\DB;

class ConsolidationReportController extends Controller
{
    public function __construct(private EmployeeAssignmentService $employeeService) {}

    public function index(Request $request)
    {
        $schoolId = auth()->user()->school_id;
        $school = School::findOrFail($schoolId);

        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        
        $activeYear = AcademicYear::where('is_active', true)->first();
        $activeSemester = Semester::where('is_active', true)->first();

        // 1. PENDAPATAN (Berdasarkan Uang Masuk Riil di bulan tersebut)
        $payments = Payment::with(['bill.paymentType', 'student.currentClassroom'])
            ->whereHas('student', function ($query) use ($schoolId) {
                $query->where('school_id', $schoolId);
            })
            ->whereMonth('payment_date', $month)
            ->whereYear('payment_date', $year)
            ->where('is_verified', true)
            ->get();

        // 1b. Pendapatan Pendaftaran Siswa Baru (PSB) Terverifikasi
        $psbPayments = \App\Models\ApplicantPayment::with('applicant')
            ->whereHas('applicant', function ($query) use ($schoolId) {
                $query->where('school_id', $schoolId);
            })
            ->whereNotNull('verified_at')
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->get();

        $psbIncomeTotal = $psbPayments->sum('amount');
        $grossIncome = $payments->sum('amount_paid') + $psbIncomeTotal;
        
        // Menghitung Kas Sekolah (Selisih dari tagihan kotor vs porsi yayasan)
        // Jika yayasan_share_amount null, maka tidak ada kas sekolah (seluruhnya yayasan)
        $schoolShareTotal = 0;
        
        // Group income by Payment Type for detailed report
        $groupedIncome = [];
        
        foreach ($payments as $payment) {
            $bill = $payment->bill;
            $paymentType = $bill->paymentType;
            $typeName = $paymentType->type_name ?? 'Lainnya';
            $paymentAmount = $payment->amount_paid;
            
            $key = $typeName;
            
            // Format SPP by Grade Level and Tariff
            if (stripos($typeName, 'SPP') !== false && $payment->student) {
                $classroom = $payment->student->currentClassroom->first();
                if ($classroom) {
                    $grade = $classroom->grade_level;
                    $gradeMap = [
                        7 => 'VII', 8 => 'VIII', 9 => 'IX',
                        10 => 'X', 11 => 'XI', 12 => 'XII'
                    ];
                    $gradeText = $gradeMap[$grade] ?? $grade;
                    
                    $tariff = $bill->amount;
                    $baseTariff = $paymentType->amount ?? $tariff;
                    
                    if ($tariff < $baseTariff) {
                        $key = "SPP Kelas {$gradeText} (Tarif Netto Rp " . number_format($tariff, 0, ',', '.') . " - Diskon)";
                    } else {
                        $key = "SPP Kelas {$gradeText} (Tarif Rp " . number_format($tariff, 0, ',', '.') . ")";
                    }
                } else {
                    $key = "SPP (Tarif Rp " . number_format($bill->amount, 0, ',', '.') . ")";
                }
            }
            
            // Hitung school share untuk payment ini
            // Asumsi: Jika payment_amount >= bill->amount, maka school share full.
            // Jika parsial, proporsional atau kita hitung selisih dari bill->yayasan_share_amount
            // Paling aman: School Share = bill->amount - bill->yayasan_share_amount (batas max),
            // Tapi karena pembayaran bisa sebagian, kita ambil (amount_paid - yayasan_share_amount).
            // Jika amount_paid < yayasan_share_amount, maka 0 (prioritas bayar yayasan dulu).
            $yayasanShare = $bill->yayasan_share_amount ?? $bill->amount;
            
            if ($paymentAmount > $yayasanShare) {
                $schoolShare = $paymentAmount - $yayasanShare;
            } else {
                $schoolShare = 0;
            }
            
            $schoolShareTotal += $schoolShare;
            
            if (!isset($groupedIncome[$key])) {
                $groupedIncome[$key] = [
                    'amount' => 0,
                    'count' => 0,
                ];
            }
            $groupedIncome[$key]['amount'] += $paymentAmount;
            $groupedIncome[$key]['count'] += 1;
        }

        $incomeDetails = [];
        foreach ($groupedIncome as $key => $data) {
            if (stripos($key, 'SPP') !== false) {
                $displayKey = $key . " - " . $data['count'] . " Siswa/Pembayaran";
            } else {
                $displayKey = $key . " (" . $data['count'] . " Transaksi)";
            }
            $incomeDetails[$displayKey] = $data['amount'];
        }

        if ($psbIncomeTotal > 0) {
            $incomeDetails["Pendaftaran Siswa Baru (PSB) (" . $psbPayments->count() . " Transaksi)"] = $psbIncomeTotal;
        }

        // 2. PENGELUARAN GAJI
        $employeeQuery = Employee::with(['activePositions', 'teacher'])
            ->where('is_active', true);

        if ($schoolId) {
            $employeeQuery->where(function ($q) use ($schoolId, $activeYear) {
                $q->where('school_id', $schoolId);
                if ($activeYear) {
                    $q->orWhereHas('activePositions', function ($posQ) use ($schoolId, $activeYear) {
                        $posQ->where('positions.school_id', $schoolId)
                             ->where('employee_positions.academic_year_id', $activeYear->id);
                    })
                    ->orWhereHas('teacher.teachingAssignments', function ($teachQ) use ($schoolId, $activeYear) {
                        $teachQ->where('academic_year_id', $activeYear->id)
                               ->where('is_active', true)
                               ->whereHas('classroom', fn($cQ) => $cQ->where('school_id', $schoolId));
                    });
                }
            });
        }

        $employees = $employeeQuery->get();

        $salaryTotal = 0;
        $salaryDetails = [
            'Guru (Tugas Mengajar)' => 0,
            'Staf / Struktural' => 0,
            'Fungsional' => 0,
            'Support' => 0,
        ];

        if ($activeYear && $activeSemester) {
            foreach ($employees as $emp) {
                $salaryData = $this->employeeService->calculateFullSalary(
                    $emp, 
                    $activeYear, 
                    $activeSemester, 
                    $school ? $school->type : null, 
                    $schoolId
                );
                
                $thp = $salaryData['thp'] ?? 0;
                $salaryTotal += $thp;
                
                // Categorize for report
                $cat = $this->categorizeEmployee($emp);
                $salaryDetails[$cat] = ($salaryDetails[$cat] ?? 0) + $thp;
            }
        }

        // 3. SALDO NETTO YAYASAN
        // Saldo = Pendapatan Kotor - Gaji - Kas Sekolah
        $netBalance = $grossIncome - $salaryTotal - $schoolShareTotal;

        // 4. DAFTAR TUNGGAKAN (PENDAPATAN BELUM TERCAPAI)
        $unpaidBills = \App\Models\StudentBill::with(['student.currentClassroom', 'paymentType'])
            ->whereHas('student', function ($query) use ($schoolId) {
                $query->where('school_id', $schoolId);
                // Optionally only active students: ->whereIn('status', ['aktif'])
            })
            ->where('month', $month)
            ->where('year', $year)
            ->where('status', '!=', 'lunas')
            ->get();

        return view('treasurer.reports.consolidation', compact(
            'school',
            'month',
            'year',
            'grossIncome',
            'incomeDetails',
            'schoolShareTotal',
            'salaryTotal',
            'salaryDetails',
            'netBalance',
            'unpaidBills'
        ));
    }

    /**
     * Categorize employee salary for consolidation report
     */
    private function categorizeEmployee(Employee $emp): string
    {
        $type = strtolower($emp->employee_type ?? '');

        // Khusus Yulianus Zega atau Pegawai yang bertugas mengajar di kelas (dikategori Guru)
        if ($emp->employee_code === 'PTY-001' || str_contains(strtolower($emp->full_name), 'yulianus zega')) {
            return 'Guru (Tugas Mengajar)';
        }

        // 1. Support (Keamanan, Kebersihan, Sopir, Pendukung)
        if (in_array($type, ['security', 'cleaning_service', 'driver'])) {
            return 'Support';
        }

        $positions = $emp->activePositions;
        $posNames = strtolower($positions->pluck('position_name')->join(' '));
        $posCategories = strtolower($positions->pluck('position_category')->join(' '));

        if (preg_match('/(satpam|security|kebersihan|cleaning|driver|sopir|penjaga|janitor|taman)/i', $posNames)) {
            return 'Support';
        }

        // 2. Fungsional (Wali Kelas, Pembimbing PKL, Kepala Lab, Kepala Perpus, BK, Koordinator, Konselor, Piket)
        if (str_contains($posCategories, 'fungsional') || 
            preg_match('/(pembimbing|pkl|kepala lab|laboratorium|kepala perpus|perpustakaan|bimbingan konseling|\bbk\b|koordinator|konselor|piket)/i', $posNames)) {
            return 'Fungsional';
        }

        // 3. Staf / Struktural (Kepsek, Wakasek, KTU, Bendahara, Staff TU, Kasubag, Kaprog, PKS)
        if ($type !== 'guru' || 
            str_contains($posCategories, 'struktural') || 
            preg_match('/(kepala sekolah|kepsek|wakil kepala|wakasek|ktu|tata usaha|bendahara|kaprog|kasubag|pks)/i', $posNames)) {
            return 'Staf / Struktural';
        }

        // 4. Default: Guru (Tugas Mengajar)
        return 'Guru (Tugas Mengajar)';
    }

    /**
     * Export Laporan Konsolidasi ke PDF
     */
    public function exportPdf(Request $request)
    {
        $schoolId = auth()->user()->school_id;
        $school = School::findOrFail($schoolId);

        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);
        
        $activeYear = AcademicYear::where('is_active', true)->first();
        $activeSemester = Semester::where('is_active', true)->first();

        // 1. PENDAPATAN
        $payments = Payment::with(['bill.paymentType', 'student.currentClassroom'])
            ->whereHas('student', fn($query) => $query->where('school_id', $schoolId))
            ->whereMonth('payment_date', $month)
            ->whereYear('payment_date', $year)
            ->where('is_verified', true)
            ->get();

        $psbPayments = \App\Models\ApplicantPayment::with('applicant')
            ->whereHas('applicant', fn($query) => $query->where('school_id', $schoolId))
            ->whereNotNull('verified_at')
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->get();

        $psbIncomeTotal = $psbPayments->sum('amount');
        $grossIncome = $payments->sum('amount_paid') + $psbIncomeTotal;
        $schoolShareTotal = 0;
        $groupedIncome = [];
        
        foreach ($payments as $payment) {
            $bill = $payment->bill;
            $paymentType = $bill->paymentType;
            $typeName = $paymentType->type_name ?? 'Lainnya';
            $paymentAmount = $payment->amount_paid;
            $key = $typeName;
            
            if (stripos($typeName, 'SPP') !== false && $payment->student) {
                $classroom = $payment->student->currentClassroom->first();
                if ($classroom) {
                    $grade = $classroom->grade_level;
                    $gradeMap = [7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'];
                    $gradeText = $gradeMap[$grade] ?? $grade;
                    $tariff = $bill->amount;
                    $baseTariff = $paymentType->amount ?? $tariff;
                    $key = ($tariff < $baseTariff) 
                        ? "SPP Kelas {$gradeText} (Netto Rp " . number_format($tariff, 0, ',', '.') . " - Diskon)" 
                        : "SPP Kelas {$gradeText} (Tarif Rp " . number_format($tariff, 0, ',', '.') . ")";
                }
            }
            
            $yayasanShare = $bill->yayasan_share_amount ?? $bill->amount;
            $schoolShareTotal += ($paymentAmount > $yayasanShare) ? ($paymentAmount - $yayasanShare) : 0;
            
            if (!isset($groupedIncome[$key])) {
                $groupedIncome[$key] = ['amount' => 0, 'count' => 0];
            }
            $groupedIncome[$key]['amount'] += $paymentAmount;
            $groupedIncome[$key]['count'] += 1;
        }

        $incomeDetails = [];
        foreach ($groupedIncome as $key => $data) {
            $displayKey = (stripos($key, 'SPP') !== false) ? $key . " - " . $data['count'] . " Siswa/Pembayaran" : $key . " (" . $data['count'] . " Transaksi)";
            $incomeDetails[$displayKey] = $data['amount'];
        }
        if ($psbIncomeTotal > 0) {
            $incomeDetails["Pendaftaran Siswa Baru (PSB) (" . $psbPayments->count() . " Transaksi)"] = $psbIncomeTotal;
        }

        // 2. GAJI
        $employeeQuery = Employee::with(['activePositions', 'teacher'])->where('is_active', true)->where('school_id', $schoolId);
        $employees = $employeeQuery->get();
        $salaryTotal = 0;
        $salaryDetails = ['Guru (Tugas Mengajar)' => 0, 'Staf / Struktural' => 0, 'Fungsional' => 0, 'Support' => 0];

        if ($activeYear && $activeSemester) {
            foreach ($employees as $emp) {
                $salData = $this->employeeService->calculateFullSalary($emp, $activeYear, $activeSemester, $school->type, $schoolId);
                $thp = $salData['thp'] ?? 0;
                $salaryTotal += $thp;
                $cat = $this->categorizeEmployee($emp);
                $salaryDetails[$cat] = ($salaryDetails[$cat] ?? 0) + $thp;
            }
        }

        $netBalance = $grossIncome - $salaryTotal - $schoolShareTotal;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('treasurer.reports.consolidation_pdf', compact(
            'school', 'month', 'year', 'grossIncome', 'incomeDetails', 'schoolShareTotal', 'salaryTotal', 'salaryDetails', 'netBalance'
        ));
        $pdf->setPaper('A4', 'portrait');

        $fileName = 'Laporan-Konsolidasi-' . str_replace(' ', '-', $school->name) . "-{$month}-{$year}.pdf";
        return $pdf->download($fileName);
    }
}
