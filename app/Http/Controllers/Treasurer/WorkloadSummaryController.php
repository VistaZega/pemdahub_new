<?php

namespace App\Http\Controllers\Treasurer;

use App\Http\Controllers\Controller;
use App\Models\Employee;
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
     * Salary report (all employees in treasurer's school) - READ ONLY
     */
    public function salaryReport(Request $request)
    {
        $user = auth()->user();
        $schoolId = $user->school_id; // Always force treasurer's own school ID
        
        $yearId = $request->get('academic_year_id', AcademicYear::where('is_active', true)->first()?->id);
        $semesterId = $request->get('semester_id', Semester::where('is_active', true)->first()?->id);

        $school = School::findOrFail($schoolId);
        $schoolLevel = $school->type ?? 'SMA';
            
        $academicYears = AcademicYear::orderByDesc('year')->get();
        $semesters = Semester::orderBy('id')->get();

        $employees = collect();
        $salaryData = [];
        $totalGaji = 0;

        if ($yearId && $semesterId) {
            $year = AcademicYear::findOrFail($yearId);
            $semester = Semester::findOrFail($semesterId);

            $employees = Employee::with(['school', 'teacher', 'activePositions' => function ($q) use ($yearId) {
                    $q->wherePivot('academic_year_id', $yearId);
                }])
                ->where('school_id', $schoolId)
                ->where('is_active', true)
                ->addSelect([
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
                $salary = $this->service->calculateFullSalary($emp, $year, $semester, $schoolLevel);
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

        return view('treasurer.workload.salary-report', compact(
            'school', 'academicYears', 'semesters',
            'schoolId', 'yearId', 'semesterId',
            'employees', 'salaryData', 'totalGaji'
        ));
    }

    /**
     * Export salary report as CSV - READ ONLY
     */
    public function exportSalaryReport(Request $request)
    {
        $user = auth()->user();
        $schoolId = $user->school_id; // Always force treasurer's own school ID
        $yearId = $request->get('academic_year_id');
        $semesterId = $request->get('semester_id');

        if (!$yearId || !$semesterId) {
            return back()->with('error', 'Pilih tahun ajaran dan semester terlebih dahulu.');
        }

        $year = AcademicYear::findOrFail($yearId);
        $semester = Semester::findOrFail($semesterId);
        $school = School::findOrFail($schoolId);
        $schoolLevel = $school->type ?? 'SMA';

        $employees = Employee::where('school_id', $schoolId)
            ->where('is_active', true)
            ->select('employees.*')
            ->addSelect([
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
            $salaryMap[$emp->id] = $this->service->calculateFullSalary($emp, $year, $semester, $schoolLevel);
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
                    $emp->full_name,
                    $emp->employee_code ?? '-',
                    strtoupper($emp->employment_status),
                    $salary['gaji_pokok'],
                    $salary['tunjangan_jabatan'],
                    $salary['honor_mengajar'],
                    $salary['jam_mengajar'],
                    $salary['jam_wajib'],
                    $salary['jam_honor'],
                    $salary['tunjangan_keluarga'],
                    $salary['tunjangan_anak'],
                    $salary['tunjangan_beras'],
                    $salary['thp']
                ], ';');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Salary detail for an employee - READ ONLY
     */
    public function salaryDetail(Request $request, Employee $employee)
    {
        $user = auth()->user();
        
        // Ensure treasurer can only access their own school employees
        if ($employee->school_id !== $user->school_id) {
            abort(403, 'Unauthorized action.');
        }

        $year = AcademicYear::findOrFail($request->get('academic_year_id', AcademicYear::where('is_active', true)->first()?->id));
        $semester = Semester::findOrFail($request->get('semester_id', Semester::where('is_active', true)->first()?->id));

        $schoolLevel = $employee->school?->type ?? 'SMA';
        $slipData = $this->service->generateSalarySlip($employee, $year, $semester, $schoolLevel);

        $workload = \App\Models\EmployeeWorkloadSummary::where('employee_id', $employee->id)
            ->where('academic_year_id', $year->id)
            ->where('semester_id', $semester->id)
            ->first();

        return view('treasurer.workload.salary-detail', compact('slipData', 'workload', 'employee', 'year', 'semester'));
    }

    /**
     * Salary slip print view (HTML) - READ ONLY
     */
    public function salarySlip(Request $request, Employee $employee)
    {
        $user = auth()->user();
        
        // Ensure treasurer can only access their own school employees
        if ($employee->school_id !== $user->school_id) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester_id' => 'required|exists:semesters,id',
        ]);

        $year = AcademicYear::findOrFail($request->get('academic_year_id'));
        $semester = Semester::findOrFail($request->get('semester_id'));

        $schoolLevel = $employee->school?->type ?? 'SMA';
        $slipData = $this->service->generateSalarySlip($employee, $year, $semester, $schoolLevel);

        return view('treasurer.workload.salary-slip', compact('slipData', 'employee', 'year', 'semester'));
    }

    /**
     * Salary slip PDF download - READ ONLY
     */
    public function salarySlipPdf(Request $request, Employee $employee)
    {
        $user = auth()->user();
        
        // Ensure treasurer can only access their own school employees
        if ($employee->school_id !== $user->school_id) {
            abort(403, 'Unauthorized action.');
        }

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
}
