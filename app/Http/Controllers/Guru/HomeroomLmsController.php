<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\LmsCourse;
use App\Models\LmsMaterialProgress;
use App\Models\LmsQuizAttempt;
use App\Models\LmsSubmission;
use App\Models\Student;
use App\Models\StudentCounselingRecord;
use App\Models\Teacher;
use Illuminate\Http\Request;
use App\Models\Semester;
use Illuminate\Support\Facades\Auth;

class HomeroomLmsController extends Controller
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
     * Dashboard Pantauan Progres LMS untuk Wali Kelas
     */
    public function index(Request $request)
    {
        $teacher = $this->getTeacher();
        $activeYear = AcademicYear::where('is_active', true)->first() ?? AcademicYear::latest()->first();

        $tIds = \Illuminate\Support\Facades\Auth::user() ? \Illuminate\Support\Facades\Auth::user()->teacherIds() : ($teacher ? $teacher->allTeacherIds() : []);

        // 1. Ambil rombel-rombel yang diampu sebagai Wali Kelas pada tahun pelajaran aktif
        $homeroomClassrooms = Classroom::whereIn('homeroom_teacher_id', $tIds)
            ->where('is_active', true)
            ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->with(['school'])
            ->get();

        if ($homeroomClassrooms->isEmpty()) {
            $homeroomClassrooms = Classroom::whereIn('homeroom_teacher_id', $tIds)
                ->where('is_active', true)
                ->with(['school'])
                ->get();
        }

        $isWaliKelas = $homeroomClassrooms->isNotEmpty();
        if (!$isWaliKelas) {
            return redirect()->route('guru.dashboard')
                ->with('error', 'Akses ditolak. Anda tidak terdaftar sebagai Wali Kelas pada tahun pelajaran aktif.');
        }

        // 2. Tentukan rombel terpilih
        $selectedClassroomId = $request->query('classroom_id', $homeroomClassrooms->first()->id);
        $classroom = $homeroomClassrooms->firstWhere('id', $selectedClassroomId) ?? $homeroomClassrooms->first();

        // 3. Ambil seluruh siswa aktif di rombel terpilih
        $students = Student::whereHas('studentClasses', function ($q) use ($classroom, $activeYear) {
            $q->where('classroom_id', $classroom->id)
              ->whereIn('status', ['aktif', 'enrolled', 'active'])
              ->when($activeYear, fn($sq) => $sq->where('academic_year_id', $activeYear->id));
        })
        ->with(['user', 'parents'])
        ->orderBy('full_name')
        ->get();

        if ($students->isEmpty()) {
            // Fallback tanpa filter tahun ajaran
            $students = Student::whereHas('studentClasses', function ($q) use ($classroom) {
                $q->where('classroom_id', $classroom->id)
                  ->whereIn('status', ['aktif', 'enrolled', 'active']);
            })
            ->with(['user', 'parents'])
            ->orderBy('full_name')
            ->get();
        }

        // 4. Ambil seluruh kursus LMS yang terhubung ke rombel ini
        $courses = LmsCourse::where(function ($q) use ($classroom) {
            $q->where('classroom_id', $classroom->id)
              ->orWhereHas('lmsClasses', fn($lq) => $lq->where('classroom_id', $classroom->id));
        })
        ->where(function ($q) {
            $q->where('is_published', true)
              ->orWhere('status', 'active')
              ->orWhere('is_active', true);
        })
        ->with([
            'subject',
            'teacher.user',
            'materials' => fn($q) => $q->where('is_published', true),
            'assignments' => fn($q) => $q->where('is_published', true),
            'quizzes' => fn($q) => $q->where('is_published', true),
        ])
        ->get();

        $allMaterialIds = $courses->flatMap(fn($c) => $c->materials->pluck('id'))->unique()->values()->toArray();
        $allAssignmentIds = $courses->flatMap(fn($c) => $c->assignments->pluck('id'))->unique()->values()->toArray();
        $allQuizIds = $courses->flatMap(fn($c) => $c->quizzes->pluck('id'))->unique()->values()->toArray();

        $studentIds = $students->pluck('id')->toArray();

        // Preload seluruh progress agar query efisien (Eager Calculation)
        $materialProgresses = LmsMaterialProgress::whereIn('student_id', $studentIds)
            ->whereIn('material_id', $allMaterialIds)
            ->get()
            ->groupBy('student_id');

        $submissions = LmsSubmission::whereIn('student_id', $studentIds)
            ->whereIn('assignment_id', $allAssignmentIds)
            ->whereIn('status', ['submitted', 'graded'])
            ->get()
            ->groupBy('student_id');

        $quizAttempts = LmsQuizAttempt::whereIn('student_id', $studentIds)
            ->whereIn('quiz_id', $allQuizIds)
            ->whereNotNull('finished_at')
            ->get()
            ->groupBy('student_id');

        // 5. Kalkulasi Matriks Progres Per Siswa
        $studentProgressList = [];
        $totalClassMaterials = count($allMaterialIds);
        $totalClassAssignments = count($allAssignmentIds);
        $totalClassQuizzes = count($allQuizIds);

        foreach ($students as $st) {
            $stMatProgress = $materialProgresses->get($st->id, collect());
            $stSubmissions = $submissions->get($st->id, collect());
            $stQuizzes = $quizAttempts->get($st->id, collect());

            $completedMats = $stMatProgress->where('status', 'completed')->count();
            $submittedTasks = $stSubmissions->count();
            $completedQuizzes = $stQuizzes->count();

            // Kalkulasi per mata pelajaran
            $courseBreakdown = [];
            $coursePercentages = [];

            foreach ($courses as $c) {
                $cMatIds = $c->materials->pluck('id')->toArray();
                $cAssignIds = $c->assignments->pluck('id')->toArray();
                $cQuizIds = $c->quizzes->pluck('id')->toArray();

                $cMatTotal = count($cMatIds);
                $cAssignTotal = count($cAssignIds);
                $cQuizTotal = count($cQuizIds);

                $cMatCompleted = $stMatProgress->whereIn('material_id', $cMatIds)->where('status', 'completed')->count();
                $cAssignSubmitted = $stSubmissions->whereIn('assignment_id', $cAssignIds)->count();
                $cQuizAttempted = $stQuizzes->whereIn('quiz_id', $cQuizIds)->count();

                // Bobot kursus: 50% materi, 30% tugas, 20% kuis (atau proporsional terhadap komponen yang ada)
                $itemsCount = 0;
                $sumPct = 0;

                if ($cMatTotal > 0) {
                    $matPct = ($cMatCompleted / $cMatTotal) * 100;
                    $sumPct += $matPct;
                    $itemsCount++;
                }
                if ($cAssignTotal > 0) {
                    $assignPct = ($cAssignSubmitted / $cAssignTotal) * 100;
                    $sumPct += $assignPct;
                    $itemsCount++;
                }
                if ($cQuizTotal > 0) {
                    $quizPct = ($cQuizAttempted / $cQuizTotal) * 100;
                    $sumPct += $quizPct;
                    $itemsCount++;
                }

                $coursePct = $itemsCount > 0 ? round($sumPct / $itemsCount) : 0;
                $coursePercentages[] = $coursePct;

                $courseBreakdown[] = [
                    'course_id' => $c->id,
                    'course_name' => $c->course_name,
                    'subject_name' => $c->subject?->name ?? $c->subject?->subject_name ?? $c->course_name,
                    'teacher_name' => $c->teacher?->user?->name ?? $c->teacher?->full_name ?? 'Pengajar',
                    'materials_total' => $cMatTotal,
                    'materials_completed' => $cMatCompleted,
                    'assignments_total' => $cAssignTotal,
                    'assignments_submitted' => $cAssignSubmitted,
                    'quizzes_total' => $cQuizTotal,
                    'quizzes_completed' => $cQuizAttempted,
                    'progress_pct' => $coursePct,
                ];
            }

            $overallProgress = count($coursePercentages) > 0 ? round(array_sum($coursePercentages) / count($coursePercentages)) : 0;

            // Tentukan status & tingkat risiko
            $status = 'active';
            $statusLabel = 'On Track';
            $statusColor = 'emerald';
            $isAtRisk = false;

            if ($overallProgress >= 85) {
                $status = 'excellent';
                $statusLabel = 'Sangat Aktif';
                $statusColor = 'indigo';
            } elseif ($overallProgress < 40 || ($totalClassAssignments > 2 && $submittedTasks === 0)) {
                $status = 'at_risk';
                $statusLabel = 'Perlu Bimbingan';
                $statusColor = 'rose';
                $isAtRisk = true;
            } elseif ($overallProgress < 65) {
                $status = 'moderate';
                $statusLabel = 'Cukup Aktif';
                $statusColor = 'amber';
            }

            // Ambil nomor WhatsApp orang tua dan siswa secara terpisah
            $parent = $st->parents->first();
            $parentPhone = $parent?->phone ?? $parent?->whatsapp ?? null;
            if ($parentPhone) {
                $parentPhone = preg_replace('/[^0-9]/', '', $parentPhone);
                if (str_starts_with($parentPhone, '0')) {
                    $parentPhone = '62' . substr($parentPhone, 1);
                }
            }

            $studentPhone = $st->phone ?? $st->whatsapp ?? null;
            if ($studentPhone) {
                $studentPhone = preg_replace('/[^0-9]/', '', $studentPhone);
                if (str_starts_with($studentPhone, '0')) {
                    $studentPhone = '62' . substr($studentPhone, 1);
                }
            }

            $studentProgressList[] = [
                'student' => $st,
                'overall_progress' => $overallProgress,
                'completed_materials' => $completedMats,
                'submitted_tasks' => $submittedTasks,
                'completed_quizzes' => $completedQuizzes,
                'status' => $status,
                'status_label' => $statusLabel,
                'status_color' => $statusColor,
                'is_at_risk' => $isAtRisk,
                'phone' => $parentPhone ?: $studentPhone,
                'parent_phone' => $parentPhone,
                'student_phone' => $studentPhone,
                'parent_name' => $parent?->father_name ?? $parent?->mother_name ?? $parent?->guardian_name ?? 'Orang Tua / Wali',
                'courses' => $courseBreakdown,
            ];
        }

        // Sorting & Filter
        $filterStatus = $request->query('status', 'all');
        $search = strtolower(trim($request->query('search', '')));

        $filteredList = collect($studentProgressList)->filter(function ($item) use ($filterStatus, $search) {
            if ($filterStatus === 'at_risk' && !$item['is_at_risk']) {
                return false;
            }
            if ($filterStatus === 'excellent' && $item['status'] !== 'excellent') {
                return false;
            }
            if ($filterStatus === 'on_track' && !in_array($item['status'], ['active', 'moderate', 'excellent'])) {
                return false;
            }
            if (!empty($search)) {
                $nameMatch = str_contains(strtolower($item['student']->full_name), $search);
                $nisnMatch = str_contains(strtolower($item['student']->nisn ?? ''), $search);
                return $nameMatch || $nisnMatch;
            }
            return true;
        })->values();

        // 6. Summary KPI Kelas
        $kpi = [
            'total_students' => $students->count(),
            'total_courses' => $courses->count(),
            'total_materials' => $totalClassMaterials,
            'total_assignments' => $totalClassAssignments,
            'total_quizzes' => $totalClassQuizzes,
            'avg_class_progress' => count($studentProgressList) > 0 ? round(collect($studentProgressList)->avg('overall_progress')) : 0,
            'at_risk_count' => collect($studentProgressList)->where('is_at_risk', true)->count(),
            'excellent_count' => collect($studentProgressList)->where('status', 'excellent')->count(),
            'on_track_count' => collect($studentProgressList)->whereIn('status', ['active', 'moderate'])->count(),
        ];

        // Ambil riwayat motivasi / pembinaan terbaru di rombel ini
        $recentMotivations = StudentCounselingRecord::whereIn('student_id', $studentIds)
            ->where('category', 'akademik')
            ->where('counselor_id', $teacher->user_id)
            ->with('student')
            ->latest('incident_date')
            ->take(5)
            ->get();

        return view('guru.walikelas.lms_monitoring', compact(
            'teacher',
            'homeroomClassrooms',
            'classroom',
            'courses',
            'filteredList',
            'studentProgressList',
            'kpi',
            'filterStatus',
            'search',
            'recentMotivations',
            'activeYear'
        ));
    }

    /**
     * AJAX/Modal Detail Progres Siswa untuk Seluruh Mata Pelajaran
     */
    public function studentDetail(Student $student)
    {
        $teacher = $this->getTeacher();
        $activeYear = AcademicYear::where('is_active', true)->first() ?? AcademicYear::latest()->first();

        // Verifikasi apakah siswa berada di rombel yang diampu oleh wali kelas ini
        $tIds = Auth::user() ? Auth::user()->teacherIds() : ($teacher ? $teacher->allTeacherIds() : []);
        $isMyStudent = Classroom::whereIn('homeroom_teacher_id', $tIds)
            ->whereHas('students', fn($q) => $q->where('students.id', $student->id))
            ->exists();

        if (!$isMyStudent && !Auth::user()->hasAnyRole(['admin', 'superadmin', 'kepala_sekolah'])) {
            return response()->json(['error' => 'Akses ditolak. Siswa bukan anggota rombel Anda.'], 403);
        }

        $student->load(['school', 'currentClassroom', 'classrooms', 'user', 'parents']);

        // Ambil kelas aktif siswa
        $activeClass = $student->currentClassroom->first() ?? $student->classrooms->first();
        $classroomId = $activeClass?->id;
        $courses = LmsCourse::where(function ($q) use ($classroomId) {
            $q->where('classroom_id', $classroomId)
              ->orWhereHas('lmsClasses', fn($lq) => $lq->where('classroom_id', $classroomId));
        })
        ->where(function ($q) {
            $q->where('is_published', true)
              ->orWhere('status', 'active')
              ->orWhere('is_active', true);
        })
        ->with([
            'subject',
            'teacher.user',
            'modules.materials' => fn($q) => $q->where('is_published', true),
            'materials' => fn($q) => $q->where('is_published', true),
            'assignments' => fn($q) => $q->where('is_published', true),
            'quizzes' => fn($q) => $q->where('is_published', true),
        ])
        ->get();

        $courseDetails = [];
        foreach ($courses as $course) {
            $allMats = $course->materials;
            $completedMatIds = LmsMaterialProgress::where('student_id', $student->id)
                ->whereIn('material_id', $allMats->pluck('id'))
                ->where('status', 'completed')
                ->pluck('material_id')
                ->toArray();

            $submissions = LmsSubmission::where('student_id', $student->id)
                ->whereIn('assignment_id', $course->assignments->pluck('id'))
                ->get()
                ->keyBy('assignment_id');

            $quizAttempts = LmsQuizAttempt::where('student_id', $student->id)
                ->whereIn('quiz_id', $course->quizzes->pluck('id'))
                ->get()
                ->groupBy('quiz_id');

            $matPct = $allMats->count() > 0 ? round((count($completedMatIds) / $allMats->count()) * 100) : 100;
            $assignPct = $course->assignments->count() > 0 ? round(($submissions->count() / $course->assignments->count()) * 100) : 100;
            $quizPct = $course->quizzes->count() > 0 ? round(($quizAttempts->count() / $course->quizzes->count()) * 100) : 100;

            $overall = round(($matPct * 0.5) + ($assignPct * 0.3) + ($quizPct * 0.2));

            $courseDetails[] = [
                'id' => $course->id,
                'name' => $course->course_name,
                'subject' => $course->subject?->name ?? $course->subject?->subject_name ?? '-',
                'teacher' => $course->teacher?->user?->name ?? $course->teacher?->full_name ?? 'Pengajar',
                'overall_pct' => $overall,
                'materials_total' => $allMats->count(),
                'materials_completed' => count($completedMatIds),
                'materials' => $allMats->map(fn($m) => [
                    'id' => $m->id,
                    'title' => $m->title,
                    'type' => $m->material_type,
                    'is_completed' => in_array($m->id, $completedMatIds),
                ]),
                'assignments_total' => $course->assignments->count(),
                'assignments_submitted' => $submissions->count(),
                'assignments' => $course->assignments->map(fn($a) => [
                    'id' => $a->id,
                    'title' => $a->title,
                    'deadline' => $a->deadline ? $a->deadline->format('d M Y H:i') : '-',
                    'is_submitted' => $submissions->has($a->id),
                    'grade' => $submissions->get($a->id)?->grade,
                ]),
                'quizzes_total' => $course->quizzes->count(),
                'quizzes_completed' => $quizAttempts->count(),
                'quizzes' => $course->quizzes->map(fn($q) => [
                    'id' => $q->id,
                    'title' => $q->title,
                    'attempts_count' => $quizAttempts->get($q->id, collect())->count(),
                    'highest_score' => $quizAttempts->get($q->id, collect())->max('score'),
                ]),
            ];
        }

        // Ambil riwayat pembinaan / catatan motivasi siswa
        $counselingRecords = StudentCounselingRecord::where('student_id', $student->id)
            ->where('category', 'akademik')
            ->latest('incident_date')
            ->take(5)
            ->get();

        $parent = $student->parents->first();
        $parentPhone = $parent?->phone ?? $parent?->whatsapp ?? null;
        if ($parentPhone) {
            $parentPhone = preg_replace('/[^0-9]/', '', $parentPhone);
            if (str_starts_with($parentPhone, '0')) {
                $parentPhone = '62' . substr($parentPhone, 1);
            }
        }

        $studentPhone = $student->phone ?? $student->whatsapp ?? null;
        if ($studentPhone) {
            $studentPhone = preg_replace('/[^0-9]/', '', $studentPhone);
            if (str_starts_with($studentPhone, '0')) {
                $studentPhone = '62' . substr($studentPhone, 1);
            }
        }

        return response()->json([
            'student' => [
                'id' => $student->id,
                'name' => $student->full_name,
                'nisn' => $student->nisn,
                'classroom' => $activeClass?->class_name,
                'photo' => $student->photo_url,
                'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($student->full_name) . '&background=4f46e5&color=fff',
                'parent_name' => $parent?->father_name ?? $parent?->mother_name ?? 'Orang Tua / Wali',
                'parent_phone' => $parentPhone,
                'student_phone' => $studentPhone,
            ],
            'courses' => $courseDetails,
            'counseling_records' => $counselingRecords,
        ]);
    }

    /**
     * Kirim & Simpan Catatan Motivasi / Bimbingan Siswa oleh Wali Kelas
     */
    public function sendMotivation(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'message_type' => 'required|in:motivasi,peringatan,apresiasi,evaluasi',
            'target_recipient' => 'nullable|in:parent,student',
            'note' => 'required|string|max:1000',
            'target_phone' => 'nullable|string',
        ]);

        $teacher = $this->getTeacher();
        $student = Student::with(['school', 'currentClassroom', 'parents'])->findOrFail($request->student_id);
        $activeYear = AcademicYear::where('is_active', true)->first() ?? AcademicYear::latest()->first();
        $activeSemester = Semester::where('is_active', true)->first() ?? Semester::latest()->first();

        $titles = [
            'motivasi' => 'Pesan Semangat & Motivasi Belajar LMS',
            'peringatan' => 'Peringatan & Evaluasi Keterlambatan Tugas LMS',
            'apresiasi' => 'Apresiasi & Pujian Prestasi Aktif Belajar LMS',
            'evaluasi' => 'Catatan Evaluasi Progres Pembelajaran LMS',
        ];

        $targetRecipient = $request->input('target_recipient', 'parent');
        $teacherName = $teacher?->user?->name ?? $teacher?->full_name ?? Auth::user()->name;
        $schoolName = $student->school?->name ?? 'Perguruan Pembda Nias';
        $className = $student->currentClassroom->first()?->class_name ?? 'Kelas';

        $actionDesc = $targetRecipient === 'student'
            ? 'Pemberian bimbingan & dorongan motivasi LMS langsung ke siswa oleh Wali Kelas (' . $teacherName . ')'
            : 'Pemberian bimbingan & dorongan motivasi LMS kepada Orang Tua oleh Wali Kelas (' . $teacherName . ')';

        // 1. Simpan ke rekaman bimbingan siswa
        $record = StudentCounselingRecord::create([
            'student_id' => $student->id,
            'school_id' => $student->school_id,
            'academic_year_id' => $activeYear?->id,
            'semester_id' => $activeSemester?->id ?? 1,
            'record_type' => $request->message_type === 'apresiasi' ? 'penghargaan' : 'bimbingan',
            'category' => 'akademik',
            'title' => $titles[$request->message_type] ?? 'Catatan Wali Kelas',
            'description' => $request->note,
            'action_taken' => $actionDesc,
            'incident_date' => now(),
            'status' => 'selesai',
            'counselor_id' => $teacher?->user_id ?? Auth::id(),
            'parent_notified' => ($targetRecipient === 'parent' && !empty($request->target_phone)),
            'parent_notified_date' => ($targetRecipient === 'parent' && !empty($request->target_phone)) ? now() : null,
        ]);

        // 2. Buat tautan WhatsApp jika nomor tujuan tersedia
        $waUrl = null;
        if (!empty($request->target_phone)) {
            $phone = preg_replace('/[^0-9]/', '', $request->target_phone);
            if (str_starts_with($phone, '0')) {
                $phone = '62' . substr($phone, 1);
            }

            if ($targetRecipient === 'student') {
                $waText = "Halo ananda *{$student->full_name}* ({$className}),\n\n"
                        . "Saya *{$teacherName}* selaku Wali Kelasmu di {$schoolName}.\n\n"
                        . "📢 *Catatan Pantauan Pembelajaran Digital (LMS):*\n"
                        . "_{$request->note}_\n\n"
                        . "Tetap semangat belajar, giat membaca materi, dan selesaikan tugas-tugas LMS tepat waktu ya! Kamu pasti bisa meraih prestasi terbaik! 💪✨\n\n"
                        . "_Sistem PembdaHUB - Ekosistem Digital Yayasan Perguruan PEMBDA Nias_";
            } else {
                $waText = "Halo Bapak/Ibu Wali dari ananda *{$student->full_name}* ({$className}),\n\n"
                        . "Saya *{$teacherName}* selaku Wali Kelas di {$schoolName}.\n\n"
                        . "📢 *Catatan Pantauan Pembelajaran Digital (LMS):*\n"
                        . "_{$request->note}_\n\n"
                        . "Mari bersama-sama kita motivasi dan dampingi ananda agar selalu giat belajar dan menyelesaikan materi/tugas tepat waktu. Terima kasih banyak atas kerja sama yang baik! 🙏✨\n\n"
                        . "_Sistem PembdaHUB - Ekosistem Digital Yayasan Perguruan PEMBDA Nias_";
            }

            $waUrl = "https://wa.me/{$phone}?text=" . urlencode($waText);
        }

        return response()->json([
            'success' => true,
            'message' => 'Catatan motivasi berhasil disimpan dan direkam.',
            'wa_url' => $waUrl,
            'record' => $record,
        ]);
    }

    /**
     * Cetak Rekapitulasi Progres LMS Rombel
     */
    public function printRekap(Request $request)
    {
        $teacher = $this->getTeacher();
        $activeYear = AcademicYear::where('is_active', true)->first() ?? AcademicYear::latest()->first();

        $classroomId = $request->query('classroom_id');
        $tIds = Auth::user() ? Auth::user()->teacherIds() : ($teacher ? $teacher->allTeacherIds() : []);
        $classroom = Classroom::where('id', $classroomId)
            ->whereIn('homeroom_teacher_id', $tIds)
            ->with(['school'])
            ->firstOrFail();

        $students = Student::whereHas('studentClasses', function ($q) use ($classroom, $activeYear) {
            $q->where('classroom_id', $classroom->id)
              ->whereIn('status', ['aktif', 'enrolled', 'active'])
              ->when($activeYear, fn($sq) => $sq->where('academic_year_id', $activeYear->id));
        })
        ->with('parents')
        ->orderBy('full_name')
        ->get();

        $courses = LmsCourse::where(function ($q) use ($classroom) {
            $q->where('classroom_id', $classroom->id)
              ->orWhereHas('lmsClasses', fn($lq) => $lq->where('classroom_id', $classroom->id));
        })
        ->where(function ($q) {
            $q->where('is_published', true)
              ->orWhere('status', 'active')
              ->orWhere('is_active', true);
        })
        ->with(['subject', 'teacher.user', 'materials', 'assignments', 'quizzes'])
        ->get();

        $allMaterialIds = $courses->flatMap(fn($c) => $c->materials->pluck('id'))->unique()->values()->toArray();
        $allAssignmentIds = $courses->flatMap(fn($c) => $c->assignments->pluck('id'))->unique()->values()->toArray();
        $allQuizIds = $courses->flatMap(fn($c) => $c->quizzes->pluck('id'))->unique()->values()->toArray();

        $studentIds = $students->pluck('id')->toArray();

        $materialProgresses = LmsMaterialProgress::whereIn('student_id', $studentIds)
            ->whereIn('material_id', $allMaterialIds)
            ->where('status', 'completed')
            ->get()
            ->groupBy('student_id');

        $submissions = LmsSubmission::whereIn('student_id', $studentIds)
            ->whereIn('assignment_id', $allAssignmentIds)
            ->whereIn('status', ['submitted', 'graded'])
            ->get()
            ->groupBy('student_id');

        $quizAttempts = LmsQuizAttempt::whereIn('student_id', $studentIds)
            ->whereIn('quiz_id', $allQuizIds)
            ->whereNotNull('finished_at')
            ->get()
            ->groupBy('student_id');

        $rekapData = [];
        foreach ($students as $st) {
            $stMats = $materialProgresses->get($st->id, collect())->count();
            $stTasks = $submissions->get($st->id, collect())->count();
            $stQuizzes = $quizAttempts->get($st->id, collect())->count();

            $totalMats = count($allMaterialIds);
            $totalTasks = count($allAssignmentIds);
            $totalQuizzes = count($allQuizIds);

            $matPct = $totalMats > 0 ? ($stMats / $totalMats) * 100 : 100;
            $taskPct = $totalTasks > 0 ? ($stTasks / $totalTasks) * 100 : 100;
            $quizPct = $totalQuizzes > 0 ? ($stQuizzes / $totalQuizzes) * 100 : 100;

            $overallPct = round(($matPct * 0.5) + ($taskPct * 0.3) + ($quizPct * 0.2));

            $rekapData[] = [
                'student' => $st,
                'completed_materials' => $stMats,
                'total_materials' => $totalMats,
                'submitted_tasks' => $stTasks,
                'total_tasks' => $totalTasks,
                'completed_quizzes' => $stQuizzes,
                'total_quizzes' => $totalQuizzes,
                'overall_pct' => $overallPct,
            ];
        }

        return view('guru.walikelas.lms_monitoring_print', compact(
            'teacher',
            'classroom',
            'courses',
            'rekapData',
            'activeYear'
        ));
    }
}
