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
            ->with(['subject', 'lmsClasses.classroom', 'materials' => fn($q) => $q->where('is_published', true), 'assignments' => fn($q) => $q->where('is_published', true), 'quizzes' => fn($q) => $q->where('is_published', true)])
            ->orderBy('course_name')
            ->get();

        if ($myCourses->isEmpty()) {
            return view('guru.lms.monitoring_empty', compact('teacher'));
        }

        // 2. Filter Kursus & Rombel
        $selectedCourseId = $request->query('course_id', 'all');
        $selectedClassroomId = $request->query('classroom_id');

        $isAllCourses = ($selectedCourseId === 'all');
        $activeCourses = $isAllCourses ? $myCourses : $myCourses->where('id', $selectedCourseId);

        if ($activeCourses->isEmpty()) {
            $selectedCourseId = 'all';
            $isAllCourses = true;
            $activeCourses = $myCourses;
        }

        $course = $isAllCourses ? null : $activeCourses->first();

        // Ambil daftar semua rombel yang terkait dengan kursus yang aktif difilter
        $classrooms = $activeCourses->flatMap(fn($c) => $c->lmsClasses->map(fn($lc) => $lc->classroom))->filter()->unique('id')->values();

        // 3. Ambil seluruh enrollment siswa
        $lmsClassQuery = \App\Models\LmsClass::whereIn('course_id', $activeCourses->pluck('id'));
        if ($selectedClassroomId) {
            $lmsClassQuery->where('classroom_id', $selectedClassroomId);
        }
        $lmsClassIds = $lmsClassQuery->pluck('id')->toArray();

        $enrollments = LmsEnrollment::whereIn('lms_class_id', $lmsClassIds)
            ->with(['student.user', 'student.classrooms', 'student.parents', 'lmsClass.classroom', 'lmsClass.course.subject'])
            ->get();

        // Preload komponen seluruh kursus aktif
        $allMatIds = $activeCourses->flatMap(fn($c) => $c->materials->pluck('id'))->unique()->values()->toArray();
        $allAssignIds = $activeCourses->flatMap(fn($c) => $c->assignments->pluck('id'))->unique()->values()->toArray();
        $allQuizIds = $activeCourses->flatMap(fn($c) => $c->quizzes->pluck('id'))->unique()->values()->toArray();

        $studentIds = $enrollments->pluck('student_id')->unique()->values()->toArray();

        $materialProgresses = LmsMaterialProgress::whereIn('student_id', $studentIds)
            ->whereIn('material_id', $allMatIds)
            ->get()
            ->groupBy('student_id');

        $submissions = LmsSubmission::whereIn('student_id', $studentIds)
            ->whereIn('assignment_id', $allAssignIds)
            ->get()
            ->groupBy('student_id');

        $quizAttempts = LmsQuizAttempt::whereIn('student_id', $studentIds)
            ->whereIn('quiz_id', $allQuizIds)
            ->whereNotNull('finished_at')
            ->get()
            ->groupBy('student_id');

        // 4. Hitung Matriks Siswa (Per Kursus - Siswa Pair)
        // Preload daftar student_id yang benar-benar terdaftar di setiap classroom (classroom_student)
        $classroomStudentMap = [];
        foreach ($activeCourses as $ac) {
            foreach ($ac->lmsClasses as $lc) {
                if ($lc->classroom) {
                    $classroomStudentMap[$lc->classroom_id] = $lc->classroom->students()->pluck('students.id')->toArray();
                }
            }
        }

        $studentList = [];
        foreach ($enrollments as $enr) {
            $st = $enr->student;
            if (!$st) continue;

            $c = $enr->lmsClass?->course;
            if (!$c) continue;

            $lmsClass = $enr->lmsClass?->classroom;

            // Validasi: Siswa HARUS benar-benar terdaftar di classroom yang dipilih
            // LMS enrollment bisa bocor (117 enrollment vs 28 anggota kelas sebenarnya)
            if ($lmsClass && isset($classroomStudentMap[$lmsClass->id])) {
                if (!in_array($st->id, $classroomStudentMap[$lmsClass->id])) {
                    continue; // Siswa bukan anggota kelas ini, skip
                }
            }

            // Filter Otomatis Kejuruan: Jika ini mapel kejuruan (DDTK / Konsentrasi Keahlian),
            // hanya sertakan siswa yang jurusannya relevan dengan kejuruan tersebut
            $subjName = $c->subject?->name ?? $c->subject?->subject_name;
            $vocKeywords = $this->getSubjectMajorKeywords($subjName, $c->subject?->code, $c->course_name);
            if (!$this->isStudentMatchingVocationalSubject($st, $vocKeywords, $lmsClass)) {
                continue;
            }

            $cMatIds = $c->materials->pluck('id')->toArray();
            $cAssignIds = $c->assignments->pluck('id')->toArray();
            $cQuizIds = $c->quizzes->pluck('id')->toArray();

            $totalMats = count($cMatIds);
            $totalAssigns = count($cAssignIds);
            $totalQuizzes = count($cQuizIds);

            $stMat = $materialProgresses->get($st->id, collect());
            $stSub = $submissions->get($st->id, collect());
            $stQuiz = $quizAttempts->get($st->id, collect());

            $completedMats = $stMat->whereIn('material_id', $cMatIds)->where('status', 'completed')->count();
            $submittedAssigns = $stSub->whereIn('assignment_id', $cAssignIds)->whereIn('status', ['submitted', 'graded'])->count();
            $completedQuizzes = $stQuiz->whereIn('quiz_id', $cQuizIds)->count();

            $matPct = $totalMats > 0 ? round(($completedMats / $totalMats) * 100) : 100;
            $assignPct = $totalAssigns > 0 ? round(($submittedAssigns / $totalAssigns) * 100) : 100;
            $quizPct = $totalQuizzes > 0 ? round(($completedQuizzes / $totalQuizzes) * 100) : 100;

            $overallPct = round(($matPct * 0.5) + ($assignPct * 0.3) + ($quizPct * 0.2));

            // Nilai rata-rata tugas & kuis pada kursus ini
            $avgAssignGrade = $stSub->whereIn('assignment_id', $cAssignIds)->whereNotNull('grade')->avg('grade');
            $avgQuizScore = $stQuiz->whereIn('quiz_id', $cQuizIds)->whereNotNull('score')->avg('score');

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

            // Identifikasi kelas siswa pada TAHUN PELAJARAN yang sesuai dengan Kursus
            $lmsClass = $enr->lmsClass?->classroom;
            $courseYearId = $c->academic_year_id ?? $lmsClass?->academic_year_id ?? $activeYearId;

            $activeClass = $st->classrooms
                ->where('academic_year_id', $courseYearId)
                ->where('is_active', true)
                ->first() ?? $lmsClass ?? $st->classrooms->first();

            $className = $activeClass?->class_name ?? $lmsClass?->class_name ?? '-';

            $studentList[] = [
                'enrollment_id' => $enr->id,
                'student' => $st,
                'course' => $c,
                'course_name' => $c->course_name,
                'subject_name' => $c->subject?->name ?? $c->subject?->subject_name ?? $c->course_name,
                'class_name' => $className,
                'block_class_name' => $lmsClass?->class_name ?? $className,
                'is_block_class' => ($lmsClass && $activeClass && $lmsClass->id !== $activeClass->id),
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
        $selectedOriginClass = $request->query('origin_class');

        $originClasses = collect($studentList)->pluck('class_name')->filter(fn($c) => !empty($c) && $c !== '-')->unique()->sort()->values();

        $filteredStudents = collect($studentList)->filter(function ($item) use ($filterTab, $search, $selectedOriginClass) {
            if ($filterTab === 'at_risk' && !$item['is_at_risk']) {
                return false;
            }
            if ($filterTab === 'missing_task' && ($item['total_assignments'] === 0 || $item['submitted_assignments'] >= $item['total_assignments'])) {
                return false;
            }
            if ($filterTab === 'completed' && $item['overall_pct'] < 100) {
                return false;
            }
            if (!empty($selectedOriginClass) && $item['class_name'] !== $selectedOriginClass) {
                return false;
            }
            if (!empty($search)) {
                $nameMatch = str_contains(strtolower($item['student']->full_name), $search);
                $nisnMatch = str_contains(strtolower($item['student']->nisn ?? ''), $search);
                $courseMatch = str_contains(strtolower($item['course_name']), $search);
                $classMatch = str_contains(strtolower($item['class_name']), $search);
                $blockMatch = str_contains(strtolower($item['block_class_name']), $search);
                return $nameMatch || $nisnMatch || $courseMatch || $classMatch || $blockMatch;
            }
            return true;
        })->values();

        // 5. KPI Ringkasan
        $kpi = [
            'total_courses_count' => $activeCourses->count(),
            'total_enrolled_students' => count($studentList),
            'avg_material_completion' => count($studentList) > 0 ? round(collect($studentList)->avg('material_pct')) : 0,
            'total_submissions_count' => LmsSubmission::whereIn('assignment_id', $allAssignIds)->count(),
            'pending_grading_count' => LmsSubmission::whereIn('assignment_id', $allAssignIds)->where('status', 'submitted')->count(),
            'at_risk_count' => collect($studentList)->where('is_at_risk', true)->count(),
            'missing_task_count' => collect($studentList)->filter(fn($i) => $i['total_assignments'] > 0 && $i['submitted_assignments'] < $i['total_assignments'])->count(),
        ];

        return view('guru.lms.monitoring', compact(
            'teacher',
            'myCourses',
            'course',
            'isAllCourses',
            'selectedCourseId',
            'classrooms',
            'selectedClassroomId',
            'originClasses',
            'selectedOriginClass',
            'filteredStudents',
            'studentList',
            'kpi',
            'filterTab',
            'search'
        ));
    }

    /**
     * AJAX Detail Progres Siswa di Suatu Kursus
     */
    public function studentCourseDetail(Student $student, LmsCourse $course)
    {
        $teacher = $this->getTeacher();
        if ($course->teacher_id != $teacher?->id && !Auth::user()->hasAnyRole(['admin', 'superadmin', 'kepala_sekolah'])) {
            return response()->json(['error' => 'Akses ditolak.'], 403);
        }

        $student->load(['parents']);

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
            ->whereNotNull('finished_at')
            ->get()
            ->groupBy('quiz_id');

        $photoUrl = null;
        if ($student->photo) {
            $photoUrl = str_starts_with($student->photo, 'http') ? $student->photo : asset('storage/' . $student->photo);
        }

        return response()->json([
            'student' => [
                'id' => $student->id,
                'name' => $student->full_name,
                'nisn' => $student->nisn,
                'photo' => $photoUrl,
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
            'assignments' => $assignments->map(function ($a) use ($submissions) {
                $sub = $submissions->get($a->id);
                $deadlineStr = '-';
                if ($a->deadline) {
                    $deadlineStr = is_string($a->deadline) ? date('d M Y H:i', strtotime($a->deadline)) : $a->deadline->format('d M Y H:i');
                }
                $submittedAtStr = null;
                if ($sub?->submitted_at) {
                    $submittedAtStr = is_string($sub->submitted_at) ? date('d M Y H:i', strtotime($sub->submitted_at)) : $sub->submitted_at->format('d M Y H:i');
                }
                return [
                    'id' => $a->id,
                    'title' => $a->title,
                    'deadline' => $deadlineStr,
                    'is_submitted' => $sub !== null,
                    'status' => $sub?->status ?? 'belum_kumpul',
                    'grade' => $sub?->score ?? $sub?->grade,
                    'submitted_at' => $submittedAtStr,
                ];
            }),
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
        $student = Student::with(['school', 'classrooms'])->findOrFail($request->student_id);
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
        $courseId = $request->query('course_id', 'all');

        $isAll = ($courseId === 'all');
        $coursesQuery = LmsCourse::where('teacher_id', $teacher->id)
            ->with(['subject', 'materials' => fn($q) => $q->where('is_published', true), 'assignments' => fn($q) => $q->where('is_published', true), 'quizzes' => fn($q) => $q->where('is_published', true), 'lmsClasses.classroom']);

        if (!$isAll) {
            $coursesQuery->where('id', $courseId);
        }
        $courses = $coursesQuery->get();

        $lmsClassIds = $courses->flatMap(fn($c) => $c->lmsClasses->pluck('id'))->toArray();
        $enrollments = LmsEnrollment::whereIn('lms_class_id', $lmsClassIds)
            ->with(['student.classrooms', 'lmsClass.classroom', 'lmsClass.course.subject'])
            ->get();

        $allMatIds = $courses->flatMap(fn($c) => $c->materials->pluck('id'))->unique()->values()->toArray();
        $allAssignIds = $courses->flatMap(fn($c) => $c->assignments->pluck('id'))->unique()->values()->toArray();
        $allQuizIds = $courses->flatMap(fn($c) => $c->quizzes->pluck('id'))->unique()->values()->toArray();
        $studentIds = $enrollments->pluck('student_id')->unique()->values()->toArray();

        $materialProgresses = LmsMaterialProgress::whereIn('student_id', $studentIds)
            ->whereIn('material_id', $allMatIds)
            ->get()
            ->groupBy('student_id');

        $submissions = LmsSubmission::whereIn('student_id', $studentIds)
            ->whereIn('assignment_id', $allAssignIds)
            ->get()
            ->groupBy('student_id');

        $quizAttempts = LmsQuizAttempt::whereIn('student_id', $studentIds)
            ->whereIn('quiz_id', $allQuizIds)
            ->whereNotNull('finished_at')
            ->get()
            ->groupBy('student_id');

        $filename = $isAll 
            ? 'Rekap_Progres_LMS_Semua_Mapel_' . date('Ymd_His') . '.csv'
            : 'Rekap_Progres_LMS_' . str_replace(' ', '_', $courses->first()->course_name) . '_' . date('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($enrollments, $courses, $isAll, $materialProgresses, $submissions, $quizAttempts) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // UTF-8 BOM

            // Header info
            fputcsv($handle, ['REKAPITULASI PROGRES PEMBELAJARAN LMS GURU']);
            fputcsv($handle, ['Cakupan', $isAll ? 'Semua Mata Pelajaran & Kelas yang Diajar' : $courses->first()->course_name]);
            fputcsv($handle, ['Total Kursus', $courses->count()]);
            fputcsv($handle, ['Total Siswa Terdaftar', $enrollments->count()]);
            fputcsv($handle, ['Tanggal Ekspor', date('d/m/Y H:i')]);
            fputcsv($handle, []);

            // Table headers
            fputcsv($handle, [
                'No',
                'Mata Pelajaran',
                'Nama Kursus',
                'Kelas / Rombel',
                'Nama Siswa',
                'NISN',
                'Materi Selesai',
                'Total Materi',
                'Progres Materi (%)',
                'Tugas Dikumpulkan',
                'Total Tugas',
                'Rata-rata Nilai Tugas',
                'Kuis Selesai',
                'Total Kuis',
                'Rata-rata Nilai Kuis',
                'Total Capaian (%)',
                'Status Pembelajaran',
            ]);

            $no = 1;
            foreach ($enrollments as $enr) {
                $st = $enr->student;
                $c = $enr->lmsClass?->course;
                if (!$st || !$c) continue;

                $lmsClass = $enr->lmsClass?->classroom;

                // Validasi: Siswa HARUS benar-benar terdaftar di classroom
                if ($lmsClass) {
                    $isActualMember = $lmsClass->students()->where('students.id', $st->id)->exists();
                    if (!$isActualMember) continue;
                }

                // Filter Otomatis Kejuruan
                $subjName = $c->subject?->name ?? $c->subject?->subject_name;
                $vocKeywords = $this->getSubjectMajorKeywords($subjName, $c->subject?->code, $c->course_name);
                if (!$this->isStudentMatchingVocationalSubject($st, $vocKeywords, $lmsClass)) {
                    continue;
                }

                $cMatIds = $c->materials->pluck('id')->toArray();
                $cAssignIds = $c->assignments->pluck('id')->toArray();
                $cQuizIds = $c->quizzes->pluck('id')->toArray();

                $totalMats = count($cMatIds);
                $totalAssigns = count($cAssignIds);
                $totalQuizzes = count($cQuizIds);

                $stMat = $materialProgresses->get($st->id, collect());
                $stSub = $submissions->get($st->id, collect());
                $stQuiz = $quizAttempts->get($st->id, collect());

                $completedMats = $stMat->whereIn('material_id', $cMatIds)->where('status', 'completed')->count();
                $submittedAssigns = $stSub->whereIn('assignment_id', $cAssignIds)->whereIn('status', ['submitted', 'graded'])->count();
                $completedQuizzes = $stQuiz->whereIn('quiz_id', $cQuizIds)->count();

                $matPct = $totalMats > 0 ? round(($completedMats / $totalMats) * 100) : 100;
                $assignPct = $totalAssigns > 0 ? round(($submittedAssigns / $totalAssigns) * 100) : 100;
                $quizPct = $totalQuizzes > 0 ? round(($completedQuizzes / $totalQuizzes) * 100) : 100;

                $overallPct = round(($matPct * 0.5) + ($assignPct * 0.3) + ($quizPct * 0.2));

                $avgAssignGrade = $stSub->whereIn('assignment_id', $cAssignIds)->whereNotNull('grade')->avg('grade');
                $avgQuizScore = $stQuiz->whereIn('quiz_id', $cQuizIds)->whereNotNull('score')->avg('score');

                $courseYearId = $c->academic_year_id ?? $lmsClass?->academic_year_id;
                $activeClass = $st->classrooms
                    ->where('academic_year_id', $courseYearId)
                    ->where('is_active', true)
                    ->first() ?? $lmsClass ?? $st->classrooms->first();

                $className = $activeClass?->class_name ?? $lmsClass?->class_name ?? '-';
                $status = ($overallPct < 40 || ($totalAssigns > 1 && $submittedAssigns === 0)) ? 'Perlu Perhatian' : ($overallPct >= 85 ? 'Sangat Aktif' : 'On Track');

                fputcsv($handle, [
                    $no++,
                    $c->subject?->name ?? $c->subject?->subject_name ?? '-',
                    $c->course_name,
                    $className,
                    $st->full_name,
                    $st->nisn ?? '-',
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

    /**
     * Deteksi kata kunci Kejuruan untuk mata pelajaran produktif SMK (DDTK / Konsentrasi Keahlian)
     */
    protected function getSubjectMajorKeywords(?string $subjectName, ?string $subjectCode = null, ?string $courseName = null): ?array
    {
        return \App\Services\VocationalMajorFilterService::getSubjectMajorKeywords($subjectName, $subjectCode, $courseName);
    }

    /**
     * Periksa apakah siswa relevan dengan mata pelajaran kejuruan ini.
     */
    protected function isStudentMatchingVocationalSubject(Student $student, ?array $subjectKeywords, ?Classroom $lmsClass): bool
    {
        return \App\Services\VocationalMajorFilterService::isStudentMatchingVocationalSubject($student, $subjectKeywords, $lmsClass);
    }
}
