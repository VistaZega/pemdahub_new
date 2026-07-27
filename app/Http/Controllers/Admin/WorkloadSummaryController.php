<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeWorkloadSummary;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\School;
use App\Services\EmployeeAssignmentService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class WorkloadSummaryController extends Controller
{
    public function __construct(private EmployeeAssignmentService $service) {}

    /**
     * Workload summary listing
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $academicYears = AcademicYear::orderByDesc('year')->get();
        $semesters = Semester::orderBy('id')->get();

        // Filter schools for dropdown
        $schools = $user->isSuperAdmin() 
            ? School::where('is_active', true)->orderBy('name')->get()
            : School::where('id', $user->school_id)->get();

        $activeYear = AcademicYear::where('is_active', true)->first();
        $activeSemester = Semester::where('is_active', true)->first();

        $yearId = $request->get('academic_year_id', $activeYear?->id);
        $semesterId = $request->get('semester_id', $activeSemester?->id);
        
        $schoolId = $request->get('school_id');
        if (!$user->isSuperAdmin()) {
            $schoolId = $user->school_id;
        }

        // Ensure summaries exist for all active employees (missing ones)
        if ($yearId && $semesterId) {
            $this->ensureSummariesExist($yearId, $semesterId, $schoolId);
        }

        $query = EmployeeWorkloadSummary::with([
            'employee.school', 
            'employee.teacher', 
            'employee.activePositions' => function ($q) use ($yearId) {
                $q->wherePivot('academic_year_id', $yearId);
            }, 
            'academicYear', 
            'semester'
        ])
            ->whereHas('employee')
            ->select('employee_workload_summaries.*')
            ->addSelect([
                'emp_type_rank' => function ($q) {
                    $q->selectRaw("CASE WHEN employee_type = 'guru' THEN 1 ELSE 2 END")
                        ->from('employees')
                        ->whereColumn('id', 'employee_workload_summaries.employee_id');
                },
                'min_position_level' => function ($q) use ($yearId) {
                    $q->selectRaw('COALESCE(MIN(positions.position_level), 999)')
                        ->from('employee_positions')
                        ->join('positions', 'employee_positions.position_id', '=', 'positions.id')
                        ->whereColumn('employee_positions.employee_id', 'employee_workload_summaries.employee_id')
                        ->where('employee_positions.academic_year_id', $yearId)
                        ->whereNull('employee_positions.end_date');
                },
                'position_name_rank' => function ($q) use ($yearId) {
                    $q->selectRaw("COALESCE(MIN(CASE 
                        WHEN positions.position_name LIKE 'Ketua Yayasan%' THEN 1
                        WHEN positions.position_name LIKE 'Kepala Sekolah%' OR positions.position_name LIKE 'Kepsek%' THEN 2
                        WHEN positions.position_name LIKE 'Wakil Kepala Sekolah%' OR positions.position_name LIKE 'Wakasek%' THEN 3
                        WHEN positions.position_name LIKE 'Pembantu Kepala Sekolah%' OR positions.position_name LIKE 'PKS%' THEN 4
                        WHEN positions.position_name LIKE 'Kepala Tata Usaha%' OR positions.position_name LIKE 'KTU%' THEN 5
                        WHEN positions.position_name LIKE 'Bendahara%' THEN 6
                        WHEN positions.position_name LIKE 'Kapro%' OR positions.position_name LIKE 'Kaprog%' THEN 7
                        WHEN positions.position_name LIKE 'Koordinator%' THEN 8
                        WHEN positions.position_name LIKE 'Wali Kelas%' THEN 9
                        ELSE 999 END), 999)")
                        ->from('employee_positions')
                        ->join('positions', 'employee_positions.position_id', '=', 'positions.id')
                        ->whereColumn('employee_positions.employee_id', 'employee_workload_summaries.employee_id')
                        ->where('employee_positions.academic_year_id', $yearId)
                        ->whereNull('employee_positions.end_date');
                },
                'employee_name' => function ($q) {
                    $q->select('full_name')
                        ->from('employees')
                        ->whereColumn('id', 'employee_workload_summaries.employee_id');
                },
                'emp_status_rank' => function ($q) {
                    $q->selectRaw("CASE 
                        WHEN LOWER(employment_status) = 'pns' THEN 1 
                        WHEN LOWER(employment_status) = 'gty' THEN 2 
                        WHEN LOWER(employment_status) = 'yayasan' THEN 3
                        WHEN LOWER(employment_status) = 'honorer' THEN 4 
                        WHEN LOWER(employment_status) = 'kontrak' THEN 5 
                        ELSE 6 END")
                        ->from('employees')
                        ->whereColumn('id', 'employee_workload_summaries.employee_id');
                }
            ])
            ->where('employee_workload_summaries.academic_year_id', $yearId)
            ->where('employee_workload_summaries.semester_id', $semesterId);

        if ($schoolId) {
            $query->where(function ($q) use ($schoolId, $yearId) {
                $q->whereHas('employee', fn($empQ) => $empQ->where('school_id', $schoolId))
                  ->orWhereHas('employee.activePositions', function ($posQ) use ($schoolId, $yearId) {
                      $posQ->where('positions.school_id', $schoolId)
                           ->where('employee_positions.academic_year_id', $yearId);
                  })
                  ->orWhereHas('employee.teacher.teachingAssignments', function ($teachQ) use ($schoolId, $yearId) {
                      $teachQ->where('academic_year_id', $yearId)
                             ->where('is_active', true)
                             ->whereHas('classroom', fn($cQ) => $cQ->where('school_id', $schoolId));
                  });
            });
        }

        $summaries = $query->orderBy('position_name_rank', 'asc')
            ->orderBy('total_compensation', 'desc')
            ->orderBy('total_position_allowance', 'desc')
            ->orderBy('total_allowance', 'desc')
            ->orderBy('emp_status_rank', 'asc')
            ->orderBy('employee_name', 'asc')
            ->paginate(50)->withQueryString();

        // LIVE MODE: Recalculate salary for current page results to ensure data is fresh
        if ($yearId && $semesterId) {
            $year = AcademicYear::find($yearId);
            $semester = Semester::find($semesterId);
            foreach ($summaries as $summary) {
                if ($summary->employee) {
                    $this->service->calculateWorkload($summary->employee, $year, $semester);
                }
            }
            // Re-load summaries to get updated data from DB
            $summaries = $query->paginate(50)->withQueryString();
        }

        // Totals (use base query without joins for simplicity if possible, or be explicit)
        $totalsQuery = EmployeeWorkloadSummary::where('academic_year_id', $yearId)
            ->where('semester_id', $semesterId)
            ->whereHas('employee');
        
        if ($schoolId) {
            $totalsQuery->whereHas('employee', fn($q) => $q->where('school_id', $schoolId));
        }

        $totals = [
            'total_compensation' => $totalsQuery->sum('total_compensation'),
            'family_allowance' => $totalsQuery->sum('family_allowance'),
            'child_allowance' => $totalsQuery->sum('child_allowance'),
            'rice_allowance' => $totalsQuery->sum('rice_allowance'),
            'count' => $totalsQuery->count(),
        ];

        return view('admin.workload.index', compact(
            'summaries', 'academicYears', 'semesters', 'schools',
            'yearId', 'semesterId', 'schoolId', 'totals'
        ));
    }

    /**
     * Calculate workload for a single employee
     */
    public function calculate(Request $request, Employee $employee)
    {
        $yearId = $request->academic_year_id ?? AcademicYear::where('is_active', true)->first()?->id;
        $semesterId = $request->semester_id ?? Semester::where('is_active', true)->first()?->id;

        if (!$yearId || !$semesterId) {
            return back()->with('error', 'Tahun ajaran atau semester aktif tidak ditemukan.');
        }

        $year = AcademicYear::findOrFail($yearId);
        $semester = Semester::findOrFail($semesterId);

        $this->service->calculateWorkload($employee, $year, $semester);

        return back()->with('success', "Beban kerja {$employee->full_name} berhasil dihitung.");
    }

    /**
     * Bulk calculate for all employees in a school
     */
    public function bulkCalculate(Request $request)
    {
        $request->validate([
            'school_id' => 'required|exists:schools,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester_id' => 'required|exists:semesters,id',
        ]);

        $year = AcademicYear::findOrFail($request->academic_year_id);
        $semester = Semester::findOrFail($request->semester_id);

        $results = $this->service->bulkCalculateWorkload($request->school_id, $year, $semester);

        return back()->with('success', "Berhasil menghitung {$results['success']} pegawai. " .
            (count($results['errors']) > 0 ? count($results['errors']) . ' error.' : ''));
    }

    /**
     * Salary detail for an employee
     */
    public function salaryDetail(Request $request, Employee $employee)
    {
        $year = AcademicYear::findOrFail($request->get('academic_year_id', AcademicYear::where('is_active', true)->first()?->id));
        $semester = Semester::findOrFail($request->get('semester_id', Semester::where('is_active', true)->first()?->id));

        $schoolLevel = $employee->school?->type ?? 'SMA';
        $slipData = $this->service->generateSalarySlip($employee, $year, $semester, $schoolLevel);

        $workload = EmployeeWorkloadSummary::where('employee_id', $employee->id)
            ->where('academic_year_id', $year->id)
            ->where('semester_id', $semester->id)
            ->first();

        return view('admin.workload.salary-detail', compact('slipData', 'workload', 'employee', 'year', 'semester'));
    }

    /**
     * Salary slip print view (HTML)
     */
    public function salarySlip(Request $request, Employee $employee)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester_id' => 'required|exists:semesters,id',
        ]);

        $year = AcademicYear::findOrFail($request->get('academic_year_id'));
        $semester = Semester::findOrFail($request->get('semester_id'));

        $schoolLevel = $employee->school?->type ?? 'SMA';
        $slipData = $this->service->generateSalarySlip($employee, $year, $semester, $schoolLevel);

        return view('admin.workload.salary-slip', compact('slipData', 'employee', 'year', 'semester'));
    }

    /**
     * Salary slip PDF download
     */
    public function salarySlipPdf(Request $request, Employee $employee)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester_id' => 'required|exists:semesters,id',
        ]);

        $year = AcademicYear::findOrFail($request->get('academic_year_id'));
        $semester = Semester::findOrFail($request->get('semester_id'));

        $schoolLevel = $employee->school?->type ?? 'SMA';
        $slipData = $this->service->generateSalarySlip($employee, $year, $semester, $schoolLevel);

        $pdf = Pdf::loadView('admin.workload.salary-slip-pdf', compact('slipData', 'employee', 'year', 'semester'));
        
        $filename = 'Slip_Gaji_' . str_replace(' ', '_', $employee->full_name) . '_' . date('Ymd') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Confirm workload summary
     */
    public function confirm(EmployeeWorkloadSummary $workload)
    {
        $this->service->confirmWorkload($workload, auth()->id());
        return back()->with('success', 'Beban kerja berhasil dikonfirmasi.');
    }

    /**
     * Lock workload summary
     */
    public function lock(EmployeeWorkloadSummary $workload)
    {
        $this->service->lockWorkload($workload);
        return back()->with('success', 'Beban kerja berhasil dikunci.');
    }

    /**
     * Salary report (all employees in a school)
     */
    public function salaryReport(Request $request)
    {
        $user = auth()->user();
        $schoolId = $request->get('school_id');
        if (!$user->isSuperAdmin()) {
            $schoolId = $user->school_id;
        }
        
        $yearId = $request->get('academic_year_id', AcademicYear::where('is_active', true)->first()?->id);
        $semesterId = $request->get('semester_id', Semester::where('is_active', true)->first()?->id);

        $schools = $user->isSuperAdmin() 
            ? School::where('is_active', true)->orderBy('name')->get()
            : School::where('id', $user->school_id)->get();
            
        $academicYears = AcademicYear::orderByDesc('year')->get();
        $semesters = Semester::orderBy('id')->get();

        $employees = collect();
        $salaryData = [];
        $totalGaji = 0;

        if ($schoolId && $yearId && $semesterId) {
            $year = AcademicYear::findOrFail($yearId);
            $semester = Semester::findOrFail($semesterId);
            $school = School::findOrFail($schoolId);
            $schoolLevel = $school->type ?? 'SMA';

            $employees = Employee::with(['school', 'teacher', 'activePositions' => function ($q) use ($yearId) {
                    $q->wherePivot('academic_year_id', $yearId);
                }])
                ->where('school_id', $schoolId)
                ->where('is_active', true)
                ->select('employees.*')
                ->addSelect([
                    'emp_type_rank' => \Illuminate\Support\Facades\DB::raw("CASE WHEN employees.employee_type = 'guru' THEN 1 ELSE 2 END as emp_type_rank"),
                    'min_position_level' => function ($q) use ($yearId) {
                        $q->selectRaw('COALESCE(MIN(positions.position_level), 999)')
                            ->from('employee_positions')
                            ->join('positions', 'employee_positions.position_id', '=', 'positions.id')
                            ->whereColumn('employee_positions.employee_id', 'employees.id')
                            ->where('employee_positions.academic_year_id', $yearId)
                            ->whereNull('employee_positions.end_date');
                    },
                    'position_name_rank' => function ($q) use ($yearId) {
                        $q->selectRaw("COALESCE(MIN(CASE 
                            WHEN positions.position_name LIKE 'Ketua Yayasan%' THEN 1
                            WHEN positions.position_name LIKE 'Kepala Sekolah%' OR positions.position_name LIKE 'Kepsek%' THEN 2
                            WHEN positions.position_name LIKE 'Wakil Kepala Sekolah%' OR positions.position_name LIKE 'Wakasek%' THEN 3
                            WHEN positions.position_name LIKE 'Pembantu Kepala Sekolah%' OR positions.position_name LIKE 'PKS%' THEN 4
                            WHEN positions.position_name LIKE 'Kepala Tata Usaha%' OR positions.position_name LIKE 'KTU%' THEN 5
                            WHEN positions.position_name LIKE 'Bendahara%' THEN 6
                            WHEN positions.position_name LIKE 'Kapro%' OR positions.position_name LIKE 'Kaprog%' THEN 7
                            WHEN positions.position_name LIKE 'Koordinator%' THEN 8
                            WHEN positions.position_name LIKE 'Wali Kelas%' THEN 9
                            ELSE 999 END), 999)")
                            ->from('employee_positions')
                            ->join('positions', 'employee_positions.position_id', '=', 'positions.id')
                            ->whereColumn('employee_positions.employee_id', 'employees.id')
                            ->where('employee_positions.academic_year_id', $yearId)
                            ->whereNull('employee_positions.end_date');
                    },
                    'emp_status_rank' => function ($q) {
                        $q->selectRaw("CASE 
                            WHEN LOWER(employment_status) = 'pns' THEN 1 
                            WHEN LOWER(employment_status) = 'gty' THEN 2 
                            WHEN LOWER(employment_status) = 'yayasan' THEN 3
                            WHEN LOWER(employment_status) = 'honorer' THEN 4 
                            WHEN LOWER(employment_status) = 'kontrak' THEN 5 
                            ELSE 6 END")
                            ->from('employees as emp_inner')
                            ->whereColumn('emp_inner.id', 'employees.id');
                    }
                ])
                ->get();

            foreach ($employees as $emp) {
                $salary = $this->service->calculateFullSalary($emp, $year, $semester, $schoolLevel, $schoolId);
                $salaryData[$emp->id] = $salary;
                $totalGaji += $salary['thp'];
            }

            $employees = $employees->sortBy([
                ['position_name_rank', 'asc'],
                [fn($a, $b) => ($salaryData[$b->id]['thp'] ?? 0) <=> ($salaryData[$a->id]['thp'] ?? 0)],
                [fn($a, $b) => ($salaryData[$b->id]['tunjangan_jabatan'] ?? 0) <=> ($salaryData[$a->id]['tunjangan_jabatan'] ?? 0)],
                [fn($a, $b) => ($salaryData[$b->id]['total_tunjangan'] ?? 0) <=> ($salaryData[$a->id]['total_tunjangan'] ?? 0)],
                ['emp_status_rank', 'asc'],
                ['full_name', 'asc'],
            ])->values();
        }

        return view('admin.workload.salary-report', compact(
            'schools', 'academicYears', 'semesters',
            'schoolId', 'yearId', 'semesterId',
            'employees', 'salaryData', 'totalGaji'
        ));
    }

    /**
     * Export salary report as CSV
     */
    public function exportSalaryReport(Request $request)
    {
        $schoolId = $request->get('school_id');
        $yearId = $request->get('academic_year_id');
        $semesterId = $request->get('semester_id');

        if (!$schoolId || !$yearId || !$semesterId) {
            return back()->with('error', 'Pilih sekolah, tahun ajaran, dan semester terlebih dahulu.');
        }

        $year = AcademicYear::findOrFail($yearId);
        $semester = Semester::findOrFail($semesterId);
        $school = School::findOrFail($schoolId);
        $schoolLevel = $school->type ?? 'SMA';

        $employees = Employee::where('school_id', $schoolId)
            ->where('is_active', true)
            ->select('employees.*')
            ->addSelect([
                'emp_type_rank' => \Illuminate\Support\Facades\DB::raw("CASE WHEN employees.employee_type = 'guru' THEN 1 ELSE 2 END as emp_type_rank"),
                'min_position_level' => function ($q) use ($yearId) {
                    $q->selectRaw('COALESCE(MIN(positions.position_level), 999)')
                        ->from('employee_positions')
                        ->join('positions', 'employee_positions.position_id', '=', 'positions.id')
                        ->whereColumn('employee_positions.employee_id', 'employees.id')
                        ->where('employee_positions.academic_year_id', $yearId)
                        ->whereNull('employee_positions.end_date');
                },
                'position_name_rank' => function ($q) use ($yearId) {
                    $q->selectRaw("COALESCE(MIN(CASE 
                        WHEN positions.position_name LIKE 'Ketua Yayasan%' THEN 1
                        WHEN positions.position_name LIKE 'Kepala Sekolah%' OR positions.position_name LIKE 'Kepsek%' THEN 2
                        WHEN positions.position_name LIKE 'Wakil Kepala Sekolah%' OR positions.position_name LIKE 'Wakasek%' THEN 3
                        WHEN positions.position_name LIKE 'Pembantu Kepala Sekolah%' OR positions.position_name LIKE 'PKS%' THEN 4
                        WHEN positions.position_name LIKE 'Kepala Tata Usaha%' OR positions.position_name LIKE 'KTU%' THEN 5
                        WHEN positions.position_name LIKE 'Bendahara%' THEN 6
                        WHEN positions.position_name LIKE 'Kapro%' OR positions.position_name LIKE 'Kaprog%' THEN 7
                        WHEN positions.position_name LIKE 'Koordinator%' THEN 8
                        WHEN positions.position_name LIKE 'Wali Kelas%' THEN 9
                        ELSE 999 END), 999)")
                        ->from('employee_positions')
                        ->join('positions', 'employee_positions.position_id', '=', 'positions.id')
                        ->whereColumn('employee_positions.employee_id', 'employees.id')
                        ->where('employee_positions.academic_year_id', $yearId)
                        ->whereNull('employee_positions.end_date');
                },
                'emp_status_rank' => function ($q) {
                    $q->selectRaw("CASE 
                        WHEN LOWER(employment_status) = 'pns' THEN 1 
                        WHEN LOWER(employment_status) = 'gty' THEN 2 
                        WHEN LOWER(employment_status) = 'yayasan' THEN 3
                        WHEN LOWER(employment_status) = 'honorer' THEN 4 
                        WHEN LOWER(employment_status) = 'kontrak' THEN 5 
                        ELSE 6 END")
                        ->from('employees as emp_inner')
                        ->whereColumn('emp_inner.id', 'employees.id');
                }
            ])
            ->get();

        $salaryMap = [];
        foreach ($employees as $emp) {
            $salaryMap[$emp->id] = $this->service->calculateFullSalary($emp, $year, $semester, $schoolLevel, $schoolId);
        }

        $employees = $employees->sortBy([
            ['position_name_rank', 'asc'],
            [fn($a, $b) => ($salaryMap[$b->id]['thp'] ?? 0) <=> ($salaryMap[$a->id]['thp'] ?? 0)],
            [fn($a, $b) => ($salaryMap[$b->id]['tunjangan_jabatan'] ?? 0) <=> ($salaryMap[$a->id]['tunjangan_jabatan'] ?? 0)],
            [fn($a, $b) => ($salaryMap[$b->id]['total_tunjangan'] ?? 0) <=> ($salaryMap[$a->id]['total_tunjangan'] ?? 0)],
            ['emp_status_rank', 'asc'],
            ['full_name', 'asc'],
        ])->values();

        $rawFilename = 'Laporan_Gaji_' . $school->name . '_' . $year->year . '_' . $semester->semester_name . '.csv';
        $filename = str_replace([' ', '/', '\\'], '_', $rawFilename);

        return response()->streamDownload(function () use ($employees, $year, $semester, $schoolLevel) {
            $handle = fopen('php://output', 'w');

            // BOM for Excel UTF-8
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header
            fputcsv($handle, [
                'No', 'Nama', 'Kode Pegawai', 'Status', 'Gaji Pokok',
                'Tunj. Jabatan', 'Honor Mengajar', 'Jam Mengajar', 'Jam Wajib', 'Jam Honor',
                'Tunj. Keluarga', 'Tunj. Anak', 'Tunj. Beras', 'THP'
            ], ';');

            $no = 1;
            foreach ($employees as $emp) {
                $salary = $this->service->calculateFullSalary($emp, $year, $semester, $schoolLevel);
                fputcsv($handle, [
                    $no++,
                    $salary['employee_name'],
                    $emp->employee_code ?? '-',
                    ucfirst($salary['employment_status'] ?? '-'),
                    $salary['gaji_pokok'],
                    $salary['tunjangan_jabatan'],
                    $salary['honor_mengajar'],
                    $salary['jam_mengajar'],
                    $salary['jam_wajib'],
                    $salary['jam_honor'],
                    $salary['tunjangan_keluarga'],
                    $salary['tunjangan_anak'],
                    $salary['tunjangan_beras'],
                    $salary['thp'],
                ], ';');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Helper to ensure all active employees have a summary record
     */
    private function ensureSummariesExist($yearId, $semesterId, $schoolId = null)
    {
        $year = AcademicYear::find($yearId);
        $semester = Semester::find($semesterId);
        if (!$year || !$semester) return;

        $employeeQuery = \App\Models\Employee::where('is_active', true);
        if ($schoolId) {
            $employeeQuery->where('school_id', $schoolId);
        }

        $existingIds = EmployeeWorkloadSummary::where('academic_year_id', $yearId)
            ->where('semester_id', $semesterId)
            ->pluck('employee_id')
            ->toArray();

        // Create missing summaries (limited to 50 at a time to avoid performance hits)
        $missing = $employeeQuery->whereNotIn('id', $existingIds)->limit(50)->get();
        foreach ($missing as $emp) {
            $this->service->calculateWorkload($emp, $year, $semester);
        }
    }
}

