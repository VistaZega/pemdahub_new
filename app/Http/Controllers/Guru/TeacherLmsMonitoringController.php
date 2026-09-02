<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\LmsCourse;
use App\Models\LmsEnrollment;
use App\Models\LmsMaterialProgress;
use App\Models\LmsQuizAttempt;
use App\Models\LmsSubmission;
use App\Models\Student;
use App\Models\StudentCounselingRecord;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TeacherLmsMonitoringController extends Controller
{
    /**
     * Get authenticated Teacher record
     */
    private function getTeacher(): ?Teacher
    {
        $user = Auth::user();
        return Teacher::where('user_id', $user->id)->first();
    }

    /**
     * Dashboard Pantauan Keseluruhan Siswa untuk Guru Mata Pelajaran
     */
    public function index(Request $request)
    {
        $teacher = $this->getTeacher();
        if (!$teacher) {
            abort(403, 'Akses khusus Guru Pengajar.');
        }

        $activeYear = AcademicYear::where('is_active', true)->first() ?? AcademicYear::latest()->first();

        // 1. Ambil seluruh kursus LMS yang diajarkan oleh Guru ini
        $myCourses = LmsCourse::where('teacher_id', $teacher->id)
            ->where(function ($q) {
                $q->where('is_published', true)
                  ->orWhere('status', 'active')
                  ->orWhere('is_active', true);
            })
            ->with(['subject', 'lmsClasses.classroom'])
            ->withCount(['materials', 'assignments', 'quizzes'])
            ->orderBy('course_name')
            ->get();

        if ($myCourses->isEmpty()) {
            return view('guru.lms.monitoring_empty', compact('teacher'));
        }

        // 2. Filter Kursus & Rombel
        $selectedCourseId = $request->query('course_id', $myCourses->first()->id);
        $course = $myCourses->firstWhere('id', $selectedCourseId) ?? $myCourses->first();

        // Ambil rombel-rombel yang terhubung ke kursus terpilih
        $classrooms = $course->lmsClasses->map(fn($lc) => $lc->classroom)->filter()->unique('id')->values();
        $selectedClassroomId = $request->query('classroom_id');

        // 3. Ambil seluruh siswa terdaftar di kursus ini (atau rombel terpilih)
        $lmsClassQuery = $course->lmsClasses();
        if ($selectedClassroomId) {
            $lmsClassQuery->where('classroom_id', $selectedClassroomId);
        }
        $lmsClassIds = $lmsClassQuery->pluck('id')->toArray();

        $enrollments = LmsEnrollment::whereIn('lms_class_id', $lmsClassIds)
            ->with(['student.user', 'student.currentClassroom', 'student.parents', 'lmsClass.classroom'])
            ->get();

        $students = $enrollments->pluck('student')->filter()->unique('id')->values();
        $studentIds = $students->pluck('id')->toArray();

        // Ambil komponen kursus
        $materials = $course->materials()->where('is_published', true)->get();
        $assignments = $course->assignments()->where('is_published', true)->get();
        $quizzes = $course->quizzes()->where('is_published', true)->get();

        $matIds = $materials->pluck('id')->toArray();
        $assignIds = $assignments->pluck('id')->toArray();
        $quizIds = $quizzes->pluck('id')->toArray();

        $totalMats = count($matIds);
        $totalAssigns = count($assignIds);
        $totalQuizzes = count($quizIds);

        // Preload progres & tugas
        $materialProgresses = LmsMaterialProgress::whereIn('student_id', $studentIds)
            ->whereIn('material_id', $matIds)
            ->get()
            ->groupBy('student_id');

        $submissions = LmsSubmission::whereIn('student_id', $studentIds)
            ->whereIn('assignment_id', $assignIds)
            ->get()
            ->groupBy('student_id');

        $quizAttempts = LmsQuizAttempt::whereIn('student_id', $studentIds)
            ->whereIn('quiz_id', $quizIds)
            ->whereNotNull('finished_at')
            ->get()
            ->groupBy('student_id');

        // 4. Hitung Matriks Siswa
        $studentList = [];
        foreach ($students as $st) {
            $stMat = $materialProgresses->get($st->id, collect());
            $stSub = $submissions->get($st->id, collect());
            $stQuiz = $quizAttempts->get($st->id, collect());

            $completedMats = $stMat->where('status', 'completed')->count();
            $submittedAssigns = $stSub->whereIn('status', ['submitted', 'graded'])->count();
            $completedQuizzes = $stQuiz->count();

            $matPct = $totalMats > 0 ? round(($completedMats / $totalMats) * 100) : 100;
            $assignPct = $totalAssigns > 0 ? round(($submittedAssigns / $totalAssigns) * 100) : 100;
            $quizPct = $totalQuizzes > 0 ? round(($completedQuizzes / $totalQuizzes) * 100) : 100;

            $overallPct = round(($matPct * 0.5) + ($assignPct * 0.3) + ($quizPct * 0.2));

            // Hitung nilai rata-rata tugas & kuis
            $avgAssignGrade = $stSub->whereNotNull('grade')->avg('grade');
            $avgQuizScore = $stQuiz->whereNotNull('score')->avg('score');

            // Status & Risiko
            $isAtRisk = false;
            if ($overallPct < 40 || ($totalAssigns > 1 && $submittedAssigns === 0)) {
                $isAtRisk = true;
                $statusBadge = 'Perlu Perhatian';
                $statusColor = 'rose';
            } elseif ($overallPct >= 85) {
                $statusBadge = 'Sangat Aktif';
                $statusColor = 'indigo';
            } else {
                $statusBadge = 'On Track';
                $statusColor = 'emerald';
            }

            // Target kontak WhatsApp
            $parent = $st->parents->first();
            $targetPhone = $parent?->phone ?? $parent?->whatsapp ?? $st->phone ?? $st->whatsapp ?? null;
            if ($targetPhone) {
                $targetPhone = preg_replace('/[^0-9]/', '', $targetPhone);
                if (str_starts_with($targetPhone, '0')) {
                    $targetPhone = '62' . substr($targetPhone, 1);
                }
            }

            // Cari rombel dari enrollment
            $enrollment = $enrollments->firstWhere('student_id', $st->id);
            $className = $enrollment?->lmsClass?->classroom?->class_name ?? $st->currentClassroom?->class_name ?? '-';

            $studentList[] = [
                'student' => $st,
                'class_name' => $className,
                'completed_materials' => $completedMats,
                'total_materials' => $totalMats,
                'material_pct' => $matPct,
                'submitted_assignments' => $submittedAssigns,
                'total_assignments' => $totalAssigns,
                'assignment_pct' => $assignPct,
                'avg_assignment_grade' => $avgAssignGrade ? round($avgAssignGrade, 1) : null,
                'completed_quizzes' => $completedQuizzes,
                'total_quizzes' => $totalQuizzes,
                'quiz_pct' => $quizPct,
                'avg_quiz_score' => $avgQuizScore ? round($avgQuizScore, 1) : null,
                'overall_pct' => $overallPct,
                'is_at_risk' => $isAtRisk,
                'status_badge' => $statusBadge,
                'status_color' => $statusColor,
                'phone' => $targetPhone,
                'parent_name' => $parent?->father_name ?? $parent?->mother_name ?? 'Orang Tua / Wali',
            ];
        }

        // Filtering & Searching
        $filterTab = $request->query('filter', 'all');
        $search = strtolower(trim($request->query('search', '')));

        $filteredStudents = collect($studentList)->filter(function ($item) use ($filterTab, $search) {
            if ($filterTab === 'at_risk' && !$item['is_at_risk']) {
                return false;
            }
            if ($filterTab === 'missing_task' && ($item['total_assignments'] === 0 || $item['submitted_assignments'] >= $item['total_assignments'])) {
                return false;
            }
            if ($filterTab === 'completed' && $item['overall_pct'] < 100) {
                return false;
            }
            if (!empty($search)) {
                $nameMatch = str_contains(strtolower($item['student']->full_name), $search);
                $nisnMatch = str_contains(strtolower($item['student']->nisn ?? ''), $search);
                return $nameMatch || $nisnMatch;
            }
            return true;
        })->values();

        // 5. KPI Ringkasan
        $kpi = [
            'total_enrolled_students' => count($studentList),
            'avg_material_completion' => count($studentList) > 0 ? round(collect($studentList)->avg('material_pct')) : 0,
            'total_submissions_count' => LmsSubmission::whereIn('assignment_id', $assignIds)->count(),
            'pending_grading_count' => LmsSubmission::whereIn('assignment_id', $assignIds)->where('status', 'submitted')->count(),
            'at_risk_count' => collect($studentList)->where('is_at_risk', true)->count(),
            'missing_task_count' => collect($studentList)->filter(fn($i) => $i['total_assignments'] > 0 && $i['submitted_assignments'] < $i['total_assignments'])->count(),
        ];

        return view('guru.lms.monitoring', compact(
            'teacher',
            'myCourses',
            'course',
            'classrooms',
            'selectedClassroomId',
            'filteredStudents',
            'studentList',
            'kpi',
            'filterTab',
            'search',
            'materials',
            'assignments',
            'quizzes'
        ));
    }

    /**
     * AJAX Detail Progres Siswa di Suatu Kursus
     */
    public function studentCourseDetail(Student $student, LmsCourse $course)
    {
        $teacher = $this->getTeacher();
        if ($course->teacher_id !== $teacher?->id && !Auth::user()->hasAnyRole(['admin', 'superadmin', 'kepala_sekolah'])) {
            return response()->json(['error' => 'Akses ditolak.'], 403);
        }

        $materials = $course->materials()->where('is_published', true)->get();
        $completedMatIds = LmsMaterialProgress::where('student_id', $student->id)
            ->whereIn('material_id', $materials->pluck('id'))
            ->where('status', 'completed')
            ->pluck('material_id')
            ->toArray();

        $assignments = $course->assignments()->where('is_published', true)->get();
        $submissions = LmsSubmission::where('student_id', $student->id)
            ->whereIn('assignment_id', $assignments->pluck('id'))
            ->get()
            ->keyBy('assignment_id');

        $quizzes = $course->quizzes()->where('is_published', true)->get();
        $attempts = LmsQuizAttempt::where('student_id', $student->id)
            ->whereIn('quiz_id', $quizzes->pluck('id'))
            ->get()
            ->groupBy('quiz_id');

        return response()->json([
            'student' => [
                'id' => $student->id,
                'name' => $student->full_name,
                'nisn' => $student->nisn,
                'photo' => $student->photo ? asset('storage/' . $student->photo) : null,
                'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($student->full_name) . '&background=ea580c&color=fff',
                'parent_name' => $student->parents->first()?->father_name ?? 'Orang Tua / Wali',
                'parent_phone' => $student->parents->first()?->phone ?? $student->parents->first()?->whatsapp ?? $student->phone ?? null,
            ],
            'course' => [
                'id' => $course->id,
                'name' => $course->course_name,
                'subject' => $course->subject?->name ?? $course->subject?->subject_name ?? '-',
            ],
            'materials' => $materials->map(fn($m) => [
                'id' => $m->id,
                'title' => $m->title,
                'type' => $m->material_type,
                'is_completed' => in_array($m->id, $completedMatIds),
            ]),
            'assignments' => $assignments->map(fn($a) => [
                'id' => $a->id,
                'title' => $a->title,
                'deadline' => $a->deadline ? $a->deadline->format('d M Y H:i') : '-',
                'is_submitted' => $submissions->has($a->id),
                'status' => $submissions->get($a->id)?->status ?? 'belum_kumpul',
                'grade' => $submissions->get($a->id)?->grade,
                'submitted_at' => $submissions->get($a->id)?->submitted_at?->format('d M Y H:i') ?? null,
            ]),
            'quizzes' => $quizzes->map(fn($q) => [
                'id' => $q->id,
                'title' => $q->title,
                'attempts_count' => $attempts->get($q->id, collect())->count(),
                'highest_score' => $attempts->get($q->id, collect())->max('score'),
            ]),
        ]);
    }

    /**
     * Kirim Apresiasi atau Pengingat Belajar Siswa oleh Guru Mapel
     */
    public function sendPraiseOrWarning(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'course_id' => 'required|exists:lms_courses,id',
            'type' => 'required|in:apresiasi,peringatan,tugas',
            'message' => 'required|string|max:1000',
            'target_phone' => 'nullable|string',
        ]);

        $teacher = $this->getTeacher();
        $student = Student::with(['school', 'currentClassroom'])->findOrFail($request->student_id);
        $course = LmsCourse::findOrFail($request->course_id);

        $waUrl = null;
        if (!empty($request->target_phone)) {
            $phone = preg_replace('/[^0-9]/', '', $request->target_phone);
            if (str_starts_with($phone, '0')) {
                $phone = '62' . substr($phone, 1);
            }

            $teacherName = $teacher?->user?->name ?? $teacher?->full_name ?? Auth::user()->name;
            $subjectName = $course->subject?->name ?? $course->subject?->subject_name ?? $course->course_name;

            $headerText = match($request->type) {
                'apresiasi' => '🌟 *Apresiasi Guru:* Hebat! Terus pertahankan semangat belajar ananda.',
                'peringatan' => '⚠️ *Pengingat Guru:* Mohon perhatian untuk keaktifan belajar LMS mata pelajaran ' . $subjectName . '.',
                default => '📝 *Pengingat Tugas:* Terdapat tugas/materi LMS ' . $subjectName . ' yang belum diselesaikan.'
            };

            $waText = "Halo ananda *{$student->full_name}* / Bapak/Ibu Wali,\n\n"
                    . "Saya *{$teacherName}* (Guru Pengampu {$subjectName}).\n\n"
                    . "{$headerText}\n\n"
                    . "💬 *Pesan Guru:*\n"
                    . "_{$request->message}_\n\n"
                    . "Segera buka portal LMS PembdaHUB untuk melanjutkan pembelajaran. Semangat! 🚀\n\n"
                    . "_Sistem PembdaHUB - Ekosistem Digital Yayasan Perguruan PEMBDA Nias_";

            $waUrl = "https://wa.me/{$phone}?text=" . urlencode($waText);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pesan pengingat / apresiasi berhasil diproses.',
            'wa_url' => $waUrl,
        ]);
    }

    /**
     * Export Rekapitulasi Progres Siswa ke Format Excel / CSV
     */
    public function exportExcel(Request $request): StreamedResponse
    {
        $teacher = $this->getTeacher();
        $courseId = $request->query('course_id');
        $course = LmsCourse::where('id', $courseId)
            ->where('teacher_id', $teacher->id)
            ->with(['subject', 'materials', 'assignments', 'quizzes'])
            ->firstOrFail();

        $lmsClassIds = $course->lmsClasses()->pluck('id')->toArray();
        $enrollments = LmsEnrollment::whereIn('lms_class_id', $lmsClassIds)
            ->with(['student.currentClassroom', 'lmsClass.classroom'])
            ->get();

        $students = $enrollments->pluck('student')->filter()->unique('id')->values();
        $studentIds = $students->pluck('id')->toArray();

        $matIds = $course->materials->pluck('id')->toArray();
        $assignIds = $course->assignments->pluck('id')->toArray();
        $quizIds = $course->quizzes->pluck('id')->toArray();

        $materialProgresses = LmsMaterialProgress::whereIn('student_id', $studentIds)
            ->whereIn('material_id', $matIds)
            ->get()
            ->groupBy('student_id');

        $submissions = LmsSubmission::whereIn('student_id', $studentIds)
            ->whereIn('assignment_id', $assignIds)
            ->get()
            ->groupBy('student_id');

        $quizAttempts = LmsQuizAttempt::whereIn('student_id', $studentIds)
            ->whereIn('quiz_id', $quizIds)
            ->whereNotNull('finished_at')
            ->get()
            ->groupBy('student_id');

        $filename = 'Rekap_Progres_LMS_' . str_replace(' ', '_', $course->course_name) . '_' . date('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($students, $enrollments, $course, $matIds, $assignIds, $quizIds, $materialProgresses, $submissions, $quizAttempts) {
            $handle = fopen('php://output', 'w');
            // BOM UTF-8 for Excel Indonesian characters
            fputs($handle, "\xEF\xBB\xBF");

            // Header info
            fputcsv($handle, ['REKAPITULASI PROGRES PEMBELAJARAN LMS']);
            fputcsv($handle, ['Mata Pelajaran', $course->subject?->name ?? $course->subject?->subject_name ?? $course->course_name]);
            fputcsv($handle, ['Nama Kursus', $course->course_name]);
            fputcsv($handle, ['Total Materi', count($matIds)]);
            fputcsv($handle, ['Total Tugas', count($assignIds)]);
            fputcsv($handle, ['Total Kuis', count($quizIds)]);
            fputcsv($handle, ['Tanggal Ekspor', date('d/m/Y H:i')]);
            fputcsv($handle, []);

            // Table headers
            fputcsv($handle, [
                'No',
                'Nama Siswa',
                'NISN',
                'Kelas / Rombel',
                'Materi Selesai',
                'Total Materi',
                'Progres Materi (%)',
                'Tugas Dikumpulkan',
                'Total Tugas',
                'Rata-rata Nilai Tugas',
                'Kuis Selesai',
                'Total Kuis',
                'Rata-rata Nilai Kuis',
                'Total Progres LMS (%)',
                'Status Pembelajaran',
            ]);

            $no = 1;
            foreach ($students as $st) {
                $stMat = $materialProgresses->get($st->id, collect());
                $stSub = $submissions->get($st->id, collect());
                $stQuiz = $quizAttempts->get($st->id, collect());

                $completedMats = $stMat->where('status', 'completed')->count();
                $submittedAssigns = $stSub->whereIn('status', ['submitted', 'graded'])->count();
                $completedQuizzes = $stQuiz->count();

                $totalMats = count($matIds);
                $totalAssigns = count($assignIds);
                $totalQuizzes = count($quizIds);

                $matPct = $totalMats > 0 ? round(($completedMats / $totalMats) * 100) : 100;
                $assignPct = $totalAssigns > 0 ? round(($submittedAssigns / $totalAssigns) * 100) : 100;
                $quizPct = $totalQuizzes > 0 ? round(($completedQuizzes / $totalQuizzes) * 100) : 100;

                $overallPct = round(($matPct * 0.5) + ($assignPct * 0.3) + ($quizPct * 0.2));

                $avgAssignGrade = $stSub->whereNotNull('grade')->avg('grade');
                $avgQuizScore = $stQuiz->whereNotNull('score')->avg('score');

                $enrollment = $enrollments->firstWhere('student_id', $st->id);
                $className = $enrollment?->lmsClass?->classroom?->class_name ?? $st->currentClassroom?->class_name ?? '-';

                $status = ($overallPct < 40 || ($totalAssigns > 1 && $submittedAssigns === 0)) ? 'Perlu Perhatian' : ($overallPct >= 85 ? 'Sangat Aktif' : 'On Track');

                fputcsv($handle, [
                    $no++,
                    $st->full_name,
                    $st->nisn ?? '-',
                    $className,
                    $completedMats,
                    $totalMats,
                    $matPct . '%',
                    $submittedAssigns,
                    $totalAssigns,
                    $avgAssignGrade ? round($avgAssignGrade, 1) : '-',
                    $completedQuizzes,
                    $totalQuizzes,
                    $avgQuizScore ? round($avgQuizScore, 1) : '-',
                    $overallPct . '%',
                    $status,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
