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
use App\Imports\LmsCourseGroupsImport;
use App\Exports\LmsCourseGroupsTemplateExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;
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

        $selectedClassroom = $request->classroom_id ? Classroom::find($request->classroom_id) : null;

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
            $classMsg = $selectedClassroom ? " pada kelas {$selectedClassroom->name}" : "";
            return redirect()->back()->with('error', "Semua siswa{$classMsg} pada kursus ini sudah memiliki kelompok.");
        }

        $numGroups = min((int)$request->group_count, $availableStudents->count());
        $chunks = $availableStudents->split($numGroups);

        $startIdx = $course->courseGroups()->count();
        $classPrefix = $selectedClassroom ? $selectedClassroom->name . ' - ' : '';

        foreach ($chunks as $idx => $chunk) {
            $groupNum = $startIdx + $idx + 1;
            $leader = $chunk->first();
            $group = $course->courseGroups()->create([
                'name' => 'Kelompok ' . $classPrefix . $groupNum,
                'leader_id' => $leader->id,
            ]);
            $group->members()->sync($chunk->pluck('id')->toArray());
        }

        $targetMsg = $selectedClassroom ? " untuk kelas {$selectedClassroom->name}" : "";
        return redirect()->route('guru.lms.show', ['course' => $course->id, 'tab' => 'groups'])
            ->with('success', "Berhasil membentuk {$numGroups} kelompok kursus secara otomatis{$targetMsg}.");
    }

    /**
     * Download Excel Template for Course Groups
     */
    public function downloadTemplate(LmsCourse $course)
    {
        $teacher = $this->getTeacher();
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }

        $filename = 'Template_Kelompok_LMS_' . Str::slug($course->course_name ?? $course->name ?? 'course') . '.xlsx';
        return Excel::download(new LmsCourseGroupsTemplateExport($course), $filename);
    }

    /**
     * Import Course Groups from Excel file
     */
    public function importExcel(Request $request, LmsCourse $course)
    {
        $teacher = $this->getTeacher();
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }

        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            'replace_existing' => 'nullable|boolean',
        ]);

        $assignmentCtrl = app(LmsAssignmentController::class);
        $refMethod = new \ReflectionMethod($assignmentCtrl, 'getEnrolledStudentsForCourse');
        $refMethod->setAccessible(true);
        $allEnrolledStudents = $refMethod->invoke($assignmentCtrl, $course);

        // Map lookup siswa berdasarkan nisn, nis, atau nama
        $studentLookup = [];
        foreach ($allEnrolledStudents as $std) {
            if (!empty($std->nisn)) {
                $studentLookup[trim((string)$std->nisn)] = $std;
            }
            if (!empty($std->nis)) {
                $studentLookup[trim((string)$std->nis)] = $std;
            }
            $cleanName = strtolower(trim($std->user->name ?? $std->full_name ?? ''));
            if (!empty($cleanName)) {
                $studentLookup['name:' . $cleanName] = $std;
            }
        }

        try {
            $import = new LmsCourseGroupsImport();
            Excel::import($import, $request->file('import_file'));
            $rows = $import->getRows();

            if (empty($rows)) {
                return redirect()->back()->with('error', 'File Excel kosong atau tidak terbaca.');
            }

            // Hapus kelompok lama jika opsi replace_existing dicentang
            if ($request->boolean('replace_existing')) {
                $course->courseGroups()->delete();
            }

            $groupedData = [];
            $notFoundIdentifiers = [];

            foreach ($rows as $row) {
                $groupName = trim($row['nama_kelompok'] ?? $row['kelompok'] ?? $row['group_name'] ?? '');
                $nisn = trim((string)($row['nisn'] ?? $row['nis'] ?? $row['no_induk'] ?? ''));
                $studentName = trim((string)($row['nama_siswa'] ?? $row['nama'] ?? $row['full_name'] ?? ''));
                $peran = strtolower(trim($row['peran'] ?? $row['role'] ?? 'anggota'));

                if (empty($groupName)) continue;

                // Cari siswa berdasarkan NISN, NIS, atau Nama
                $student = null;
                if (!empty($nisn) && isset($studentLookup[$nisn])) {
                    $student = $studentLookup[$nisn];
                } elseif (!empty($studentName) && isset($studentLookup['name:' . strtolower($studentName)])) {
                    $student = $studentLookup['name:' . strtolower($studentName)];
                }

                if (!$student) {
                    if (!empty($nisn) || !empty($studentName)) {
                        $notFoundIdentifiers[] = !empty($studentName) ? "{$studentName} ({$nisn})" : $nisn;
                    }
                    continue;
                }

                if (!isset($groupedData[$groupName])) {
                    $groupedData[$groupName] = [
                        'leader_id' => null,
                        'member_ids' => [],
                    ];
                }

                if ($peran === 'ketua' || $peran === 'leader' || $groupedData[$groupName]['leader_id'] === null) {
                    if ($groupedData[$groupName]['leader_id'] === null) {
                        $groupedData[$groupName]['leader_id'] = $student->id;
                    }
                }

                $groupedData[$groupName]['member_ids'][] = $student->id;
            }

            if (empty($groupedData)) {
                return redirect()->back()->with('error', 'Tidak ada kelompok atau siswa yang cocok yang dapat diimpor dari file tersebut.');
            }

            $createdCount = 0;
            $studentCount = 0;

            foreach ($groupedData as $name => $gInfo) {
                $memberIds = array_values(array_unique($gInfo['member_ids']));
                $leaderId = $gInfo['leader_id'] ?? $memberIds[0] ?? null;

                if (!$leaderId) continue;

                $group = $course->courseGroups()->firstOrCreate(
                    ['name' => $name],
                    ['leader_id' => $leaderId]
                );

                if ($group->leader_id !== $leaderId) {
                    $group->update(['leader_id' => $leaderId]);
                }

                $group->members()->sync($memberIds);
                $createdCount++;
                $studentCount += count($memberIds);
            }

            $msg = "Berhasil mengimpor {$createdCount} kelompok dengan total {$studentCount} siswa.";
            if (!empty($notFoundIdentifiers)) {
                $unmatched = implode(', ', array_unique($notFoundIdentifiers));
                $msg .= " Catatan: Siswa berikut tidak ditemukan/tidak terdaftar di kursus ini: {$unmatched}.";
            }

            return redirect()->route('guru.lms.show', ['course' => $course->id, 'tab' => 'groups'])
                ->with('success', $msg);

        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Gagal memproses import Excel: ' . $e->getMessage());
        }
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
