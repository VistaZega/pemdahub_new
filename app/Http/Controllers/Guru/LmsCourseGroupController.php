<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\LmsAssignment;
use App\Models\LmsAssignmentGroup;
use App\Models\LmsCourse;
use App\Models\LmsCourseGroup;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LmsCourseGroupController extends Controller
{
    use HasMultiSchool;

    private function getTeacher(): ?Teacher
    {
        return Teacher::where('user_id', Auth::id())->first();
    }

    private function authorizeAccess(LmsCourse $course, Teacher $teacher): bool
    {
        return $course->teacher_id === $teacher->id;
    }

    /**
     * Store a new course master group
     */
    public function store(Request $request, LmsCourse $course)
    {
        $teacher = $this->getTeacher();
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:100',
            'leader_id' => 'required|exists:students,id',
            'member_ids' => 'nullable|array',
            'member_ids.*' => 'exists:students,id',
        ]);

        // Cek konflik siswa yang sudah masuk kelompok kursus lain
        $alreadyGroupedStudentIds = $course->courseGroups->flatMap(function ($grp) {
            return $grp->members->pluck('id')->push($grp->leader_id);
        })->unique()->filter()->toArray();

        $allRequestedIds = collect($request->member_ids ?? [])->push((int)$request->leader_id)->unique()->toArray();
        $conflicts = array_values(array_intersect($allRequestedIds, $alreadyGroupedStudentIds));

        if (!empty($conflicts)) {
            $conflictStudents = Student::whereIn('id', $conflicts)->get()->map(fn($s) => $s->user->name ?? $s->full_name)->implode(', ');
            return redirect()->back()->with('error', "Gagal: Siswa ({$conflictStudents}) sudah terdaftar di kelompok kursus lain.");
        }

        $group = $course->courseGroups()->create([
            'name' => $request->name,
            'leader_id' => $request->leader_id,
        ]);

        $memberIds = collect($request->member_ids ?? [])->push($request->leader_id)->unique()->filter()->values()->toArray();
        $group->members()->sync($memberIds);

        return redirect()->route('guru.lms.show', ['course' => $course->id, 'tab' => 'groups'])
            ->with('success', "Kelompok '{$group->name}' berhasil ditambahkan ke kursus.");
    }

    /**
     * Delete a course master group
     */
    public function destroy(LmsCourse $course, LmsCourseGroup $group)
    {
        $teacher = $this->getTeacher();
        if (!$teacher || !$this->authorizeAccess($course, $teacher) || $group->course_id !== $course->id) {
            abort(403);
        }

        $groupName = $group->name;
        $group->delete();

        return redirect()->route('guru.lms.show', ['course' => $course->id, 'tab' => 'groups'])
            ->with('success', "Kelompok '{$groupName}' berhasil dihapus dari kursus.");
    }

    /**
     * Auto generate course master groups
     */
    public function autoGenerate(Request $request, LmsCourse $course)
    {
        $teacher = $this->getTeacher();
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }

        $request->validate([
            'group_count' => 'required|integer|min:1|max:30',
            'classroom_id' => 'nullable|exists:classrooms,id',
        ]);

        $assignmentCtrl = app(LmsAssignmentController::class);
        $refMethod = new \ReflectionMethod($assignmentCtrl, 'getEnrolledStudentsForCourse');
        $refMethod->setAccessible(true);
        $allStudents = $refMethod->invoke($assignmentCtrl, $course, $request->classroom_id ? (int)$request->classroom_id : null);

        // Hanya bagi siswa yang belum memiliki kelompok kursus
        $alreadyGroupedStudentIds = $course->courseGroups->flatMap(function ($grp) {
            return $grp->members->pluck('id')->push($grp->leader_id);
        })->unique()->filter()->toArray();

        $availableStudents = $allStudents->reject(fn($s) => in_array($s->id, $alreadyGroupedStudentIds))->shuffle()->values();

        if ($availableStudents->isEmpty()) {
            return redirect()->back()->with('error', 'Semua siswa pada kursus ini sudah memiliki kelompok.');
        }

        $numGroups = min((int)$request->group_count, $availableStudents->count());
        $chunks = $availableStudents->split($numGroups);

        $startIdx = $course->courseGroups()->count();
        foreach ($chunks as $idx => $chunk) {
            $groupNum = $startIdx + $idx + 1;
            $leader = $chunk->first();
            $group = $course->courseGroups()->create([
                'name' => 'Kelompok ' . $groupNum,
                'leader_id' => $leader->id,
            ]);
            $group->members()->sync($chunk->pluck('id')->toArray());
        }

        return redirect()->route('guru.lms.show', ['course' => $course->id, 'tab' => 'groups'])
            ->with('success', "Berhasil membentuk {$numGroups} kelompok kursus secara otomatis.");
    }

    /**
     * Import / Apply Course Master Groups to a specific Assignment
     */
    public function importToAssignment(Request $request, LmsAssignment $assignment)
    {
        $teacher = $this->getTeacher();
        $course = $assignment->course;
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }

        $courseGroups = $course->courseGroups()->with('members')->get();

        if ($courseGroups->isEmpty()) {
            return redirect()->back()->with('error', 'Kursus ini belum memiliki master kelompok belajar.');
        }

        $importedCount = 0;
        foreach ($courseGroups as $cg) {
            // Hindari duplikasi jika kelompok dengan nama yang sama sudah ada di tugas ini
            $existing = $assignment->groups()->where('name', $cg->name)->first();
            if (!$existing) {
                $grp = $assignment->groups()->create([
                    'name' => $cg->name,
                    'leader_id' => $cg->leader_id,
                ]);
                $grp->members()->sync($cg->members->pluck('id')->toArray());
                $importedCount++;
            }
        }

        return redirect()->route('guru.lms.assignments.show', $assignment->id)
            ->with('success', "Berhasil menerapkan {$importedCount} kelompok dari Master Kelompok Kursus ke tugas ini.");
    }
}
