<?php

namespace App\Http\Controllers\Treasurer;

use App\Http\Controllers\Controller;
use App\Models\StudentBill;
use App\Models\Payment;
use App\Models\Student;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $schoolId = $user->school_id;

        // Guard: Treasurer must have a school assigned
        if (!$schoolId) {
            return redirect()->route('login')->with('error', 'Akun bendahara belum dikaitkan dengan sekolah.');
        }

        // Get current academic year (cached)
        $currentAcademicYear = Cache::remember('current_academic_year', 3600, function () {
            return AcademicYear::where('is_active', true)->first();
        });
        $currentMonth = now()->month;
        $currentYear = now()->year;

        // Base scope for bills of active students in this school up to current month
        $billScope = function ($query) use ($schoolId, $currentYear, $currentMonth) {
            $query->whereHas('student', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId)->where('status', 'aktif');
            })->where(function($q) use ($currentYear, $currentMonth) {
                $q->where('year', '<', $currentYear)
                  ->orWhere(function($q2) use ($currentYear, $currentMonth) {
                      $q2->where('year', '=', $currentYear)
                         ->where('month', '<=', $currentMonth);
                  });
            });
        };

        // Use DB aggregation instead of pulling all records into PHP memory
        $billsThisMonth = StudentBill::whereHas('student', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId)->where('status', 'aktif');
            })
            ->where('month', $currentMonth)
            ->where('year', $currentYear);

        $billsYtd = StudentBill::tap($billScope);

        // Statistics - cached for 5 minutes per school to reduce DB load
        $cacheKey = "treasurer_stats_{$schoolId}_{$currentYear}_{$currentMonth}";
        $stats = Cache::remember($cacheKey, 300, function () use ($billsThisMonth, $billsYtd, $schoolId, $currentMonth, $currentYear) {
            return [
            'bills_this_month' => (clone $billsThisMonth)->count(),

            'outstanding_this_month' => (clone $billsThisMonth)
                ->whereIn('status', ['belum_bayar', 'cicilan'])
                ->selectRaw('COALESCE(SUM(amount - paid_amount), 0) as total')
                ->value('total') ?? 0,

            'paid_this_month' => Payment::whereHas('student', function ($q) use ($schoolId) {
                    $q->where('school_id', $schoolId);
                })
                ->whereMonth('payment_date', $currentMonth)
                ->whereYear('payment_date', $currentYear)
                ->where('is_verified', true)
                ->sum('amount_paid'),

            'bills_ytd' => (clone $billsYtd)->count(),

            'outstanding_ytd' => (clone $billsYtd)
                ->whereIn('status', ['belum_bayar', 'cicilan'])
                ->selectRaw('COALESCE(SUM(amount - paid_amount), 0) as total')
                ->value('total') ?? 0,

            'paid_ytd' => Payment::whereHas('student', function ($q) use ($schoolId) {
                    $q->where('school_id', $schoolId);
                })
                ->whereYear('payment_date', $currentYear)
                ->where('is_verified', true)
                ->sum('amount_paid'),

            'total_students' => \App\Models\StudentClass::whereHas('student', function ($q) use ($schoolId) {
                    $q->where('school_id', $schoolId)->where('status', 'aktif');
                })
                ->when($currentAcademicYear, function ($q) use ($currentAcademicYear) {
                    $q->where('academic_year_id', $currentAcademicYear->id);
                })
                ->distinct('student_id')
                ->count('student_id'),

            'payments_today' => Payment::whereHas('student', function ($q) use ($schoolId) {
                    $q->where('school_id', $schoolId);
                })
                ->whereDate('payment_date', today())
                ->count(),
            ];
        });

        // Recent payments (last 10)
        $recentPayments = Payment::with(['student', 'bill.paymentType'])
            ->whereHas('student', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            })
            ->orderBy('payment_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Unpaid bills grouped by status
        $unpaidBills = StudentBill::with(['student', 'paymentType'])
            ->whereHas('student', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            })
            ->whereIn('status', ['belum_bayar', 'cicilan'])
            ->orderBy('created_at', 'desc')
            ->take(15)
            ->get();

        // Monthly revenue chart (last 6 months)
        $driver = DB::getDriverName();
        $dateFormat = $driver === 'sqlite'
            ? "strftime('%Y-%m', payment_date) as month"
            : 'DATE_FORMAT(payment_date, "%Y-%m") as month';

        $monthlyRevenue = Payment::whereHas('student', function ($q) use ($schoolId) {
            $q->where('school_id', $schoolId);
        })
            ->where('is_verified', true)
            ->where('payment_date', '>=', now()->subMonths(6))
            ->selectRaw("{$dateFormat}, SUM(amount_paid) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('treasurer.dashboard', compact(
            'stats',
            'recentPayments',
            'unpaidBills',
            'monthlyRevenue',
            'currentAcademicYear'
        ));
    }
}
