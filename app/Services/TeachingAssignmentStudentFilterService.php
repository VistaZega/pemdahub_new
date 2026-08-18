<?php

namespace App\Services;

use App\Models\TeachingAssignment;
use App\Models\Student;
use Illuminate\Support\Collection;

class TeachingAssignmentStudentFilterService
{
    /**
     * Dapatkan daftar seluruh siswa aktif untuk penugasan mengajar di kelas ini.
     * Mengembalikan seluruh siswa aktif kelas agar guru memiliki keleluasaan penuh 
     * untuk menandai kehadiran (H / S / I / A) siswa manapun di kelasnya.
     */
    public function getStudentsForAssignment(TeachingAssignment $assignment, $date = null): Collection
    {
        $activeYearId = $assignment->academic_year_id;
        $classroomId = $assignment->classroom_id;
        
        $studentsQuery = Student::whereHas('studentClasses', function ($q) use ($activeYearId) {
            $q->where('status', 'aktif')
              ->where('academic_year_id', $activeYearId);
        });

        // Jika kelas gabungan lintas kelas (group_code)
        if (!empty($assignment->group_code)) {
            $relatedClassroomIds = TeachingAssignment::where('teacher_id', $assignment->teacher_id)
                ->where('academic_year_id', $activeYearId)
                ->where('group_code', $assignment->group_code)
                ->pluck('classroom_id')
                ->unique()
                ->toArray();
                
            $studentsQuery->whereHas('studentClasses', function($q) use ($relatedClassroomIds) {
                $q->whereIn('classroom_id', $relatedClassroomIds);
            })->with(['classrooms' => function($q) use ($relatedClassroomIds) {
                $q->whereIn('classrooms.id', $relatedClassroomIds);
            }]);
        } else {
            $studentsQuery->whereHas('studentClasses', function($q) use ($classroomId) {
                $q->where('classroom_id', $classroomId);
            })->with(['classrooms' => function($q) use ($classroomId) {
                $q->where('classrooms.id', $classroomId);
            }]);
        }

        return $studentsQuery->orderBy('full_name')->get();
    }
}

