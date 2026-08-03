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

        $grossIncome = $payments->sum('amount_paid');
        
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

        // 2. PENGELUARAN GAJI
        $employees = Employee::with(['activePositions', 'teacher'])
            ->where('school_id', $schoolId)
            ->where('is_active', true)
            ->get();

        $salaryTotal = 0;
        $salaryDetails = [
            'Guru (Tugas Mengajar)' => 0,
            'Staf/Struktural' => 0,
            'Tunjangan & Lainnya' => 0,
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
                if ($emp->employee_type === 'guru') {
                    $salaryDetails['Guru (Tugas Mengajar)'] += $thp;
                } else {
                    $salaryDetails['Staf/Struktural'] += $thp;
                }
            }
        }

        // 3. SALDO NETTO YAYASAN
        // Saldo = Pendapatan Kotor - Gaji - Kas Sekolah
        $netBalance = $grossIncome - $salaryTotal - $schoolShareTotal;

        return view('treasurer.reports.consolidation', compact(
            'school',
            'month',
            'year',
            'grossIncome',
            'incomeDetails',
            'schoolShareTotal',
            'salaryTotal',
            'salaryDetails',
            'netBalance'
        ));
    }
}
