<?php

namespace App\Http\Controllers\Yayasan;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\Employee;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\AcademicYear;
use App\Models\StudentBill;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Get current academic year (cached)
        $currentAcademicYear = Cache::remember('current_academic_year', 3600, function () {
            return AcademicYear::where('is_active', true)->first();
        });

        // Get all 3 schools (excluding yayasan)
        $schools = School::schoolsOnly()->where('is_active', true)->get();

        // Get yayasan record
        $yayasan = School::yayasanOnly()->first();

        // Aggregate statistics across all schools
        $schoolIds = $schools->pluck('id');

        // Total siswa aktif ber-rombel
        $totalStudents = StudentClass::whereHas('student', function ($q) use ($schoolIds) {
                $q->whereIn('school_id', $schoolIds)->where('status', 'aktif');
            })
            ->when($currentAcademicYear, function ($q) use ($currentAcademicYear) {
                $q->where('academic_year_id', $currentAcademicYear->id);
            })
            ->distinct('student_id')
            ->count('student_id');

        $totalEmployees = Employee::whereIn('school_id', $schoolIds)
            ->where('is_active', true)
            ->count();

        // Total Tagihan & Keuangan (Realisasi Yayasan)
        $totalBilled = StudentBill::whereIn('school_id', $schoolIds)
            ->when($currentAcademicYear, function ($q) use ($currentAcademicYear) {
                $q->where('academic_year_id', $currentAcademicYear->id);
            })
            ->sum('amount');

        $totalPaid = StudentBill::whereIn('school_id', $schoolIds)
            ->when($currentAcademicYear, function ($q) use ($currentAcademicYear) {
                $q->where('academic_year_id', $currentAcademicYear->id);
            })
            ->sum('paid_amount');

        $totalUnpaid = max(0, $totalBilled - $totalPaid);
        $realizationRate = $totalBilled > 0 ? round(($totalPaid / $totalBilled) * 100, 1) : 0;

        $stats = [
            'total_schools'      => $schools->count(),
            'total_students'     => $totalStudents,
            'total_employees'    => $totalEmployees,
            'total_billed'       => $totalBilled,
            'total_paid'         => $totalPaid,
            'total_unpaid'       => $totalUnpaid,
            'realization_rate'   => $realizationRate,
        ];

        // Per-school summary & chart data compilation
        $calendarService = app(\App\Services\EducationalCalendarService::class);
        
        $chartSchools = [];
        $chartStudents = [];
        $chartTeachers = [];
        $chartStaff = [];
        $chartBilled = [];
        $chartPaid = [];
        $chartUnpaid = [];

        $totalMale = 0;
        $totalFemale = 0;

        $schoolSummaries = $schools->map(function ($school) use ($currentAcademicYear, $calendarService, &$chartSchools, &$chartStudents, &$chartTeachers, &$chartStaff, &$chartBilled, &$chartPaid, &$chartUnpaid, &$totalMale, &$totalFemale) {
            $activeDays = $currentAcademicYear ? $calendarService->calculateActiveDays($school, $currentAcademicYear) : 0;

            // Jumlah siswa aktif ber-rombel per sekolah
            $studentQuery = StudentClass::whereHas('student', function ($q) use ($school) {
                    $q->where('school_id', $school->id)->where('status', 'aktif');
                })
                ->when($currentAcademicYear, function ($q) use ($currentAcademicYear) {
                    $q->where('academic_year_id', $currentAcademicYear->id);
                });

            $studentCount = (clone $studentQuery)->distinct('student_id')->count('student_id');

            // Gender breakdown
            $maleCount = StudentClass::whereHas('student', function ($q) use ($school) {
                    $q->where('school_id', $school->id)->where('status', 'aktif')->whereIn('gender', ['L', 'l', 'Laki-laki', 'Male']);
                })
                ->when($currentAcademicYear, function ($q) use ($currentAcademicYear) {
                    $q->where('academic_year_id', $currentAcademicYear->id);
                })
                ->distinct('student_id')
                ->count('student_id');

            $femaleCount = max(0, $studentCount - $maleCount);
            $totalMale += $maleCount;
            $totalFemale += $femaleCount;

            // Employees breakdown (Guru vs Staf)
            $totalEmp = Employee::where('school_id', $school->id)->where('is_active', true)->count();
            
            // Guru count
            $teacherCount = Employee::where('school_id', $school->id)
                ->where('is_active', true)
                ->where(function($q) {
                    $q->whereRaw('LOWER(employee_type) LIKE ?', ['%guru%'])
                      ->orWhereRaw('LOWER(employee_type) LIKE ?', ['%teacher%']);
                })
                ->count();

            // If teacher_count is 0 but we have Employee records, count from Teacher model or default split
            if ($teacherCount == 0 && $totalEmp > 0) {
                $teacherCount = \App\Models\Teacher::where('school_id', $school->id)->count();
                if ($teacherCount == 0) {
                    $teacherCount = (int) round($totalEmp * 0.75); // estimated 75% teachers
                }
            }

            $staffCount = max(0, $totalEmp - $teacherCount);

            // Financials per school
            $schoolBilled = (float) StudentBill::where('school_id', $school->id)
                ->when($currentAcademicYear, function ($q) use ($currentAcademicYear) {
                    $q->where('academic_year_id', $currentAcademicYear->id);
                })
                ->sum('amount');

            $schoolPaid = (float) StudentBill::where('school_id', $school->id)
                ->when($currentAcademicYear, function ($q) use ($currentAcademicYear) {
                    $q->where('academic_year_id', $currentAcademicYear->id);
                })
                ->sum('paid_amount');

            $schoolUnpaid = max(0, $schoolBilled - $schoolPaid);

            // Push to chart arrays
            $chartSchools[]  = $school->name;
            $chartStudents[] = $studentCount;
            $chartTeachers[] = $teacherCount;
            $chartStaff[]    = $staffCount;
            $chartBilled[]   = $schoolBilled;
            $chartPaid[]     = $schoolPaid;
            $chartUnpaid[]   = $schoolUnpaid;

            return [
                'id'             => $school->id,
                'name'           => $school->name,
                'type'           => strtoupper($school->type),
                'student_count'  => $studentCount,
                'male_students'   => $maleCount,
                'female_students' => $femaleCount,
                'employee_count' => $totalEmp,
                'teacher_count'  => $teacherCount,
                'staff_count'    => $staffCount,
                'billed'         => $schoolBilled,
                'paid'           => $schoolPaid,
                'unpaid'         => $schoolUnpaid,
                'active_days'    => $activeDays,
            ];
        });

        $chartData = [
            'schools'        => $chartSchools,
            'students'       => $chartStudents,
            'teachers'       => $chartTeachers,
            'staff'          => $chartStaff,
            'billed'         => $chartBilled,
            'paid'           => $chartPaid,
            'unpaid'         => $chartUnpaid,
            'total_male'     => $totalMale,
            'total_female'   => $totalFemale,
        ];

        return view('yayasan.dashboard', compact(
            'stats',
            'schools',
            'schoolSummaries',
            'chartData',
            'currentAcademicYear',
            'yayasan'
        ));
    }
}
