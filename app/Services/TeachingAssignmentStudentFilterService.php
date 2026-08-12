<?php

namespace App\Services;

use App\Models\TeachingAssignment;
use App\Models\Student;
use App\Models\BlockStudentGroup;
use Illuminate\Support\Collection;

class TeachingAssignmentStudentFilterService
{
    /**
     * Dapatkan daftar siswa untuk suatu penugasan mengajar, 
     * dengan mempertimbangkan grup kelas gabungan, filter agama (paralel), 
     * dan filter grup blok (SMK).
     */
    public function getStudentsForAssignment(TeachingAssignment $assignment): Collection
    {
        $activeYearId = $assignment->academic_year_id;
        $classroomId = $assignment->classroom_id;
        $subject = $assignment->subject;
        
        $studentsQuery = Student::whereHas('studentClasses', function ($q) use ($activeYearId) {
            $q->where('status', 'aktif')
              ->where('academic_year_id', $activeYearId);
        });

        // 1. Group Code (Gabungan Kelas)
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

        // 2. Filter Paralel (Agama)
        if ($assignment->block_type === 'parallel' && $subject) {
            $subjectName = strtolower($subject->name ?? $subject->subject_name ?? '');
            
            if (str_contains($subjectName, 'islam')) {
                $studentsQuery->where('religion', 'Islam');
            } elseif (str_contains($subjectName, 'katolik') || str_contains($subjectName, 'katholik')) {
                $studentsQuery->where('religion', 'Katolik');
            } elseif (str_contains($subjectName, 'kristen')) {
                $studentsQuery->where('religion', 'Kristen');
            } elseif (str_contains($subjectName, 'hindu')) {
                $studentsQuery->where('religion', 'Hindu');
            } elseif (str_contains($subjectName, 'buddha') || str_contains($subjectName, 'budha')) {
                $studentsQuery->where('religion', 'Buddha');
            } elseif (str_contains($subjectName, 'konghucu')) {
                $studentsQuery->where('religion', 'Konghucu');
            }
        }

        // 3. Filter SMK Block System
        // 'all' = Group A
        // 'split' = Group B
        if (in_array($assignment->block_type, ['all', 'split'])) {
            $targetGroup = $assignment->block_type === 'all' ? 'A' : 'B';
            
            // Get student IDs that belong to this group for the relevant classrooms
            $classroomIdsForBlock = !empty($assignment->group_code) && isset($relatedClassroomIds) 
                ? $relatedClassroomIds 
                : [$classroomId];

            $validStudentIds = BlockStudentGroup::whereIn('classroom_id', $classroomIdsForBlock)
                ->where('group', $targetGroup)
                ->pluck('student_id')
                ->toArray();
                
            if (!empty($validStudentIds)) {
                $studentsQuery->whereIn('id', $validStudentIds);
            } else {
                // If no group is mapped but it's supposed to be filtered, we should return empty.
                $studentsQuery->whereIn('id', [0]);
            }
        }

        return $studentsQuery->orderBy('full_name')->get();
    }
}
