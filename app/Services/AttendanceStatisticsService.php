<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AcademicYear;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class AttendanceStatisticsService
{
    /**
     * Calculate Z (Actual Training/School Days) from start of year to end date.
     * Z = X (All days) - Y (Sat, Sun, Holidays marked 'is_holiday' = true)
     */
    public function calculateZ($startDate, $endDate, $classroomId = null, $schoolId = null)
    {
        $startDate = ($startDate instanceof Carbon) ? $startDate : Carbon::parse($startDate);
        $endDate = ($endDate instanceof Carbon) ? $endDate : Carbon::parse($endDate);
        
        if ($startDate->gt($endDate)) {
            return 0;
        }

        // Determine school and academic year context
        $school = null;
        $academicYear = AcademicYear::where('is_active', true)->first();

        if ($classroomId) {
            $classroom = \App\Models\Classroom::find($classroomId);
            if ($classroom) {
                $school = $classroom->school;
            }
        } elseif ($schoolId) {
            $school = \App\Models\School::find($schoolId);
        }

        if (!$school || !$academicYear) {
            // Fallback to simple calculation if context is missing
            $period = CarbonPeriod::create($startDate, $endDate);
            $z = 0;
            foreach ($period as $date) {
                if (!$date->isWeekend()) $z++;
            }
            return $z;
        }

        $calendarService = app(\App\Services\EducationalCalendarService::class);
        return $calendarService->calculateActiveDaysBetween($school, $academicYear, $startDate, $endDate);
    }

    /**
     * Calculate cumulative attendance rate for a classroom
     */
    public function getClassroomCumulativeRate($classroomId, $endDate)
    {
        $classroom = \App\Models\Classroom::with('school.academicYears')->find($classroomId);
        if (!$classroom) return 0;

        $ay = $classroom->school?->currentAcademicYear() 
              ?? AcademicYear::where('is_active', true)->first();
        
        if (!$ay) return 0;

        $startDate = $ay->start_date;
        $parsedEndDate = Carbon::parse($endDate);
        if ($startDate->gt($parsedEndDate)) {
            $startDate = $parsedEndDate;
        }

        $z = $this->calculateZ($startDate, $parsedEndDate, $classroomId);
        if ($z <= 0) return 0;

        $studentCount = $classroom->students()->where('student_classes.status', 'aktif')->count();
        if ($studentCount <= 0) return 0;

        $totalHadir = Attendance::where('classroom_id', $classroomId)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $parsedEndDate->format('Y-m-d')])
            ->whereIn('status', ['hadir', 'terlambat'])
            ->select(\Illuminate\Support\Facades\DB::raw('count(DISTINCT CONCAT(student_id, "-", date)) as count'))
            ->value('count') ?? 0;

        // Formula: (Total Hadir / (Total Siswa * Z)) * 100
        $rate = ($studentCount * $z) > 0 ? ($totalHadir / ($studentCount * $z)) * 100 : 0;
        return round(min(100, $rate), 1);
    }
}
