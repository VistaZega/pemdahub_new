<?php

namespace App\Http\Controllers\Treasurer;

use App\Http\Controllers\Controller;
use App\Models\StudentBill;
use App\Models\Payment;
use App\Models\PaymentType;
use App\Models\AcademicYear;
use App\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $schoolId = auth()->user()->school_id;

        // Filter options
        $paymentTypes = PaymentType::where('school_id', $schoolId)
            ->where('is_active', true)
            ->orderBy('type_name')
            ->get();

        $academicYears = AcademicYear::orderBy('year', 'desc')->get();

        $activeAcademicYear = AcademicYear::where('is_active', true)->first();

        $classrooms = Classroom::where('school_id', $schoolId)
            ->when($activeAcademicYear, function ($q) use ($activeAcademicYear) {
                $q->where('academic_year_id', $activeAcademicYear->id);
            })
            ->orderBy('class_name')
            ->get();

        // Default filters
        $paymentTypeId = $request->payment_type_id;
        $academicYearId = $request->academic_year_id ?? AcademicYear::where('is_active', true)->first()?->id;
        $classroomId = $request->classroom_id;
        $periodType = $request->period_type ?? 'ytd'; // yearly, ytd, month
        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;
        $showAll = $request->boolean('show_all'); // Toggle untuk tampilkan semua termasuk non-aktif

        // Build query
        $query = StudentBill::with(['student', 'paymentType', 'academicYear'])
            ->whereHas('student', function ($q) use ($schoolId, $showAll) {
                $q->where('school_id', $schoolId);
                if (!$showAll) {
                    $q->where('status', 'aktif'); // Default: hanya siswa aktif
                }
            });

        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }

        if ($paymentTypeId) {
            $query->where('payment_type_id', $paymentTypeId);
        }

        if ($classroomId) {
            $studentIds = DB::table('student_classes')
                ->where('classroom_id', $classroomId)
                ->where('academic_year_id', $academicYearId)
                ->pluck('student_id');
            $query->whereIn('student_id', $studentIds);
        }

        // Period filter
        if ($periodType == 'month') {
            $query->where('month', $month)->where('year', $year);
        } elseif ($periodType == 'ytd') {
            $query->where(function($q) use ($year, $month) {
                $q->where('year', '<', $year)
                  ->orWhere(function($q2) use ($year, $month) {
                      $q2->where('year', '=', $year)
                         ->where('month', '<=', $month);
                  });
            });
        }
        // yearly = no additional filter

        $bills = $query->orderBy('year')->orderBy('month')->get();

        // Calculate statistics
        $totalBills = $bills->count();
        $totalAmount = $bills->sum('amount');
        $totalPaid = $bills->sum('paid_amount');
        $totalOutstanding = $bills->sum(function($bill) {
            return ($bill->amount - $bill->paid_amount) + $bill->late_fee;
        });

        // Group by student for matrix view (student per row, months as columns)
        $studentsData = $bills->groupBy('student_id')->map(function($studentBills) use ($academicYearId) {
            $student = $studentBills->first()->student;
            
            // Get classroom for this academic year
            $classroom = $student->classrooms->where('pivot.academic_year_id', $academicYearId)->first();
            
            // Group bills by month
            $monthlyBills = [];
            foreach ($studentBills as $bill) {
                $monthKey = $bill->month;
                $monthlyBills[$monthKey] = [
                    'status' => $bill->status,
                    'paid_amount' => $bill->paid_amount,
                    'amount' => $bill->amount,
                    'is_paid' => $bill->status == 'lunas'
                ];
            }
            
            return [
                'student'          => $student,
                'classroom'        => $classroom,
                'monthly_bills'    => $monthlyBills,
                'total_bills'      => $studentBills->count(),
                'paid_count'       => $studentBills->where('status', 'lunas')->count(),
                'total_amount'     => $studentBills->sum('amount'),
                'total_paid'       => $studentBills->sum('paid_amount'),
                'total_outstanding'=> max(0, $studentBills->sum('amount') - $studentBills->sum('paid_amount')),
            ];
        });

        // Get filter info for display
        $selectedPaymentType = $paymentTypeId ? PaymentType::find($paymentTypeId) : null;
        $selectedAcademicYear = $academicYearId ? AcademicYear::find($academicYearId) : null;
        $selectedClassroom = $classroomId ? Classroom::find($classroomId) : null;

        return view('treasurer.reports.index', compact(
            'paymentTypes',
            'academicYears',
            'classrooms',
            'paymentTypeId',
            'academicYearId',
            'classroomId',
            'periodType',
            'month',
            'year',
            'showAll',
            'totalBills',
            'totalAmount',
            'totalPaid',
            'totalOutstanding',
            'studentsData',
            'selectedPaymentType',
            'selectedAcademicYear',
            'selectedClassroom'
        ));
    }

    public function export(Request $request)
    {
        $schoolId = auth()->user()->school_id;

        // Same filter logic as index
        $paymentTypeId = $request->payment_type_id;
        $academicYearId = $request->academic_year_id;
        $classroomId = $request->classroom_id;
        $periodType = $request->period_type ?? 'ytd';
        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;
        $showAll = $request->boolean('show_all');

        $query = StudentBill::with(['student', 'paymentType', 'academicYear'])
            ->whereHas('student', function ($q) use ($schoolId, $showAll) {
                $q->where('school_id', $schoolId);
                if (!$showAll) {
                    $q->where('status', 'aktif');
                }
            });

        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }

        if ($paymentTypeId) {
            $query->where('payment_type_id', $paymentTypeId);
        }

        if ($classroomId) {
            $studentIds = DB::table('student_classes')
                ->where('classroom_id', $classroomId)
                ->where('academic_year_id', $academicYearId)
                ->pluck('student_id');
            $query->whereIn('student_id', $studentIds);
        }

        if ($periodType == 'month') {
            $query->where('month', $month)->where('year', $year);
        } elseif ($periodType == 'ytd') {
            $query->where(function($q) use ($year, $month) {
                $q->where('year', '<', $year)
                  ->orWhere(function($q2) use ($year, $month) {
                      $q2->where('year', '=', $year)
                         ->where('month', '<=', $month);
                  });
            });
        }

        $bills = $query->orderBy('year')->orderBy('month')->get();

        // Get filter info
        $filters = [
            'payment_type'       => $paymentTypeId ? PaymentType::find($paymentTypeId) : null,
            'academic_year'      => $academicYearId ? AcademicYear::find($academicYearId) : null,
            'classroom'          => $classroomId ? Classroom::find($classroomId) : null,
            'school'             => null,
            'period_type'        => $periodType,
            'month'              => $month,
            'year'               => $year,
            'show_school_column' => false,
        ];

        return \Excel::download(
            new \App\Exports\BillsReportExport($bills, $filters),
            'laporan-pembayaran-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Laporan Buku Kas Umum (BKU) Unit Sekolah
     */
    public function bku(Request $request)
    {
        $schoolId = auth()->user()->school_id;
        $school = \App\Models\School::findOrFail($schoolId);

        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);

        $bkuData = $this->getBkuData($schoolId, $month, $year);

        return view('treasurer.reports.bku', array_merge($bkuData, compact('school', 'month', 'year')));
    }

    /**
     * Export Buku Kas Umum (BKU) ke PDF
     */
    public function exportBkuPdf(Request $request)
    {
        $schoolId = auth()->user()->school_id;
        $school = \App\Models\School::findOrFail($schoolId);

        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);

        $bkuData = $this->getBkuData($schoolId, $month, $year);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('treasurer.reports.bku_pdf', array_merge($bkuData, compact('school', 'month', 'year')));
        $pdf->setPaper('A4', 'landscape');

        $fileName = 'Buku-Kas-Umum-' . str_replace(' ', '-', $school->name) . "-{$month}-{$year}.pdf";

        return $pdf->download($fileName);
    }

    /**
     * Helper penghimpun data BKU
     */
    private function getBkuData(int $schoolId, int $month, int $year): array
    {
        // 1. Kas Masuk Pembayaran Siswa
        $studentPayments = Payment::with(['bill.paymentType', 'student'])
            ->whereHas('student', fn($q) => $q->where('school_id', $schoolId))
            ->whereMonth('payment_date', $month)
            ->whereYear('payment_date', $year)
            ->where('is_verified', true)
            ->get();

        // 2. Kas Masuk PSB
        $psbPayments = \App\Models\ApplicantPayment::with('applicant')
            ->whereHas('applicant', fn($q) => $q->where('school_id', $schoolId))
            ->whereNotNull('verified_at')
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->get();

        // 3. Kas Keluar Operasional
        $opsExpenses = \App\Models\OperationalExpense::with('expenseCategory')
            ->where('school_id', $schoolId)
            ->whereMonth('expense_date', $month)
            ->whereYear('expense_date', $year)
            ->get();

        // 4. Kas Keluar Gaji
        $activeYear = AcademicYear::where('is_active', true)->first();
        $activeSemester = \App\Models\Semester::where('is_active', true)->first();
        $assignmentService = app(\App\Services\EmployeeAssignmentService::class);

        $employees = \App\Models\Employee::where('school_id', $schoolId)->where('is_active', true)->get();
        $salaryTotal = 0;
        if ($activeYear && $activeSemester) {
            foreach ($employees as $emp) {
                $sal = $assignmentService->calculateFullSalary($emp, $activeYear, $activeSemester, null, $schoolId);
                $salaryTotal += (float)($sal['thp'] ?? 0);
            }
        }

        // Susun Jurnal Kronologis
        $transactions = collect();

        foreach ($studentPayments as $p) {
            $typeName = $p->bill->paymentType->type_name ?? 'Pembayaran';
            $studentName = $p->student->full_name ?? '-';
            $transactions->push([
                'date' => $p->payment_date ? \Carbon\Carbon::parse($p->payment_date)->format('Y-m-d') : now()->format('Y-m-d'),
                'ref_no' => $p->receipt_number ?? "PAY-{$p->id}",
                'description' => "Penerimaan {$typeName} a.n. {$studentName}",
                'type' => 'in',
                'debit' => (float)$p->amount_paid,
                'credit' => 0,
            ]);
        }

        foreach ($psbPayments as $psb) {
            $applicantName = $psb->applicant->full_name ?? '-';
            $transactions->push([
                'date' => $psb->created_at ? $psb->created_at->format('Y-m-d') : now()->format('Y-m-d'),
                'ref_no' => $psb->transaction_id ?? "PSB-{$psb->id}",
                'description' => "Penerimaan Pendaftaran Siswa Baru (PSB) a.n. {$applicantName}",
                'type' => 'in',
                'debit' => (float)$psb->amount,
                'credit' => 0,
            ]);
        }

        foreach ($opsExpenses as $exp) {
            $catName = $exp->expenseCategory->name ?? 'Operasional';
            $transactions->push([
                'date' => $exp->expense_date ? \Carbon\Carbon::parse($exp->expense_date)->format('Y-m-d') : now()->format('Y-m-d'),
                'ref_no' => "EXP-{$exp->id}",
                'description' => "Pengeluaran {$catName}: {$exp->title}",
                'type' => 'out',
                'debit' => 0,
                'credit' => (float)$exp->amount,
            ]);
        }

        if ($salaryTotal > 0) {
            $lastDay = \Carbon\Carbon::create($year, $month, 1)->endOfMonth()->format('Y-m-d');
            $transactions->push([
                'date' => $lastDay,
                'ref_no' => "GAJI-{$year}-{$month}",
                'description' => "Pengeluaran Gaji & Tunjangan Pegawai Bulan " . \Carbon\Carbon::create($year, $month, 1)->translatedFormat('F Y'),
                'type' => 'out',
                'debit' => 0,
                'credit' => $salaryTotal,
            ]);
        }

        $sortedTransactions = $transactions->sortBy('date')->values();

        $totalDebit = $sortedTransactions->sum('debit');
        $totalCredit = $sortedTransactions->sum('credit');
        $netEndingBalance = $totalDebit - $totalCredit;

        // Hitung Running Balance
        $runningBalance = 0;
        $formattedJournal = $sortedTransactions->map(function ($item) use (&$runningBalance) {
            $runningBalance += ($item['debit'] - $item['credit']);
            $item['balance'] = $runningBalance;
            return $item;
        });

        return [
            'transactions' => $formattedJournal,
            'totalDebit' => $totalDebit,
            'totalCredit' => $totalCredit,
            'netEndingBalance' => $netEndingBalance,
        ];
    }
}
