<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\School;
use Illuminate\Http\Request;
use Carbon\Carbon;

class EmployeeAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $date = $request->get('date', today()->toDateString());

        $schoolId = $request->get('school_id');
        if (!$user->isSuperAdmin() && !$user->isKetuaYayasan()) {
            $schoolId = $user->school_id;
        }

        $employees = collect();
        $attendances = collect();
        $stats = ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpha' => 0, 'dinas_luar' => 0, 'cuti' => 0, 'belum' => 0];

        if ($schoolId) {
            $employees = Employee::with('school')
                ->where('is_active', true)
                ->where('school_id', $schoolId)
                ->where(function($q) {
                    $q->where('employee_type', '!=', 'guru')
                      ->whereDoesntHave('teacher');
                })
                ->orderBy('full_name')
                ->get();

            $attendances = EmployeeAttendance::where('date', $date)
                ->where('school_id', $schoolId)
                ->whereHas('employee', function($q) {
                    $q->where('employee_type', '!=', 'guru')
                      ->whereDoesntHave('teacher');
                })
                ->get()
                ->keyBy('employee_id');

            foreach ($employees as $emp) {
                $att = $attendances->get($emp->id);
                if ($att) {
                    $stats[$att->status] = ($stats[$att->status] ?? 0) + 1;
                } else {
                    $stats['belum']++;
                }
            }

            // Hanya tampilkan pegawai yang sudah memiliki record absensi, urutkan dari yang terbaru absen
            $employees = $employees->filter(function($emp) use ($attendances) {
                return $attendances->has($emp->id);
            })->sortByDesc(function($emp) use ($attendances) {
                return $attendances->get($emp->id)->created_at;
            })->values();
        }

        $schools = $user->isSuperAdmin() || $user->isKetuaYayasan()
            ? School::where('is_active', true)->orderBy('name')->get()
            : School::where('id', $user->school_id)->get();

        return view('admin.employees.attendance.index', compact(
            'employees', 'attendances', 'stats', 'schools', 'schoolId', 'date'
        ));
    }

    public function bulkInput(Request $request)
    {
        $user = auth()->user();
        $date = $request->get('date', today()->toDateString());

        $schoolId = $request->get('school_id');
        if (!$user->isSuperAdmin() && !$user->isKetuaYayasan()) {
            $schoolId = $user->school_id;
        }

        if (!$schoolId) {
            return redirect()->route('admin.employees.attendance.index')
                ->with('error', 'Pilih sekolah terlebih dahulu.');
        }

        $school = School::findOrFail($schoolId);
        $employees = Employee::where('is_active', true)
            ->where('school_id', $schoolId)
            ->where(function($q) {
                $q->where('employee_type', '!=', 'guru')
                  ->whereDoesntHave('teacher');
            })
            ->orderBy('full_name')
            ->get();

        $existing = EmployeeAttendance::where('date', $date)
            ->where('school_id', $schoolId)
            ->whereHas('employee', function($q) {
                $q->where('employee_type', '!=', 'guru')
                  ->whereDoesntHave('teacher');
            })
            ->get()
            ->keyBy('employee_id');

        return view('admin.employees.attendance.bulk-input', compact(
            'employees', 'existing', 'school', 'date'
        ));
    }

    public function bulkStore(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'school_id' => 'required|exists:schools,id',
            'attendance' => 'required|array',
            'attendance.*.status' => 'required|in:hadir,sakit,izin,alpha,dinas_luar,cuti',
        ]);

        $date = $request->date;
        $schoolId = $request->school_id;
        $userId = auth()->id();
        $count = 0;

        foreach ($request->attendance as $employeeId => $data) {
            EmployeeAttendance::updateOrCreate(
                ['employee_id' => $employeeId, 'date' => $date],
                [
                    'school_id' => $schoolId,
                    'status' => $data['status'],
                    'time_in' => $data['time_in'] ?? null,
                    'time_out' => $data['time_out'] ?? null,
                    'notes' => $data['notes'] ?? null,
                    'recorded_via' => 'manual',
                    'recorded_by' => $userId,
                ]
            );
            $count++;
        }

        return redirect()->route('admin.employees.attendance.index', [
            'date' => $date, 'school_id' => $schoolId
        ])->with('success', "Absensi {$count} pegawai berhasil disimpan.");
    }

    public function rekap(Request $request)
    {
        $user = auth()->user();
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $schoolId = $request->get('school_id');
        if (!$user->isSuperAdmin() && !$user->isKetuaYayasan()) {
            $schoolId = $user->school_id;
        }

        $employees = collect();
        $attendanceData = [];
        $daysInMonth = Carbon::create($year, $month)->daysInMonth;

        if ($schoolId) {
            $employees = Employee::where('is_active', true)
                ->where('school_id', $schoolId)
                ->where(function($q) {
                    $q->where('employee_type', '!=', 'guru')
                      ->whereDoesntHave('teacher');
                })
                ->orderBy('full_name')
                ->get();

            $attendances = EmployeeAttendance::where('school_id', $schoolId)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->whereHas('employee', function($q) {
                    $q->where('employee_type', '!=', 'guru')
                      ->whereDoesntHave('teacher');
                })
                ->get();

            // Build matrix: employee_id => [day => EmployeeAttendance model]
            foreach ($attendances as $att) {
                $day = $att->date->day;
                $attendanceData[$att->employee_id][$day] = $att;
            }
        }

        $schools = $user->isSuperAdmin() || $user->isKetuaYayasan()
            ? School::where('is_active', true)->orderBy('name')->get()
            : School::where('id', $user->school_id)->get();

        return view('admin.employees.attendance.rekap', compact(
            'employees', 'attendanceData', 'schools', 'schoolId', 'month', 'year', 'daysInMonth'
        ));
    }

    public function monitoring(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->isSuperAdmin() || $user->isKetuaYayasan();
        $schoolId = $request->input('school_id', $isSuperAdmin ? null : $user->school_id);
        
        $date = $request->input('date', \Carbon\Carbon::now('Asia/Jakarta')->toDateString());
        $startDateOfMonth = \Carbon\Carbon::parse($date)->startOfMonth();
        $endDate = \Carbon\Carbon::parse($date);

        // Daily Stats
        $dailyStatsQuery = EmployeeAttendance::where('date', $date)
            ->whereHas('employee', function ($q) use ($schoolId) {
                $q->where('is_active', true)->where('employee_type', '!=', 'guru');
                if ($schoolId) {
                    $q->where('school_id', $schoolId);
                }
            });

        $dailyStats = [
            'hadir' => (clone $dailyStatsQuery)->where('status', 'hadir')->count(),
            'sakit' => (clone $dailyStatsQuery)->where('status', 'sakit')->count(),
            'izin' => (clone $dailyStatsQuery)->where('status', 'izin')->count(),
            'alpha' => (clone $dailyStatsQuery)->where('status', 'alpha')->count(),
            'dinas_luar' => (clone $dailyStatsQuery)->where('status', 'dinas_luar')->count(),
            'cuti' => (clone $dailyStatsQuery)->where('status', 'cuti')->count(),
        ];
        $dailyStats['total_daily'] = array_sum($dailyStats);

        // Active Employee Count
        $activeEmployeesQuery = Employee::where('is_active', true)->where('employee_type', '!=', 'guru');
        if ($schoolId) {
            $activeEmployeesQuery->where('school_id', $schoolId);
        }
        $activeEmployeeCount = $activeEmployeesQuery->count();

        // Cumulative Stats (Start of Month)
        $cumulativeStatsQuery = EmployeeAttendance::whereBetween('date', [$startDateOfMonth->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->whereHas('employee', function ($q) use ($schoolId) {
                $q->where('is_active', true)->where('employee_type', '!=', 'guru');
                if ($schoolId) {
                    $q->where('school_id', $schoolId);
                }
            });

        // Calculate 'Z' -> working days since start of month
        $workingDays = 0;
        for ($d = $startDateOfMonth->copy(); $d->lte($endDate); $d->addDay()) {
            if (!in_array($d->dayOfWeek, [0, 6])) { // Skip Sun, Sat
                $workingDays++;
            }
        }
        
        // Calculate dynamic expected Z (total expected attendances) for all active employees
        $totalExpectedAttendances = 0;
        $allActiveEmployees = \App\Models\Employee::where('is_active', true)
            ->where('employee_type', '!=', 'guru')
            ->when($schoolId, function($q) use ($schoolId) {
                return $q->where('school_id', $schoolId);
            })
            ->with('teacher.schedules')
            ->get();
            
        foreach ($allActiveEmployees as $emp) {
            $isTeacher = $emp->teacher !== null && $emp->teacher->schedules->count() > 0;
            $teachingDays = [];
            if ($isTeacher) {
                $teachingDays = $emp->teacher->schedules->pluck('day_of_week')->map(function($day) {
                    return strtolower($day);
                })->unique()->toArray();
            }
            
            for ($d = $startDateOfMonth->copy(); $d->lte($endDate); $d->addDay()) {
                if ($isTeacher) {
                    if (in_array(strtolower($d->format('l')), $teachingDays)) {
                        $totalExpectedAttendances++;
                    }
                } else {
                    if (!in_array($d->dayOfWeek, [0, 6])) {
                        $totalExpectedAttendances++;
                    }
                }
            }
        }
        
        $cumulativeStats = [
            'hadir' => (clone $cumulativeStatsQuery)->where('status', 'hadir')->count(),
            'sakit' => (clone $cumulativeStatsQuery)->where('status', 'sakit')->count(),
            'izin' => (clone $cumulativeStatsQuery)->where('status', 'izin')->count(),
            'alpha' => (clone $cumulativeStatsQuery)->where('status', 'alpha')->count(),
            'dinas_luar' => (clone $cumulativeStatsQuery)->where('status', 'dinas_luar')->count(),
            'cuti' => (clone $cumulativeStatsQuery)->where('status', 'cuti')->count(),
            'z' => $workingDays,
            'expected_attendances' => $totalExpectedAttendances,
            'active_employees' => $activeEmployeeCount
        ];
        $cumulativeStats['total_count'] = $cumulativeStats['hadir'] + $cumulativeStats['sakit'] + $cumulativeStats['izin'] + $cumulativeStats['alpha'] + $cumulativeStats['dinas_luar'] + $cumulativeStats['cuti'];

        // Chart Data (Last 14 days)
        $chartData = [];
        $startChart = $endDate->copy()->subDays(13);
        $chartQuery = EmployeeAttendance::whereBetween('date', [$startChart->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->whereHas('employee', function ($q) use ($schoolId) {
                $q->where('is_active', true)->where('employee_type', '!=', 'guru');
                if ($schoolId) {
                    $q->where('school_id', $schoolId);
                }
            })->get();

        for ($d = $startChart->copy(); $d->lte($endDate); $d->addDay()) {
            $dStr = $d->format('Y-m-d');
            $chartData[$dStr] = [
                'hadir' => 0,
                'sakit' => 0,
                'izin' => 0,
                'alpha' => 0,
                'dinas_luar' => 0,
                'cuti' => 0
            ];
            foreach ($chartQuery as $cq) {
                if ($cq->date->format('Y-m-d') === $dStr) {
                    if (isset($chartData[$dStr][$cq->status])) {
                        $chartData[$dStr][$cq->status]++;
                    }
                }
            }
        }

        // Unit Stats (for Table)
        $unitStats = [];
        $schools = $isSuperAdmin ? School::where('is_active', true)->orderBy('name')->get() : School::where('id', $user->school_id)->get();
        foreach ($schools as $sch) {
            $empCount = Employee::where('school_id', $sch->id)->where('is_active', true)->where('employee_type', '!=', 'guru')->count();
            
            // Calculate expected attendances specifically for this school
            $schExpectedAttendances = 0;
            $schEmployees = $allActiveEmployees->where('school_id', $sch->id);
            foreach ($schEmployees as $emp) {
                $isTeacher = $emp->teacher !== null && $emp->teacher->schedules->count() > 0;
                $teachingDays = [];
                if ($isTeacher) {
                    $teachingDays = $emp->teacher->schedules->pluck('day_of_week')->map(function($day) {
                        return strtolower($day);
                    })->unique()->toArray();
                }
                for ($d = $startDateOfMonth->copy(); $d->lte($endDate); $d->addDay()) {
                    if ($isTeacher) {
                        if (in_array(strtolower($d->format('l')), $teachingDays)) {
                            $schExpectedAttendances++;
                        }
                    } else {
                        if (!in_array($d->dayOfWeek, [0, 6])) {
                            $schExpectedAttendances++;
                        }
                    }
                }
            }
            
            $schDaily = (clone $dailyStatsQuery)->whereHas('employee', fn($q) => $q->where('school_id', $sch->id))->get();
            $schHadir = $schDaily->where('status', 'hadir')->count();
            $schCumHadir = (clone $cumulativeStatsQuery)->whereHas('employee', fn($q) => $q->where('school_id', $sch->id))->where('status', 'hadir')->count();
            
            $presenceRate = $schExpectedAttendances > 0 ? round(($schCumHadir / $schExpectedAttendances) * 100, 1) : 0;
            
            $unitStats[] = (object) [
                'school_id' => $sch->id,
                'school_name' => $sch->name,
                'school' => (object) ['name' => $sch->name],
                'employees_count' => $empCount,
                'total_hadir' => $schCumHadir,
                'daily_present' => $schHadir,
                'z_days' => $workingDays,
                'expected_attendances' => $schExpectedAttendances,
                'presence_rate' => $presenceRate
            ];
        }

        if ($request->ajax() && $request->has('json')) {
            return response()->json([
                'dailyStats' => $dailyStats,
                'cumulativeStats' => $cumulativeStats,
                'unitStats' => $unitStats,
                'chartData' => $chartData
            ]);
        }

        return view('admin.employees.attendance.monitoring', compact(
            'date', 'schoolId', 'isSuperAdmin', 'schools',
            'dailyStats', 'cumulativeStats', 'chartData', 'unitStats'
        ));
    }

    public function destroy(EmployeeAttendance $attendance)
    {
        $attendance->delete();

        return redirect()->back()->with('success', 'Data absensi berhasil dihapus.');
    }
}

