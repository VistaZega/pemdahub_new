<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentBillingController extends Controller
{
    /**
     * Get the authenticated teacher record.
     */
    private function getTeacher()
    {
        $user = Auth::user();
        return Teacher::where('user_id', $user->id)->firstOrFail();
    }

    public function index(Request $request)
    {
        $teacher = $this->getTeacher();
        $activeYear = AcademicYear::where('is_active', true)->first();

        if (!$activeYear) {
            return back()->with('error', 'Tidak ada Tahun Ajaran aktif.');
        }

        // Get homeroom classroom for active year
        $tIds = Auth::user() ? Auth::user()->teacherIds() : $teacher->allTeacherIds();
        $classroom = Classroom::whereIn('homeroom_teacher_id', $tIds)
            ->where('academic_year_id', $activeYear->id)
            ->with(['students' => function ($q) use ($activeYear) {
                // only active students
                $q->whereIn('student_classes.status', ['aktif', 'enrolled', 'active'])
                  ->where('student_classes.academic_year_id', $activeYear->id)
                  ->with(['bills' => function ($bq) use ($activeYear) {
                      $bq->where('academic_year_id', $activeYear->id)->with('paymentType');
                  }]);
            }])
            ->first();

        if (!$classroom) {
            $classroom = Classroom::whereIn('homeroom_teacher_id', $tIds)
                ->where('is_active', true)
                ->with(['students' => function ($q) {
                    $q->whereIn('student_classes.status', ['aktif', 'enrolled', 'active'])
                      ->with(['bills' => fn($bq) => $bq->with('paymentType')]);
                }])
                ->first();
        }

        if (!$classroom) {
            // Not a homeroom teacher or no class assigned
            return redirect()->route('guru.dashboard')->with('error', 'Anda tidak ditugaskan sebagai Wali Kelas pada Tahun Ajaran saat ini.');
        }

        $students = $classroom->students;

        $overallStats = [
            'total_bills' => 0,
            'lunas_count' => 0,
            'belum_bayar_count' => 0,
            'student_lunas' => 0,
            'student_cicilan' => 0,
            'student_belum' => 0,
        ];

        // Calculate stats per student and overall by item count
        $students->transform(function ($student) use (&$overallStats) {
            $bills = $student->bills;
            
            $totalBills = $bills->count();
            $lunasBills = $bills->where('status', 'lunas')->count();
            $tunggakanBills = $bills->filter(fn($b) => $b->isOverdue())->count();
            $upcomingBills = $totalBills - $lunasBills - $tunggakanBills;

            // Group bills by payment type
            $groupedBills = $bills->groupBy('payment_type_id')->map(function ($typeBills) {
                $paymentType = $typeBills->first()->paymentType;
                return (object)[
                    'type_name' => $paymentType->type_name ?? 'Lainnya',
                    'is_recurring' => $paymentType->is_recurring ?? false,
                    'bills' => $typeBills->sortBy('month')
                ];
            });

            // Build month map for recurring bills: month => bill
            $monthMap = [];
            foreach ($bills as $bill) {
                if ($bill->paymentType && $bill->paymentType->is_recurring && $bill->month) {
                    $key = $bill->payment_type_id . '_' . $bill->month;
                    $monthMap[$key] = $bill;
                }
            }
            $student->month_map = $monthMap;
            
            // Non-recurring bills
            $student->non_recurring_bills = $bills->filter(function ($b) {
                return !$b->paymentType || !$b->paymentType->is_recurring;
            })->values();
            
            // Determine student payment status
            if ($totalBills == 0) {
                $status = 'belum_ada_tagihan';
            } elseif ($lunasBills == $totalBills) {
                $status = 'lunas';
                $overallStats['student_lunas']++;
            } elseif ($tunggakanBills > 0) {
                $status = 'belum_bayar'; // They have overdue items
                $overallStats['student_belum']++;
            } else {
                $status = 'cicilan'; // They have paid some, or no overdue items yet
                $overallStats['student_cicilan']++;
            }

            $dueBillsCount = $lunasBills + $tunggakanBills;
            $percentage = $dueBillsCount > 0 ? round(($lunasBills / $dueBillsCount) * 100) : 0;

            $student->billing_stats = (object)[
                'total_bills' => $totalBills,
                'lunas_bills' => $lunasBills,
                'tunggakan_bills' => $tunggakanBills,
                'upcoming_bills' => $upcomingBills,
                'status' => $status,
                'percentage' => $percentage,
                'grouped_bills' => $groupedBills,
                
                // Monetary amounts
                'total_amount' => $bills->sum('amount'),
                'dipenuhi_amount' => $bills->sum('paid_amount'),
                'tunggakan_amount' => $bills->filter(fn($b) => $b->isOverdue())->sum(fn($b) => max(0, $b->amount - $b->paid_amount)),
                'upcoming_amount' => $bills->filter(fn($b) => !$b->isOverdue() && $b->status !== 'lunas')->sum(fn($b) => max(0, $b->amount - $b->paid_amount)),
            ];

            $overallStats['total_bills'] += $totalBills;
            $overallStats['lunas_count'] += $lunasBills;
            $overallStats['belum_bayar_count'] += $tunggakanBills;

            return $student;
        });

        // Collect all recurring payment types across all students for the matrix header
        $recurringTypes = collect();
        foreach ($students as $student) {
            foreach ($student->billing_stats->grouped_bills as $typeId => $group) {
                if ($group->is_recurring && !$recurringTypes->has($typeId)) {
                    $recurringTypes->put($typeId, $group->type_name);
                }
            }
        }

        // Month labels (Juli-Juni for typical academic year)
        $months = [7, 8, 9, 10, 11, 12, 1, 2, 3, 4, 5, 6];

        return view('guru.tagihan.index', compact('teacher', 'activeYear', 'classroom', 'students', 'overallStats', 'recurringTypes', 'months'));
    }
}
