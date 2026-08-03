<?php

namespace App\Http\Controllers\Treasurer;

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
    /**
     * Slip search (Read-Only)
     */
    public function slipSearch(Request $request)
    {
        $user = auth()->user();
        $schoolId = $user->school_id; // Always force treasurer's own school ID

        $academicYears = AcademicYear::orderByDesc('year')->get();
        $semesters = Semester::orderBy('id')->get();
        $school = School::findOrFail($schoolId);

        $activeYear = AcademicYear::where('is_active', true)->first();
        $activeSemester = Semester::where('is_active', true)->first();

        $yearId = $request->get('academic_year_id', $activeYear?->id);
        $semesterId = $request->get('semester_id', $activeSemester?->id);
        $search = $request->get('q');

        $employees = collect();

        if ($schoolId && $yearId && $semesterId) {
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

        return view('treasurer.payroll.slip-search', compact(
            'academicYears', 'semesters', 'school',
            'yearId', 'semesterId', 'schoolId', 'search', 'employees'
        ));
    }

    /**
     * Display salary formula settings (Read-Only)
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

            // Potongan BPJS
            'bpjs_kesehatan_persen' => $savedSettings['bpjs_kesehatan_persen'] ?? 1.0,
            'bpjs_ketenagakerjaan_persen' => $savedSettings['bpjs_ketenagakerjaan_persen'] ?? 2.0,
        ];

        return view('treasurer.payroll.settings', compact('settings'));
    }
}
