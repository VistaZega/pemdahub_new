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
        $payments = Payment::with(['bill.paymentType', 'student'])
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
        $incomeDetails = [];
        
        foreach ($payments as $payment) {
            $bill = $payment->bill;
            $typeName = $bill->paymentType->type_name ?? 'Lainnya';
            
            // Hitung gross
            $paymentAmount = $payment->amount_paid;
            
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
            
            if (!isset($incomeDetails[$typeName])) {
                $incomeDetails[$typeName] = 0;
            }
            $incomeDetails[$typeName] += $paymentAmount;
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
                    null, 
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
