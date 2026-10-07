<?php

namespace App\Services;

use App\Models\TeachingAssignment;
use App\Models\Student;
use Illuminate\Support\Collection;

class TeachingAssignmentStudentFilterService
{
    /**
     * Dapatkan daftar seluruh siswa aktif untuk penugasan mengajar di kelas ini.
     * Untuk mapel kejuruan (DDTK / Konsentrasi Keahlian), otomatis menyaring hanya
     * siswa yang jurusannya relevan dengan mata pelajaran tersebut.
     */
    public function getStudentsForAssignment(TeachingAssignment $assignment, $date = null): Collection
    {
        $activeYearId = $assignment->academic_year_id;
        $classroomId = $assignment->classroom_id;
        
        $studentsQuery = Student::whereIn('students.status', ['aktif', 'calon', 'naik'])
            ->whereHas('studentClasses', function ($q) use ($activeYearId) {
                $q->whereIn('status', ['aktif', 'enrolled', 'active'])
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
            })->with(['classrooms']);
        } else {
            $studentsQuery->whereHas('studentClasses', function($q) use ($classroomId) {
                $q->where('classroom_id', $classroomId);
            })->with(['classrooms']);
        }

        $students = $studentsQuery->orderBy('full_name')->get();

        // Jika guru pengampu adalah WALI KELAS dari rombel ini, seluruh siswa kelas bimbingannya
        // wajib dapat dilihat oleh wali kelas (tidak boleh dipotong oleh filter kejuruan).
        $classroom = $assignment->classroom ?? \App\Models\Classroom::find($classroomId);
        $tIds = $assignment->teacher ? $assignment->teacher->allTeacherIds() : [(int) $assignment->teacher_id];
        $isHomeroom = $classroom && in_array((int) $classroom->homeroom_teacher_id, array_map('intval', $tIds), true);
        if ($isHomeroom) {
            return $students;
        }

        // Filter otomatis untuk Mata Pelajaran Kejuruan (DDTK / Konsentrasi Keahlian)
        $subjectName = $assignment->subject?->name ?? $assignment->subject?->subject_name;
        $subjectCode = $assignment->subject?->code ?? $assignment->subject?->subject_code;
        $vocKeywords = VocationalMajorFilterService::getSubjectMajorKeywords($subjectName, $subjectCode);

        if ($vocKeywords) {
            $filtered = $students->filter(function ($student) use ($vocKeywords, $classroom) {
                return VocationalMajorFilterService::isStudentMatchingVocationalSubject($student, $vocKeywords, $classroom);
            })->values();

            // PENGAMAN KRITIS: Gunakan hasil filter jika terdapat siswa yang sesuai.
            // Jika hasil filter kosong (0 siswa) padahal kelas memiliki siswa aktif, JANGAN hilangkan siswa!
            // Kembalikan seluruh siswa aktif rombel agar guru tidak mengalami layar kosong dan tetap dapat mengabsen.
            if ($filtered->isNotEmpty()) {
                return $filtered;
            }
        }

        return $students;
    }
}

