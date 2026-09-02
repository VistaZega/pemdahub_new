<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lms\StoreLmsCourseRequest;
use App\Http\Requests\Lms\UpdateLmsCourseRequest;
use App\Http\Requests\Lms\StoreLmsModuleRequest;
use App\Http\Requests\Lms\StoreLmsMaterialRequest;
use App\Models\LmsCourse;
use App\Models\LmsModule;
use App\Models\LmsMaterial;
use App\Models\LmsClass;
use App\Models\LmsEnrollment;
use App\Models\LmsMeetingSession;
use App\Models\LmsMeetingAttendance;
use App\Models\LmsMaterialProgress;
use App\Models\LmsSubmission;
use App\Models\LmsQuizAttempt;
use App\Models\LmsDiscussionReply;
use App\Models\Notification;
use App\Models\Teacher;
use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LmsCourseController extends Controller
{
    use HasMultiSchool;
    private function getTeacher(): ?Teacher
    {
        $user = Auth::user();
        return Teacher::where('user_id', $user->id)->first();
    }

    private function getActiveSemester(): ?Semester
    {
        return Semester::where('is_active', true)->first();
    }

    private function authorizeAccess(LmsCourse $course, Teacher $teacher): bool
    {
        return $course->teacher_id === $teacher->id;
    }

    /**
     * List all courses for this teacher
     */
    public function index(Request $request)
    {
        $teacher = $this->getTeacher();
        if (!$teacher) {
            return redirect()->route('guru.dashboard')->with('error', 'Data guru tidak ditemukan.');
        }

        $activeSemester = $this->getActiveSemester();
        $classrooms = \App\Models\Classroom::whereHas('lmsClasses.course', function($q) use ($teacher) {
            $q->where('teacher_id', $teacher->id);
        })
        ->orderBy('class_name')
        ->get();

        $selectedClassroomId = $request->query('classroom_id');
        
        $courses = LmsCourse::where('teacher_id', $teacher->id)
            ->when($selectedClassroomId, function($q) use ($selectedClassroomId) {
                $q->whereHas('lmsClasses', fn($sq) => $sq->where('classroom_id', $selectedClassroomId));
            })
            ->when(!$selectedClassroomId, function($q) {
                // Tanpa filter: tampilkan semua course yang punya rombel
                $q->whereHas('lmsClasses');
            })
            ->with(['subject', 'semester', 'classroom', 'lmsClasses.classroom'])
            ->withCount(['materials', 'assignments', 'quizzes'])
            ->orderByDesc('created_at')
            ->paginate(12)->withQueryString();

        // Orphan courses: milik guru, tidak punya rombel
        $orphanCourses = LmsCourse::where('teacher_id', $teacher->id)
            ->whereDoesntHave('lmsClasses')
            ->with(['subject', 'semester'])
            ->withCount(['materials', 'assignments', 'quizzes'])
            ->get();

        // Rombel yang dipilih untuk context banner
        $selectedClassroom = $selectedClassroomId
            ? $classrooms->firstWhere('id', $selectedClassroomId)
            : null;

        return view('guru.lms.index', compact(
            'teacher', 'courses', 'activeSemester',
            'classrooms', 'selectedClassroomId', 'selectedClassroom',
            'orphanCourses'
        ));
    }

    public function indexByClassroom($classroomId)
    {
        return redirect()->route('guru.lms.index', ['classroom_id' => $classroomId]);
    }

    /**
     * Show create course form
     */
    public function create()
    {
        $teacher = $this->getTeacher();
        if (!$teacher) {
            return redirect()->route('guru.dashboard')->with('error', 'Data guru tidak ditemukan.');
        }
        $teacher->load('school');

        $activeSemester = $this->getActiveSemester();
        $activeYear = AcademicYear::where('is_active', true)->first();

        // Ambil mata pelajaran HANYA dari Teaching Assignment aktif
        $teachingSubjectIds = \App\Models\TeachingAssignment::where('teacher_id', $teacher->id)
            ->where('is_active', true)
            ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->pluck('subject_id')
            ->unique();

        // Hanya tampilkan mapel yang ada penugasan aktif
        $subjects = \App\Models\Subject::whereIn('id', $teachingSubjectIds)
            ->where('is_active', true)
            ->orderBy('subject_name')
            ->get();

        // Get classrooms from teacher's school for active academic year
        $classroomIds = collect();

        // From schedules
        if (method_exists($teacher, 'schedules')) {
            $scheduleClassrooms = $teacher->schedules()
                ->with('classroom')
                ->get()
                ->pluck('classroom')
                ->filter()
                ->pluck('id');
            $classroomIds = $classroomIds->merge($scheduleClassrooms);
        }

        // From teaching assignments
        $teachingClassrooms = \App\Models\TeachingAssignment::where('teacher_id', $teacher->id)
            ->where('is_active', true)
            ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->pluck('classroom_id');
        $classroomIds = $classroomIds->merge($teachingClassrooms);

        // From homeroom
        $homeroomClassrooms = \App\Models\Classroom::where('homeroom_teacher_id', $teacher->id)
            ->where('is_active', true)
            ->pluck('id');
        $classroomIds = $classroomIds->merge($homeroomClassrooms);

        // Hanya tampilkan kelas yang terkait dengan guru (tanpa fallback ke semua kelas)
        $classrooms = \App\Models\Classroom::whereIn('id', $classroomIds->unique())
            ->where('is_active', true)
            ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->orderBy('class_name')
            ->get();

        $semesters = Semester::with('academicYear')
            ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->orderBy('semester_number')
            ->get();

        return view('guru.lms.create', compact('teacher', 'subjects', 'classrooms', 'semesters', 'activeSemester'));
    }

    /**
     * Store new course
     */
    public function store(StoreLmsCourseRequest $request)
    {
        $teacher = $this->getTeacher();
        if (!$teacher) {
            return redirect()->route('guru.dashboard')->with('error', 'Data guru tidak ditemukan.');
        }

        $validated = $request->validated();

        // 🛡️ Anti-Duplicate / Anti-Double Submit Check (within last 15 seconds)
        $recentDuplicate = LmsCourse::where('teacher_id', $teacher->id)
            ->where('subject_id', $request->subject_id)
            ->where('semester_id', $request->semester_id)
            ->where('course_name', $request->name)
            ->where('created_at', '>=', now()->subSeconds(15))
            ->first();

        if ($recentDuplicate) {
            return redirect()->route('guru.lms.show', $recentDuplicate->id)
                ->with('success', 'Course berhasil dibuat.');
        }

        $code = 'LMS-' . strtoupper(Str::random(8));

        // Use first classroom_id for the direct classroom_id column
        $firstClassroomId = $request->classroom_ids[0] ?? null;

        $course = LmsCourse::create([
            'school_id' => $this->getEffectiveSchoolId($teacher),
            'teacher_id' => $teacher->id,
            'subject_id' => $request->subject_id,
            'semester_id' => $request->semester_id,
            'classroom_id' => $firstClassroomId,
            'code' => $code,
            'course_name' => $request->name,
            'description' => $request->description,
            'status' => 'active',
            'is_published' => true,
            'is_active' => true,
            'is_sequential' => $request->has('is_sequential'),
        ]);

        // Assign classrooms via lms_classes and auto-enroll students
        if ($request->classroom_ids) {
            foreach ($request->classroom_ids as $classroomId) {
                $lmsClass = LmsClass::create([
                    'course_id' => $course->id,
                    'classroom_id' => $classroomId,
                    'school_id' => $this->getEffectiveSchoolId($teacher),
                    'status' => 'active',
                ]);

                // Auto-enroll active students
                $classroom = \App\Models\Classroom::find($classroomId);
                if ($classroom) {
                    $activeYear = AcademicYear::where('is_active', true)->first();
                    $students = $classroom->students();
                    if ($activeYear) {
                        $students = $students->wherePivot('academic_year_id', $activeYear->id);
                    }
                    $students = $students->wherePivot('status', 'aktif')->get();

                    foreach ($students as $student) {
                        LmsEnrollment::firstOrCreate([
                            'lms_class_id' => $lmsClass->id,
                            'student_id' => $student->id,
                        ], [
                            'status' => 'enrolled',
                            'enrolled_at' => now(),
                        ]);
                    }
                }
            }
        }

        app(\App\Services\LmsEnrollmentService::class)->syncCourseEnrollments($course);

        return redirect()->route('guru.lms.show', $course->id)
            ->with('success', 'Course berhasil dibuat.');
    }

    /**
     * Show course detail with materials, assignments, quizzes
     */
    public function show(LmsCourse $course)
    {
        $teacher = $this->getTeacher();
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403, 'Anda tidak memiliki akses ke course ini.');
        }
        $teacher->load('school');

        $course->load([
            'subject',
            'semester',
            'materials' => fn($q) => $q->orderBy('order_number'),
            'modules' => fn($q) => $q->orderBy('sequence')->with(['materials' => fn($mq) => $mq->orderBy('order_number')]),
            'assignments' => fn($q) => $q->orderByDesc('created_at')->withCount('submissions'),
            'quizzes' => fn($q) => $q->orderByDesc('created_at')->withCount('attempts'),
            'lmsClasses.classroom',
            'announcements' => fn($q) => $q->with('author')->orderByDesc('is_pinned')->orderByDesc('created_at'),
            'courseGroups' => fn($q) => $q->with(['leader.user', 'leader.studentClasses.classroom', 'members.user', 'members.studentClasses.classroom']),
        ]);

        $course->loadCount(['materials', 'assignments', 'quizzes', 'discussions', 'announcements', 'courseGroups']);

        // Ambil daftar rombel/kelas yang terhubung ke course ini
        $classrooms = $course->lmsClasses->pluck('classroom')->filter()->values();
        if ($classrooms->isEmpty() && $course->classroom_id) {
            $c = \App\Models\Classroom::find($course->classroom_id);
            if ($c) {
                $classrooms = collect([$c]);
            }
        }

        // Ambil data siswa untuk tab manajemen kelompok kursus
        $assignmentCtrl = app(LmsAssignmentController::class);
        $refMethod = new \ReflectionMethod($assignmentCtrl, 'getEnrolledStudentsForCourse');
        $refMethod->setAccessible(true);
        $allEnrolledStudents = $refMethod->invoke($assignmentCtrl, $course);
        $totalStudents = $allEnrolledStudents->count();

        return view('guru.lms.show', compact('teacher', 'course', 'totalStudents', 'allEnrolledStudents', 'classrooms'));
    }

    /**
     * Show edit form
     */
    public function edit(LmsCourse $course)
    {
        $teacher = $this->getTeacher();
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }
        $teacher->load('school');
        $activeYear = AcademicYear::where('is_active', true)->first();

        // Ambil mata pelajaran dari Teaching Assignment + kompetensi guru (tanpa fallback ke semua mapel)
        $subjectIds = collect();

        $teachingSubjectIds = \App\Models\TeachingAssignment::where('teacher_id', $teacher->id)
            ->where('is_active', true)
            ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->pluck('subject_id');
        $subjectIds = $subjectIds->merge($teachingSubjectIds);

        $competentSubjectIds = $teacher->competentSubjects()->pluck('subjects.id');
        $subjectIds = $subjectIds->merge($competentSubjectIds);

        $subjects = \App\Models\Subject::whereIn('id', $subjectIds->unique())
            ->where('is_active', true)
            ->orderBy('subject_name')
            ->get();

        $semesters = Semester::orderByDesc('start_date')->limit(4)->get();

        // Get classrooms from teacher's school
        $classroomIds = collect();

        if (method_exists($teacher, 'schedules')) {
            $scheduleClassrooms = $teacher->schedules()->with('classroom')->get()->pluck('classroom')->filter()->pluck('id');
            $classroomIds = $classroomIds->merge($scheduleClassrooms);
        }

        $teachingClassrooms = \App\Models\TeachingAssignment::where('teacher_id', $teacher->id)
            ->where('is_active', true)
            ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->pluck('classroom_id');
        $classroomIds = $classroomIds->merge($teachingClassrooms);

        $homeroomClassrooms = \App\Models\Classroom::where('homeroom_teacher_id', $teacher->id)->where('is_active', true)->pluck('id');
        $classroomIds = $classroomIds->merge($homeroomClassrooms);

        // Hanya tampilkan kelas yang terkait dengan guru (tanpa fallback ke semua kelas)
        $classrooms = \App\Models\Classroom::whereIn('id', $classroomIds->unique())
            ->where('is_active', true)
            ->orderBy('class_name')
            ->get();

        $assignedClassroomIds = $course->lmsClasses->pluck('classroom_id')->toArray();

        return view('guru.lms.edit', compact('teacher', 'course', 'subjects', 'classrooms', 'semesters', 'assignedClassroomIds'));
    }

    /**
     * Update course
     */
    public function update(UpdateLmsCourseRequest $request, LmsCourse $course)
    {
        $teacher = $this->getTeacher();
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }

        $validated = $request->validated();

        $course->update([
            'course_name' => $request->name,
            'description' => $request->description,
            'status' => $request->status,
            'is_published' => $request->status === 'active',
            'is_sequential' => $request->has('is_sequential'),
            'code' => $request->code,
        ]);

        // Sync classrooms assignment
        $classroomIds = $request->input('classroom_ids', []);
        $currentClasses = $course->lmsClasses;
        $currentClassroomIds = $currentClasses->pluck('classroom_id')->toArray();

        $toAdd = array_diff($classroomIds, $currentClassroomIds);
        $toRemove = array_diff($currentClassroomIds, $classroomIds);

        // Add new classrooms
        foreach ($toAdd as $classroomId) {
            $lmsClass = LmsClass::create([
                'course_id' => $course->id,
                'classroom_id' => $classroomId,
                'school_id' => $this->getEffectiveSchoolId($teacher),
                'status' => 'active',
            ]);

            // Auto-enroll active students
            $classroom = \App\Models\Classroom::find($classroomId);
            if ($classroom) {
                $activeYear = AcademicYear::where('is_active', true)->first();
                $students = $classroom->students();
                if ($activeYear) {
                    $students = $students->wherePivot('academic_year_id', $activeYear->id);
                }
                $students = $students->wherePivot('status', 'aktif')->get();

                foreach ($students as $student) {
                    LmsEnrollment::firstOrCreate([
                        'lms_class_id' => $lmsClass->id,
                        'student_id' => $student->id,
                    ], [
                        'status' => 'enrolled',
                        'enrolled_at' => now(),
                    ]);
                }
            }
        }

        // Remove unselected classrooms
        if (!empty($toRemove)) {
            $classesToRemove = $currentClasses->whereIn('classroom_id', $toRemove);
            foreach ($classesToRemove as $classToRemove) {
                LmsEnrollment::where('lms_class_id', $classToRemove->id)->delete();
                $classToRemove->delete();
            }
        }

        app(\App\Services\LmsEnrollmentService::class)->syncCourseEnrollments($course);

        return redirect()->route('guru.lms.show', $course->id)
            ->with('success', 'Course berhasil diperbarui.');
    }

    /**
     * Delete course
     */
    public function destroy(LmsCourse $course)
    {
        $teacher = $this->getTeacher();
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403, 'Anda tidak memiliki akses untuk menghapus course ini.');
        }

        // Clean up linked LMS classes & enrollments safely
        $lmsClassIds = $course->lmsClasses()->pluck('id');
        if ($lmsClassIds->isNotEmpty()) {
            LmsEnrollment::whereIn('lms_class_id', $lmsClassIds)->delete();
            $course->lmsClasses()->delete();
        }

        $course->delete();

        return redirect()->route('guru.lms.index')
            ->with('success', 'Course berhasil dihapus.');
    }

    // ================================================================
    // MODULE MANAGEMENT
    // ================================================================

    public function createModule(LmsCourse $course)
    {
        $teacher = $this->getTeacher();
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }
        $teacher->load('school');

        return view('guru.lms.modules.create', compact('teacher', 'course'));
    }

    public function editModule(LmsModule $module)
    {
        $course = $module->course;
        $teacher = $this->getTeacher();
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }
        $teacher->load('school');

        return view('guru.lms.modules.edit', compact('teacher', 'course', 'module'));
    }

    public function storeModule(StoreLmsModuleRequest $request, LmsCourse $course)
    {
        $teacher = $this->getTeacher();
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }

        $validated = $request->validated();

        $maxSequence = $course->modules()->max('sequence') ?? 0;

        $module = $course->modules()->create([
            'title' => $request->title,
            'description' => $request->description,
            'color' => $request->color,
            'sequence' => $maxSequence + 1,
            'is_active' => true,
        ]);

        // Reputation Hook for Teacher (+30 Points per Modul)
        \App\Models\ReputationLog::log(
            Auth::id(),
            30,
            'lms_content',
            "Membuat modul LMS baru: " . ($module->title ?? 'Modul'),
            $module
        );

        return redirect()->route('guru.lms.show', $course->id)
            ->with('success', 'Modul berhasil ditambahkan.');
    }

    public function updateModule(StoreLmsModuleRequest $request, LmsModule $module)
    {
        $course = $module->course;
        $teacher = $this->getTeacher();
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }

        $validated = $request->validated();

        $module->update([
            'title' => $request->title,
            'description' => $request->description,
            'color' => $request->color,
        ]);

        return redirect()->route('guru.lms.show', $course->id)
            ->with('success', 'Modul berhasil diperbarui.');
    }

    public function destroyModule(LmsModule $module)
    {
        $course = $module->course;
        $teacher = $this->getTeacher();
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }

        $module->delete();

        return redirect()->route('guru.lms.show', $course->id)
            ->with('success', 'Modul berhasil dihapus.');
    }

    // ================================================================
    // MATERIAL MANAGEMENT (Materials linked directly to course)
    // ================================================================

    public function generateAiMaterial(Request $request)
    {
        $request->validate([
            'topic' => 'required|string|max:255',
            'subject' => 'nullable|string|max:255',
            'level' => 'nullable|string|max:100',
        ]);

        $topic = $request->topic;
        $subject = $request->subject ?? 'Mata Pelajaran';
        $level = $request->level ?? 'SMK/SMA';

        $prompt = "Anda adalah seorang pakar pendidik dan pembuat kurikulum pembelajaran. Buatkan artikel materi pembelajaran yang lengkap, terstruktur, rapi, menarik, dan mudah dipahami oleh siswa tingkat {$level} untuk mata pelajaran '{$subject}' dengan topik '{$topic}'.

Format keluaran HARUS berupa HTML rapi (gunakan tag <h2>, <h3>, <p>, <ul>, <li>, <strong>, <em>, blockquote). JANGAN gunakan tag <html> atau <body>.

Struktur materi yang harus dibuat:
1. <h2>📌 Pendahuluan & Tujuan Pembelajaran</h2>
<p>Jelaskan konsep dasar secara singkat & motivasi belajar.</p>

2. <h2>💡 Pembahasan Utama & Konsep Kunci</h2>
<p>Gunakan sub-judul <h3>, poin-poin <ul>/<li>, dan cetak tebal <strong> untuk konsep penting.</p>

3. <h2>🧪 Contoh Penerapan / Studi Kasus Praktis</h2>
<p>Berikan contoh nyata dalam kehidupan sehari-hari atau dunia kerja / industri.</p>

4. <h2>⚡ Ringkasan Poin Penting (Key Takeaways)</h2>
<blockquote>Rangkum 3-4 poin kunci yang wajib diingat siswa.</blockquote>

5. <h2>🔍 Pertanyaan Refleksi Siswa</h2>
<ol>
  <li>Pertanyaan pemantik 1...</li>
  <li>Pertanyaan pemantik 2...</li>
</ol>

Buat dengan bahasa Indonesia yang ramah, jelas, dan edukatif.";

        try {
            $gemini = app(\App\Services\GeminiService::class);
            $htmlContent = $gemini->generateText($prompt);

            // Clean markdown code block wrapper if present
            $htmlContent = trim($htmlContent);
            if (str_starts_with($htmlContent, '```html')) {
                $htmlContent = substr($htmlContent, 7);
            }
            if (str_starts_with($htmlContent, '```')) {
                $htmlContent = substr($htmlContent, 3);
            }
            if (str_ends_with($htmlContent, '```')) {
                $htmlContent = substr($htmlContent, 0, -3);
            }
            $htmlContent = trim($htmlContent);

            return response()->json([
                'success' => true,
                'content' => $htmlContent
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghasilkan materi via AI: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storeMaterial(StoreLmsMaterialRequest $request, LmsCourse $course)
    {
        $teacher = $this->getTeacher();
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }

        $validated = $request->validated();

        $filePath = null;
        $fileSize = null;

        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('lms/materials', 'public');
            $fileSize = $request->file('file')->getSize();
        }

        $maxOrder = $course->materials()->max('order_number') ?? 0;

        $material = $course->materials()->create([
            'module_id' => $request->module_id,
            'title' => $request->title,
            'material_type' => $request->material_type,
            'content' => $request->content,
            'file_url' => $request->file_url,
            'file_path' => $filePath,
            'file_size' => $fileSize,
            'order_number' => $maxOrder + 1,
            'is_published' => true,
        ]);

        // Reputation Hook for Teacher
        \App\Models\ReputationLog::log(
            Auth::id(), 
            30, 
            'lms_content', 
            "Membuat materi LMS baru: " . ($request->title ?? 'Materi'),
            $material
        );

        // Send WhatsApp notification to enrolled students
        try {
            $notificationService = app(\App\Services\NotificationService::class);
            $notificationService->sendLmsNotification($course, 'lms.material.published', [
                'title' => $material->title,
            ]);
        } catch (\Exception $e) {
            \Log::error('LMS material notification failed: ' . $e->getMessage());
        }

        return redirect()->route('guru.lms.show', $course->id)
            ->with('success', 'Materi berhasil ditambahkan.');
    }

    public function destroyMaterial(LmsMaterial $material)
    {
        $course = $material->course;
        $teacher = $this->getTeacher();
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }

        if ($material->file_path) {
            Storage::disk('public')->delete($material->file_path);
        }

        $material->delete();

        return redirect()->route('guru.lms.show', $course->id)
            ->with('success', 'Materi berhasil dihapus.');
    }

    /**
     * Teacher Learning Analytics Dashboard
     */
    public function analytics(LmsCourse $course)
    {
        $teacher = $this->getTeacher();
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }

        $course->load([
            'subject', 'lmsClasses.classroom',
            'modules' => fn($q) => $q->where('is_active', true)->withCount('materials'),
            'materials' => fn($q) => $q->where('is_published', true),
            'assignments' => fn($q) => $q->where('is_published', true)->withCount('submissions'),
            'quizzes' => fn($q) => $q->where('is_published', true)->withCount('attempts'),
        ]);

        // Get enrolled students
        $enrollments = LmsEnrollment::whereIn('lms_class_id', $course->lmsClasses->pluck('id'))
            ->with(['student.user'])
            ->get();

        $students = $enrollments->pluck('student')->filter();

        // Calculate progress & stats per student
        $studentStats = [];
        $atRiskStudents = [];

        foreach ($students as $student) {
            $progress = LmsMaterialProgress::getProgressForCourse($course->id, $student->id);
            $completedMaterials = LmsMaterialProgress::where('student_id', $student->id)
                ->whereIn('material_id', $course->materials->pluck('id'))
                ->where('status', 'completed')
                ->count();

            $submissionsCount = LmsSubmission::where('student_id', $student->id)
                ->whereIn('assignment_id', $course->assignments->pluck('id'))
                ->count();

            $stat = [
                'student' => $student,
                'progress' => $progress,
                'completed_materials' => $completedMaterials,
                'submissions_count' => $submissionsCount,
            ];

            $studentStats[] = $stat;

            if ($progress < 40 || ($course->assignments->count() > 0 && $submissionsCount === 0)) {
                $atRiskStudents[] = $stat;
            }
        }

        $avgCourseProgress = count($studentStats) > 0 ? round(collect($studentStats)->avg('progress')) : 0;

        return view('guru.lms.analytics', compact(
            'teacher', 'course', 'students', 'studentStats', 'atRiskStudents', 'avgCourseProgress'
        ));
    }

    /**
     * Export LMS Gradebook to CSV/Excel
     */
    public function exportGradebook(LmsCourse $course)
    {
        $teacher = $this->getTeacher();
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }

        $course->load([
            'subject', 'lmsClasses.classroom',
            'assignments' => fn($q) => $q->where('is_published', true),
            'quizzes' => fn($q) => $q->where('is_published', true),
        ]);

        $enrollments = LmsEnrollment::whereIn('lms_class_id', $course->lmsClasses->pluck('id'))
            ->with(['student.user'])
            ->get();

        $students = $enrollments->pluck('student')->filter();

        $filename = 'Rekap_Nilai_LMS_' . Str::slug($course->course_name ?? $course->name) . '_' . date('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($students, $course) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");

            $headerRow = ['No', 'NISN', 'Nama Siswa'];
            foreach ($course->assignments as $asgn) {
                $headerRow[] = 'Tugas: ' . $asgn->title;
            }
            foreach ($course->quizzes as $qz) {
                $headerRow[] = 'Kuis: ' . $qz->title;
            }
            $headerRow[] = 'Progres (%)';

            fputcsv($file, $headerRow);

            $no = 1;
            foreach ($students as $student) {
                $row = [
                    $no++,
                    $student->nisn ?? '-',
                    $student->user->name ?? '-',
                ];

                foreach ($course->assignments as $asgn) {
                    $sub = LmsSubmission::where('assignment_id', $asgn->id)
                        ->where('student_id', $student->id)
                        ->first();
                    $row[] = $sub ? ($sub->score !== null ? $sub->score : 'Dikumpul') : '-';
                }

                foreach ($course->quizzes as $qz) {
                    $att = LmsQuizAttempt::where('quiz_id', $qz->id)
                        ->where('student_id', $student->id)
                        ->orderByDesc('score')
                        ->first();
                    $row[] = $att ? ($att->score !== null ? $att->score : '-') : '-';
                }

                $row[] = LmsMaterialProgress::getProgressForCourse($course->id, $student->id) . '%';

                fputcsv($file, $row);
            }

            fclose($file);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Mark a discussion reply as Best Answer by Teacher
     */
    public function markBestReply(Request $request, LmsDiscussionReply $reply)
    {
        $teacher = $this->getTeacher();
        $course = $reply->discussion->course;
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }

        // Unmark previous best replies in this discussion
        LmsDiscussionReply::where('discussion_id', $reply->discussion_id)
            ->update(['is_best_answer' => false]);

        $reply->update([
            'is_best_answer' => true,
            'is_teacher_verified' => true,
        ]);

        $reply->discussion->update(['is_resolved' => true]);

        // Award reputation EXP points to student (+15 EXP)
        if ($reply->user_id) {
            \App\Models\ReputationLog::log(
                $reply->user_id,
                15,
                'lms_best_answer',
                "Jawaban terbaik diverifikasi guru pada diskusi: " . Str::limit($reply->discussion->title, 30),
                $reply
            );
        }

        return redirect()->back()->with('success', 'Jawaban berhasil ditandai sebagai Jawaban Terbaik & Poin EXP diberikan ke siswa!');
    }

    /**
     * Update material (title, description, file replacement)
     */
    public function updateMaterial(Request $request, LmsMaterial $material)
    {
        $course = $material->course;
        $teacher = $this->getTeacher();
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }

        $request->validate([
            'module_id' => 'required|exists:lms_modules,id',
            'title' => 'required|string|max:200',
            'material_type' => 'nullable|string',
            'file_url' => 'nullable|string|max:1000',
            'content' => 'nullable|string',
            'file' => 'nullable|file|max:10240',
        ], [
            'title.required' => 'Judul materi wajib diisi.',
            'file.max' => 'Ukuran file maksimal 10MB.',
        ]);

        $data = [
            'module_id' => $request->module_id,
            'title' => $request->title,
            'content' => $request->content,
        ];
        if ($request->filled('material_type')) {
            $data['material_type'] = $request->material_type;
        }
        if ($request->has('file_url')) {
            $data['file_url'] = $request->file_url;
        }

        if ($request->hasFile('file')) {
            if ($material->file_path) {
                Storage::disk('public')->delete($material->file_path);
            }
            $data['file_path'] = $request->file('file')->store('lms/materials', 'public');
            $data['file_name'] = $request->file('file')->getClientOriginalName();
            $data['file_type'] = $request->file('file')->getClientMimeType();
            $data['file_size'] = $request->file('file')->getSize();
        }

        $material->update($data);

        return redirect()->route('guru.lms.show', $course->id)
            ->with('success', 'Materi berhasil diperbarui.');
    }

    public function downloadMaterial(LmsMaterial $material)
    {
        $course = $material->course;
        $teacher = $this->getTeacher();

        // Allow download if teacher belongs to the same school as the course
        if (!$teacher || !$course || !Auth::user()->canAccessSchool($course->school_id)) {
            abort(403, 'Anda tidak memiliki akses untuk mengunduh file ini.');
        }

        if (!$material->file_path || !Storage::disk('public')->exists($material->file_path)) {
            abort(404, 'File tidak ditemukan.');
        }

        return Storage::disk("public")->download($material->file_path, str_replace(["/", "\\"], "-", $material->title));
    }

    public function viewMaterial(LmsMaterial $material)
    {
        $course = $material->course;
        $teacher = $this->getTeacher();

        // Allow view if teacher belongs to the same school as the course
        if (!$teacher || !$course || !Auth::user()->canAccessSchool($course->school_id)) {
            abort(403, 'Anda tidak memiliki akses untuk melihat file ini.');
        }

        if (!$material->file_path || !Storage::disk('public')->exists($material->file_path)) {
            abort(404, 'File tidak ditemukan.');
        }

        $path = Storage::disk('public')->path($material->file_path);
        $mimeType = \Illuminate\Support\Facades\File::mimeType($path);

        return response()->file($path, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline'
        ]);
    }

    // ──────────────────────────────────────────────
    //  ENROLLMENT MANAGEMENT
    // ──────────────────────────────────────────────

    /**
     * View enrolled students for a course
     */
    public function enrolledStudents(LmsCourse $course)
    {
        $teacher = $this->getTeacher();
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }
        $teacher->load('school');

        $course->load(['lmsClasses.classroom']);

        // Get all enrollments across all classes for this course
        $enrollments = LmsEnrollment::whereIn('lms_class_id', $course->lmsClasses->pluck('id'))
            ->with(['student.user', 'lmsClass.classroom'])
            ->orderBy('enrolled_at', 'desc')
            ->get();

        // Get classrooms available for enrollment (same school, not yet linked)
        $linkedClassroomIds = $course->lmsClasses->pluck('classroom_id')->toArray();
        $availableClassrooms = \App\Models\Classroom::where('school_id', $this->getEffectiveSchoolId($teacher))
            ->whereNotIn('id', $linkedClassroomIds)
            ->orderBy('class_name')
            ->get();

        return view('guru.lms.enrolled-students', compact('teacher', 'course', 'enrollments', 'availableClassrooms'));
    }

    /**
     * Enroll students from a classroom into the course
     */
    public function enrollStudents(Request $request, LmsCourse $course)
    {
        $teacher = $this->getTeacher();
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }

        $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
        ]);

        $classroomId = $request->classroom_id;

        // Create LmsClass for this classroom if not exists
        $lmsClass = LmsClass::firstOrCreate([
            'course_id' => $course->id,
            'classroom_id' => $classroomId,
        ], [
            'school_id' => $this->getEffectiveSchoolId($teacher),
            'status' => 'active',
        ]);

        // Get active students in the classroom
        $classroom = \App\Models\Classroom::findOrFail($classroomId);
        $activeYear = AcademicYear::where('is_active', true)->first();

        $studentsQuery = $classroom->students();
        if ($activeYear) {
            $studentsQuery = $studentsQuery->wherePivot('academic_year_id', $activeYear->id);
        }
        $students = $studentsQuery->wherePivot('status', 'aktif')->get();

        $enrolled = 0;
        foreach ($students as $student) {
            $created = LmsEnrollment::firstOrCreate([
                'lms_class_id' => $lmsClass->id,
                'student_id' => $student->id,
            ], [
                'status' => 'enrolled',
                'enrolled_at' => now(),
            ]);

            if ($created->wasRecentlyCreated) {
                $enrolled++;
            }
        }

        return redirect()->route('guru.lms.students.index', $course->id)
            ->with('success', "Berhasil mendaftarkan {$enrolled} siswa dari kelas {$classroom->class_name}.");
    }

    /**
     * Unenroll a student from a course
     */
    public function unenrollStudent(LmsCourse $course, \App\Models\Student $student)
    {
        $teacher = $this->getTeacher();
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }

        $lmsClassIds = $course->lmsClasses->pluck('id');

        $deleted = LmsEnrollment::whereIn('lms_class_id', $lmsClassIds)
            ->where('student_id', $student->id)
            ->delete();

        if ($deleted) {
            return redirect()->back()->with('success', "Siswa {$student->full_name} berhasil dikeluarkan dari course.");
        }

        return redirect()->back()->with('error', 'Siswa tidak ditemukan di course ini.');
    }

    /**
     * Start a video conference meeting for this course
     */
    public function startMeeting(Request $request, LmsCourse $course)
    {
        $teacher = $this->getTeacher();
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }

        $course->update([
            'meeting_active'    => true,
            'meeting_started_at' => now(),
        ]);

        // Buat record sesi meeting
        $session = LmsMeetingSession::create([
            'course_id'  => $course->id,
            'started_by' => Auth::id(),
            'started_at' => now(),
        ]);

        // Simpan session_id di cache agar bisa diakses saat siswa join
        cache()->put('lms_meeting_session_' . $course->id, $session->id, now()->addHours(8));

        // Buat notifikasi in-app untuk semua siswa yang enrolled
        try {
            $enrolledStudentIds = LmsEnrollment::whereHas('lmsClass', fn($q) => $q->where('course_id', $course->id))
                ->whereIn('status', ['enrolled', 'in_progress'])
                ->with('student.user')
                ->get()
                ->pluck('student');

            foreach ($enrolledStudentIds as $student) {
                if ($student && $student->user_id) {
                    Notification::create([
                        'user_id'       => $student->user_id,
                        'title'         => '🔴 Kelas Live Dimulai!',
                        'message'       => "Guru telah memulai kelas live untuk course **{$course->name}**. Bergabunglah sekarang!",
                        'type'          => 'info',
                        'related_model' => 'LmsCourse',
                        'related_id'    => $course->id,
                        'is_read'       => false,
                    ]);
                }
            }
        } catch (\Exception $e) {
            \Log::error('LMS meeting in-app notification failed: ' . $e->getMessage());
        }

        // Kirim WhatsApp notification (sudah ada sebelumnya)
        try {
            $notificationService = app(\App\Services\NotificationService::class);
            $notificationService->sendLmsNotification($course, 'lms.meeting.started');
        } catch (\Exception $e) {
            \Log::error('LMS meeting WhatsApp notification failed: ' . $e->getMessage());
        }

        return redirect()->route('guru.lms.meeting.join', $course->id)
            ->with('success', 'Kelas tatap muka virtual berhasil dimulai.');
    }

    /**
     * Stop the video conference meeting for this course
     */
    public function stopMeeting(LmsCourse $course)
    {
        $teacher = $this->getTeacher();
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }

        // Tutup sesi meeting yang aktif
        $sessionId = cache()->get('lms_meeting_session_' . $course->id);
        if ($sessionId) {
            $session = LmsMeetingSession::find($sessionId);
            if ($session && $session->isActive()) {
                // Finalize semua attendance yang masih aktif
                LmsMeetingAttendance::where('session_id', $sessionId)
                    ->whereNull('left_at')
                    ->each(function ($att) {
                        $att->recordLeave();
                    });

                // Update jumlah total peserta
                $totalAttendees = LmsMeetingAttendance::where('session_id', $sessionId)->count();
                $session->update([
                    'ended_at'        => now(),
                    'total_attendees' => $totalAttendees,
                ]);
            }
            cache()->forget('lms_meeting_session_' . $course->id);
        }

        $course->update([
            'meeting_active'     => false,
            'meeting_started_at' => null,
        ]);

        return redirect()->route('guru.lms.show', $course->id)
            ->with('success', 'Kelas tatap muka virtual berhasil diakhiri.');
    }

    /**
     * Join the video conference meeting
     */
    public function joinMeeting(LmsCourse $course)
    {
        $teacher = $this->getTeacher();
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }

        $roomName    = 'PembdaHub_Course_' . $course->id . '_' . md5($course->code . config('app.key'));
        $displayName = ($teacher->user->name ?? 'Guru') . ' (Guru)';

        // Ambil sesi aktif untuk ditampilkan di panel peserta
        $activeSessionId = cache()->get('lms_meeting_session_' . $course->id);
        $activeSession   = $activeSessionId ? LmsMeetingSession::with('attendances.student.user')->find($activeSessionId) : null;

        return view('guru.lms.meeting', compact('teacher', 'course', 'roomName', 'displayName', 'activeSession'));
    }

    /**
     * AJAX: Daftar siswa yang sedang hadir di meeting (polling)
     */
    public function meetingAttendees(LmsCourse $course)
    {
        $teacher = $this->getTeacher();
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $sessionId = cache()->get('lms_meeting_session_' . $course->id);
        if (!$sessionId) {
            return response()->json(['attendees' => [], 'total' => 0]);
        }

        $attendees = LmsMeetingAttendance::where('session_id', $sessionId)
            ->whereNull('left_at')
            ->with('student.user')
            ->get()
            ->map(fn($a) => [
                'name'      => $a->student->full_name ?? $a->student->user->name ?? 'Siswa',
                'joined_at' => $a->joined_at->diffForHumans(),
                'initials'  => strtoupper(substr($a->student->full_name ?? 'S', 0, 1)),
            ]);

        return response()->json(['attendees' => $attendees, 'total' => $attendees->count()]);
    }

    /**
     * Laporan kehadiran meeting per course
     */
    public function attendanceReport(LmsCourse $course)
    {
        $teacher = $this->getTeacher();
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }
        $teacher->load('school');

        $sessions = LmsMeetingSession::where('course_id', $course->id)
            ->with(['attendances.student.user', 'startedBy'])
            ->orderByDesc('started_at')
            ->paginate(10);

        return view('guru.lms.attendance-report', compact('teacher', 'course', 'sessions'));
    }

    /**
     * Upload gambar dari WYSIWYG Rich Text Editor (Quill.js) materi LMS
     */
    public function uploadEditorImage(Request $request)
    {
        $teacher = $this->getTeacher();
        if (!$teacher) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp,svg|max:5120',
        ], [
            'image.required' => 'File gambar wajib diunggah.',
            'image.image'    => 'File harus berupa gambar.',
            'image.max'      => 'Ukuran gambar maksimal 5 MB.',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('lms/editor', 'public');
            $url = asset('storage/' . $path);

            return response()->json([
                'success' => true,
                'url'     => $url,
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Gagal mengunggah gambar.'], 400);
    }
}

