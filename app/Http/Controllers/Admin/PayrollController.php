<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\School;
use App\Models\Setting;
use App\Services\EmployeeAssignmentService;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function __construct(private EmployeeAssignmentService $service) {}

    /**
     * Slip Gaji - Search for employee to view salary slip
     */
    public function slipSearch(Request $request)
    {
        $academicYears = AcademicYear::orderByDesc('year')->get();
        $semesters = Semester::orderBy('id')->get();
        $schools = School::where('is_active', true)->orderBy('name')->get();

        $activeYear = AcademicYear::where('is_active', true)->first();
        $activeSemester = Semester::where('is_active', true)->first();

        $yearId = $request->get('academic_year_id', $activeYear?->id);
        $semesterId = $request->get('semester_id', $activeSemester?->id);
        $schoolId = $request->get('school_id');
        $search = $request->get('q');

        $employees = collect();

        if ($schoolId) {
            $query = Employee::with(['school', 'activePositions' => function ($q) use ($yearId) {
                $q->wherePivot('academic_year_id', $yearId);
            }, 'teacher'])
                ->where('school_id', $schoolId)
                ->where('is_active', true);

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                      ->orWhere('employee_code', 'like', "%{$search}%");
                });
            }

            $employees = $query->orderBy('full_name')->paginate(20)->withQueryString();
        }

        return view('admin.payroll.slip-search', compact(
            'academicYears', 'semesters', 'schools',
            'yearId', 'semesterId', 'schoolId', 'search', 'employees'
        ));
    }

    /**
     * Pengaturan Gaji - Display salary formula settings
     */
    public function settings()
    {
        // Get saved settings from DB
        $savedSettings = Setting::where('group', 'salary_formula')
            ->pluck('value', 'key')
            ->toArray();

        // Merge with defaults
        $defaults = EmployeeAssignmentService::DEFAULT_FORMULAS;
        $jamHonorDefaults = EmployeeAssignmentService::DEFAULT_JAM_HONOR;

        // Build settings array with current values
        $settings = [
            // Tunjangan
            'tunjangan_keluarga_persen' => $savedSettings['tunjangan_keluarga_persen'] ?? $defaults['tunjangan_keluarga_persen'],
            'tunjangan_anak_persen' => $savedSettings['tunjangan_anak_persen'] ?? $defaults['tunjangan_anak_persen'],
            'tunjangan_anak_max' => $savedSettings['tunjangan_anak_max'] ?? $defaults['tunjangan_anak_max'],
            'tunjangan_beras' => $savedSettings['tunjangan_beras'] ?? $defaults['tunjangan_beras'],

            // Honor Mengajar - SMP
            'jam_wajib_tetap_smp' => $savedSettings['jam_wajib_tetap_smp'] ?? $jamHonorDefaults['SMP']['jam_wajib_tetap'],
            'jam_wajib_honor_smp' => $savedSettings['jam_wajib_honor_smp'] ?? $jamHonorDefaults['SMP']['jam_wajib_honor'],
            'honor_tetap_smp' => $savedSettings['honor_tetap_smp'] ?? $jamHonorDefaults['SMP']['honor_tetap'],
            'honor_honorer_smp' => $savedSettings['honor_honorer_smp'] ?? $jamHonorDefaults['SMP']['honor_honorer'],
            'honor_percobaan_smp' => $savedSettings['honor_percobaan_smp'] ?? $jamHonorDefaults['SMP']['honor_percobaan'],

            // Honor Mengajar - SMA
            'jam_wajib_tetap_sma' => $savedSettings['jam_wajib_tetap_sma'] ?? $jamHonorDefaults['SMA']['jam_wajib_tetap'],
            'jam_wajib_honor_sma' => $savedSettings['jam_wajib_honor_sma'] ?? $jamHonorDefaults['SMA']['jam_wajib_honor'],
            'honor_tetap_sma' => $savedSettings['honor_tetap_sma'] ?? $jamHonorDefaults['SMA']['honor_tetap'],
            'honor_honorer_sma' => $savedSettings['honor_honorer_sma'] ?? $jamHonorDefaults['SMA']['honor_honorer'],
            'honor_percobaan_sma' => $savedSettings['honor_percobaan_sma'] ?? $jamHonorDefaults['SMA']['honor_percobaan'],

            // Honor Mengajar - SMK
            'jam_wajib_tetap_smk' => $savedSettings['jam_wajib_tetap_smk'] ?? $jamHonorDefaults['SMK']['jam_wajib_tetap'],
            'jam_wajib_honor_smk' => $savedSettings['jam_wajib_honor_smk'] ?? $jamHonorDefaults['SMK']['jam_wajib_honor'],
            'honor_tetap_smk' => $savedSettings['honor_tetap_smk'] ?? $jamHonorDefaults['SMK']['honor_tetap'],
            'honor_honorer_smk' => $savedSettings['honor_honorer_smk'] ?? $jamHonorDefaults['SMK']['honor_honorer'],
            'honor_percobaan_smk' => $savedSettings['honor_percobaan_smk'] ?? $jamHonorDefaults['SMK']['honor_percobaan'],

            // Honor Pembimbing PKL
            'pkl_honor_rate' => $savedSettings['pkl_honor_rate'] ?? EmployeeAssignmentService::DEFAULT_PKL_HONOR_RATE,

            // Potongan BPJS
            'bpjs_kesehatan_persen' => $savedSettings['bpjs_kesehatan_persen'] ?? 1.0,
            'bpjs_ketenagakerjaan_persen' => $savedSettings['bpjs_ketenagakerjaan_persen'] ?? 2.0,
        ];

        return view('admin.payroll.settings', compact('settings'));
    }

    /**
     * Update salary formula settings
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            // Tunjangan
            'tunjangan_keluarga_persen' => 'required|numeric|min:0|max:100',
            'tunjangan_anak_persen' => 'required|numeric|min:0|max:100',
            'tunjangan_anak_max' => 'required|integer|min:0|max:10',
            'tunjangan_beras' => 'required|numeric|min:0',

            // Honor SMP
            'jam_wajib_tetap_smp' => 'required|integer|min:0',
            'jam_wajib_honor_smp' => 'required|numeric|min:0',
            'honor_tetap_smp' => 'required|numeric|min:0',
            'honor_honorer_smp' => 'required|numeric|min:0',
            'honor_percobaan_smp' => 'required|numeric|min:0',

            // Honor SMA
            'jam_wajib_tetap_sma' => 'required|integer|min:0',
            'jam_wajib_honor_sma' => 'required|numeric|min:0',
            'honor_tetap_sma' => 'required|numeric|min:0',
            'honor_honorer_sma' => 'required|numeric|min:0',
            'honor_percobaan_sma' => 'required|numeric|min:0',

            // Honor SMK
            'jam_wajib_tetap_smk' => 'required|integer|min:0',
            'jam_wajib_honor_smk' => 'required|numeric|min:0',
            'honor_tetap_smk' => 'required|numeric|min:0',
            'honor_honorer_smk' => 'required|numeric|min:0',
            'honor_percobaan_smk' => 'required|numeric|min:0',

            // Honor PKL
            'pkl_honor_rate' => 'required|numeric|min:0',

            // Potongan
            'bpjs_kesehatan_persen' => 'required|numeric|min:0|max:100',
            'bpjs_ketenagakerjaan_persen' => 'required|numeric|min:0|max:100',
        ]);

        foreach ($validated as $key => $value) {
            Setting::setValue($key, $value, is_int($value) ? 'integer' : 'string', 'salary_formula');
        }

        // Recalculate workload summaries for active employees in active academic year & semester
        $activeYear = \App\Models\AcademicYear::where('is_active', true)->first();
        $activeSemester = \App\Models\Semester::where('is_active', true)->first();
        if ($activeYear && $activeSemester) {
            $assignmentService = app(\App\Services\EmployeeAssignmentService::class);
            $employees = \App\Models\Employee::where('is_active', true)->get();
            foreach ($employees as $emp) {
                try {
                    $assignmentService->calculateWorkload($emp, $activeYear, $activeSemester);
                } catch (\Exception $e) {
                    // Ignore individual failures so settings save completes
                }
            }
        }

        return back()->with('success', 'Pengaturan gaji berhasil disimpan dan beban kerja telah diperbarui.');
    }
}

