<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Extracurricular;
use App\Models\ExtracurricularActivity;
use App\Models\ExtracurricularMember;
use App\Models\School;
use App\Models\Student;
use App\Models\Teacher;
use App\Services\ExtracurricularService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExtracurricularController extends Controller
{
    protected ExtracurricularService $ekskulService;

    public function __construct(ExtracurricularService $ekskulService)
    {
        $this->ekskulService = $ekskulService;
    }

    private function isGlobalAdmin(): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        return $user->canAccessAllSchools()
            || $user->isOwnerOrSuperAdmin()
            || in_array($user->role, ['superadmin', 'admin_yayasan', 'yayasan', 'ketua_yayasan', 'pengurus_yayasan'])
            || empty($user->school_id);
    }

    private function getUserSchoolId(): ?int
    {
        $user = Auth::user();
        if (!$user) return null;
        if ($user->school_id) return (int)$user->school_id;
        if ($user->teacher && $user->teacher->school_id) return (int)$user->teacher->school_id;
        return null;
    }

    private function checkSchoolAccess(?int $schoolId): void
    {
        $user = Auth::user();
        if (!$user) {
            abort(403, 'Akses Ditolak: Anda belum login.');
        }

        if ($this->isGlobalAdmin()) {
            return;
        }

        // If unit is foundation-level (empty school_id or Yayasan school_id = 4), allow access
        if (empty($schoolId) || (int)$schoolId === 4) {
            return;
        }

        // If the current logged-in teacher is the assigned advisor of this extracurricular, allow access
        if ($user->teacher) {
            $tIds = method_exists($user, 'teacherIds') ? $user->teacherIds() : [$user->teacher->id];
            if (!empty($tIds) && Extracurricular::where('school_id', $schoolId)->whereIn('advisor_teacher_id', $tIds)->exists()) {
                return;
            }
        }

        $userSchoolId = $this->getUserSchoolId();
        if ($userSchoolId && (int)$userSchoolId !== (int)$schoolId) {
            abort(403, 'Akses Ditolak: Kewenangan PKS dan Guru terbatas hanya pada unit sekolah Anda sendiri.');
        }
    }

    /**
     * Display list of Extracurricular units.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isGlobal = $this->isGlobalAdmin();

        $query = Extracurricular::with(['school', 'advisor', 'leader', 'secretary', 'treasurer', 'activeMembers', 'lmsCourse'])
            ->withCount(['members', 'activeMembers', 'activities']);

        // Strict school filtering: PKS / Guru / Admin Sekolah can see their school + Yayasan units
        if (!$isGlobal) {
            $schoolId = $user->school_id;
            $query->where(function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId)
                  ->orWhere('scope', 'yayasan')
                  ->orWhereNull('school_id');
            });
        } else {
            $schoolId = $request->get('school_id');
            if ($schoolId) {
                $query->where('school_id', $schoolId);
            }
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhere('advisor_name', 'LIKE', "%{$search}%");
            });
        }

        $extracurriculars = $query->orderByRaw("CASE WHEN scope = 'yayasan' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        // Schools list: Global admins get all active schools, PKS only gets their own school
        if ($isGlobal) {
            $schools = School::schoolsOnly()->get();
            if ($schools->isEmpty()) {
                $schools = School::where('type', '!=', 'yayasan')->get();
            }
        } else {
            $schools = School::where('id', $user->school_id)->get();
        }

        $statsQuery = Extracurricular::where('is_active', true);
        $membersQuery = ExtracurricularMember::where('status', 'approved');
        $activitiesQuery = ExtracurricularActivity::query();

        if (!$isGlobal && $user->school_id) {
            $statsQuery->where(function ($q) use ($user) {
                $q->where('school_id', $user->school_id)->orWhere('scope', 'yayasan')->orWhereNull('school_id');
            });
        }

        $stats = [
            'total_units' => $statsQuery->count(),
            'total_members' => $membersQuery->count(),
            'total_activities' => $activitiesQuery->count(),
        ];

        return view('admin.extracurricular.index', compact('extracurriculars', 'schools', 'stats', 'schoolId', 'isGlobal'));
    }

    /**
     * Store newly created Extracurricular unit.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $isGlobal = $this->isGlobalAdmin();

        $validated = $request->validate([
            'school_id' => $isGlobal ? 'nullable|exists:schools,id' : 'nullable',
            'scope' => 'nullable|string',
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',
            'schedule_day_time' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'manager_name' => 'nullable|string|max:255',
            'advisor_teacher_id' => 'nullable|exists:teachers,id',
            'advisor_name' => 'nullable|string|max:255',
            'leader_student_id' => 'nullable|exists:students,id',
            'secretary_student_id' => 'nullable|exists:students,id',
            'treasurer_student_id' => 'nullable|exists:students,id',
            'is_active' => 'boolean',
        ]);

        // Force school_id for non-global admins
        if (!$isGlobal) {
            $validated['school_id'] = $user->school_id;
            $validated['scope'] = 'sekolah';
        }

        if (empty($validated['advisor_name']) && !empty($validated['advisor_teacher_id'])) {
            $teacher = Teacher::find($validated['advisor_teacher_id']);
            $validated['advisor_name'] = $teacher?->full_name;
        }

        $ekskul = $this->ekskulService->createExtracurricular($validated, Auth::id());

        $showRoute = request()->routeIs('guru.*') ? 'guru.extracurricular.show' : 'admin.extracurricular.show';
        return redirect()->route($showRoute, $ekskul)
            ->with('success', 'Unit Ekstrakurikuler berhasil dibuat dan kanal Pembda Space telah aktif!');
    }

    /**
     * Display detailed unit page with members and activities.
     */
    public function show(Extracurricular $extracurricular)
    {
        $this->checkSchoolAccess($extracurricular->school_id);

        // Auto-sync nilai extra dari LMS jika kelas LMS sudah aktif
        if ($extracurricular->lms_course_id) {
            $this->ekskulService->syncAllMembersExtraGrades($extracurricular);
        }

        $extracurricular->load([
            'school',
            'advisor',
            'leader',
            'secretary',
            'treasurer',
            'forumGroup',
            'lmsCourse.modules',
            'lmsCourse.assignments',
            'lmsCourse.quizzes',
            'members.student.currentClassroom',
            'members.student.school',
            'activities.creator'
        ]);

        if ($extracurricular->isFoundationLevel()) {
            $students = Student::with('school')->where('status', 'aktif')->orderBy('full_name')->get();
            $teachers = Teacher::with('school')->where('is_active', true)->orderBy('full_name')->get();
        } else {
            $students = Student::with('school')->where('school_id', $extracurricular->school_id)
                ->where('status', 'aktif')
                ->orderBy('full_name')
                ->get();

            $teachers = Teacher::with('school')->where('school_id', $extracurricular->school_id)
                ->where('is_active', true)
                ->orderBy('full_name')
                ->get();
        }

        return view('admin.extracurricular.show', compact('extracurricular', 'students', 'teachers'));
    }

    /**
     * Update Extracurricular unit.
     */
    public function update(Request $request, Extracurricular $extracurricular)
    {
        $this->checkSchoolAccess($extracurricular->school_id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',
            'schedule_day_time' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'manager_name' => 'nullable|string|max:255',
            'advisor_teacher_id' => 'nullable|exists:teachers,id',
            'advisor_name' => 'nullable|string|max:255',
            'leader_student_id' => 'nullable|exists:students,id',
            'secretary_student_id' => 'nullable|exists:students,id',
            'treasurer_student_id' => 'nullable|exists:students,id',
            'is_active' => 'boolean',
        ]);

        if (empty($validated['advisor_name']) && !empty($validated['advisor_teacher_id'])) {
            $teacher = Teacher::find($validated['advisor_teacher_id']);
            $validated['advisor_name'] = $teacher?->full_name;
        }

        $this->ekskulService->updateExtracurricular($extracurricular, $validated, Auth::id());

        return back()->with('success', 'Informasi Ekstrakurikuler berhasil diperbarui.');
    }

    /**
     * Assign or update leadership structure.
     */
    public function assignLeadership(Request $request, Extracurricular $extracurricular)
    {
        $this->checkSchoolAccess($extracurricular->school_id);

        $validated = $request->validate([
            'manager_name' => 'nullable|string|max:255',
            'advisor_teacher_id' => 'nullable|exists:teachers,id',
            'advisor_name' => 'nullable|string|max:255',
            'leader_student_id' => 'nullable|exists:students,id',
            'secretary_student_id' => 'nullable|exists:students,id',
            'treasurer_student_id' => 'nullable|exists:students,id',
        ]);

        if (empty($validated['advisor_name']) && !empty($validated['advisor_teacher_id'])) {
            $teacher = Teacher::find($validated['advisor_teacher_id']);
            $validated['advisor_name'] = $teacher?->full_name;
        }

        $this->ekskulService->updateExtracurricular($extracurricular, $validated, Auth::id());

        return back()->with('success', 'Struktur Pengurus Ekstrakurikuler berhasil ditetapkan!');
    }

    /**
     * Approve pending member claim.
     */
    public function approveMember(Request $request, ExtracurricularMember $member)
    {
        $this->checkSchoolAccess($member->extracurricular->school_id);

        $this->ekskulService->approveMember($member, Auth::id());

        return back()->with('success', "Keanggotaan {$member->student->full_name} telah disetujui (+15 Poin Reputasi).");
    }

    /**
     * Add student manually to extracurricular.
     */
    public function addMember(Request $request, Extracurricular $extracurricular)
    {
        $this->checkSchoolAccess($extracurricular->school_id);

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'role' => 'required|string',
            'section' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $student = Student::findOrFail($validated['student_id']);
        if (!$extracurricular->isFoundationLevel() && (int)$student->school_id !== (int)$extracurricular->school_id) {
            return back()->with('error', 'Siswa harus berasal dari unit sekolah yang sama dengan unit ekstrakurikuler.');
        }

        $this->ekskulService->claimMembership($student, $extracurricular, $validated['role'], $validated['notes'] ?? null, $validated['section'] ?? null);

        return back()->with('success', "Siswa {$student->full_name} berhasil ditambahkan sebagai {$validated['role']}.");
    }

    /**
     * Remove or reject member.
     */
    public function removeMember(ExtracurricularMember $member)
    {
        $this->checkSchoolAccess($member->extracurricular->school_id);

        $name = $member->student?->full_name ?? 'Siswa';
        $member->delete();

        return back()->with('success', "Anggota {$name} telah dihapus dari unit ekstrakurikuler.");
    }

    /**
     * Log new activity or exercise session.
     */
    public function addActivity(Request $request, Extracurricular $extracurricular)
    {
        $this->checkSchoolAccess($extracurricular->school_id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'activity_date' => 'required|date',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'broadcast_to_space' => 'nullable|boolean',
        ]);

        $validated['extracurricular_id'] = $extracurricular->id;
        $validated['created_by'] = Auth::id();

        $activity = ExtracurricularActivity::create($validated);

        if ($request->boolean('broadcast_to_space')) {
            $service = app(\App\Services\ExtracurricularService::class);
            $service->broadcastActivityToSpace($activity, Auth::user());
        }

        return back()->with('success', 'Catatan aktivitas latihan berhasil disimpan' . ($request->boolean('broadcast_to_space') ? ' dan disiarkan ke Pembda Space.' : '.'));
    }

    /**
     * Delete Extracurricular unit.
     */
    public function destroy(Extracurricular $extracurricular)
    {
        $this->checkSchoolAccess($extracurricular->school_id);

        $name = $extracurricular->name;

        // Delete associated activities & members safely
        $extracurricular->activities()->delete();
        $extracurricular->members()->delete();

        // If connected to forum group, remove forum group cleanly
        if ($extracurricular->forum_group_id) {
            $group = \App\Models\ForumGroup::find($extracurricular->forum_group_id);
            if ($group) {
                $group->delete();
            }
        }

        $extracurricular->delete();

        $redirectRoute = (Auth::user()->role === 'guru' && !in_array(Auth::user()->role, ['superadmin', 'admin_sekolah'])) 
            ? 'guru.extracurricular.index' 
            : 'admin.extracurricular.index';

        return redirect()->route($redirectRoute)->with('success', "Unit Ekstrakurikuler '{$name}' berhasil dihapus.");
    }

    /**
     * Tombol 1-Klik: Aktifkan atau Buka Kelas LMS Ekstrakurikuler (Materi, Tugas, Quiz & Nilai Extra).
     */
    public function openOrActivateLms(Request $request, Extracurricular $extracurricular)
    {
        $this->checkSchoolAccess($extracurricular->school_id);

        $course = $this->ekskulService->ensureLmsCourse($extracurricular, Auth::user());

        // Redirect sesuai peran pengguna (Guru Pembina langsung ke Ruang Kelas LMS Guru, Admin ke Detail LMS / Ekskul)
        $user = Auth::user();
        if (request()->routeIs('guru.*') || ($user && $user->role === 'guru')) {
            return redirect()->route('guru.lms.show', $course->id)
                ->with('success', "Kelas LMS Ekstrakurikuler '{$extracurricular->name}' siap digunakan! Anda dapat mengelola Materi, Tugas, dan Quiz di sini.");
        }

        if ($request->boolean('stay')) {
            return back()->with('success', "Kelas LMS '{$extracurricular->name}' telah diaktifkan dan seluruh anggota telah terdaftar otomatis!");
        }

        // Jika Admin juga memiliki profil Guru atau mengakses dari panel Guru
        if ($user && $user->teacher) {
            return redirect()->route('guru.lms.show', $course->id)
                ->with('success', "Kelas LMS Ekstrakurikuler '{$extracurricular->name}' telah aktif!");
        }

        return back()->with('success', "Kelas LMS Ekstrakurikuler '{$extracurricular->name}' berhasil diaktifkan (Kode: {$course->code}) dan seluruh anggota aktif telah didaftarkan otomatis!");
    }

    /**
     * Sinkronisasi & Hitung Ulang Nilai Extra Otomatis dari Rata-rata Tugas & Quiz LMS.
     */
    public function syncExtraGrades(Extracurricular $extracurricular)
    {
        $this->checkSchoolAccess($extracurricular->school_id);

        if (!$extracurricular->lms_course_id) {
            return back()->with('error', 'Aktifkan Kelas LMS terlebih dahulu sebelum menyinkronkan nilai Tugas & Quiz.');
        }

        $count = $this->ekskulService->syncAllMembersExtraGrades($extracurricular, Auth::id());

        return back()->with('success', "Berhasil menyinkronkan Nilai Extra (Tugas & Quiz LMS) untuk {$count} anggota {$extracurricular->name}!");
    }

    /**
     * Simpan Nilai Extra Manual / Edit Nilai (Angka 0-100 + Predikat + Deskripsi) untuk 1 Anggota.
     */
    public function updateMemberGrade(Request $request, ExtracurricularMember $member)
    {
        $this->checkSchoolAccess($member->extracurricular->school_id);

        $validated = $request->validate([
            'avg_assignment_score' => 'nullable|numeric|min:0|max:100',
            'avg_quiz_score' => 'nullable|numeric|min:0|max:100',
            'attendance_score' => 'nullable|numeric|min:0|max:100',
            'final_score' => 'required|numeric|min:0|max:100',
            'predicate' => 'nullable|string|max:30',
            'grade_description' => 'nullable|string|max:1000',
            'issue_certificate' => 'nullable|boolean',
        ]);

        $finalScore = round((float) $validated['final_score'], 2);
        $predicate = !empty($validated['predicate'])
            ? $validated['predicate']
            : ExtracurricularMember::calculatePredicate($finalScore);

        $description = !empty($validated['grade_description'])
            ? $validated['grade_description']
            : ExtracurricularMember::generateDefaultDescription($member->extracurricular->name, $predicate);

        $updateData = [
            'avg_assignment_score' => $validated['avg_assignment_score'] ?? $member->avg_assignment_score,
            'avg_quiz_score' => $validated['avg_quiz_score'] ?? $member->avg_quiz_score,
            'attendance_score' => $validated['attendance_score'] ?? $member->attendance_score,
            'final_score' => $finalScore,
            'predicate' => $predicate,
            'grade_description' => $description,
            'graded_by' => Auth::id(),
            'graded_at' => now(),
        ];

        if (($request->boolean('issue_certificate') || $finalScore >= 60) && empty($member->certificate_code)) {
            $updateData['certificate_code'] = $this->ekskulService->generateCertificateCode($member->extracurricular, $member);
            $updateData['certificate_issued_at'] = now();
        }

        $member->update($updateData);

        return back()->with('success', "Nilai Extra untuk {$member->student->full_name} berhasil disimpan ({$finalScore} - {$predicate})!");
    }

    /**
     * Simpan Nilai Extra Massal (Bulk Save) dari Tabel Rekap Nilai Ekskul.
     */
    public function bulkUpdateGrades(Request $request, Extracurricular $extracurricular)
    {
        $this->checkSchoolAccess($extracurricular->school_id);

        $gradesInput = $request->input('grades', []);
        $updatedCount = 0;

        foreach ($gradesInput as $memberId => $row) {
            $member = ExtracurricularMember::where('extracurricular_id', $extracurricular->id)
                ->where('id', $memberId)
                ->first();

            if (!$member) {
                continue;
            }

            $rawFinal = $row['final_score'] ?? null;
            $rawAttendance = $row['attendance_score'] ?? null;

            if ($rawFinal === null || $rawFinal === '') {
                continue;
            }

            $finalScore = min(100, max(0, round((float) $rawFinal, 2)));
            $attendanceScore = ($rawAttendance !== null && $rawAttendance !== '')
                ? min(100, max(0, round((float) $rawAttendance, 2)))
                : $member->attendance_score;

            $predicate = !empty($row['predicate'])
                ? $row['predicate']
                : ExtracurricularMember::calculatePredicate($finalScore);

            $description = !empty($row['grade_description'])
                ? $row['grade_description']
                : ExtracurricularMember::generateDefaultDescription($extracurricular->name, $predicate);

            $updateData = [
                'attendance_score' => $attendanceScore,
                'final_score' => $finalScore,
                'predicate' => $predicate,
                'grade_description' => $description,
                'graded_by' => Auth::id(),
                'graded_at' => now(),
            ];

            if ($finalScore >= 60 && empty($member->certificate_code)) {
                $updateData['certificate_code'] = $this->ekskulService->generateCertificateCode($extracurricular, $member);
                $updateData['certificate_issued_at'] = now();
            }

            $member->update($updateData);
            $updatedCount++;
        }

        return back()->with('success', "Rekap Nilai Extra & Sertifikat berhasil diperbarui untuk {$updatedCount} anggota {$extracurricular->name}!");
    }

    /**
     * Cetak / Download Sertifikat Kelulusan Ekstrakurikuler (Khususnya English Club dll.)
     */
    public function downloadCertificate(ExtracurricularMember $member)
    {
        $member->load(['extracurricular.school', 'extracurricular.advisor', 'student.school', 'student.currentClassroom']);
        $ekskul = $member->extracurricular;

        // Pastikan kode sertifikat tersedia
        if (empty($member->certificate_code)) {
            $member->update([
                'certificate_code' => $this->ekskulService->generateCertificateCode($ekskul, $member),
                'certificate_issued_at' => now(),
            ]);
        }

        if ($member->final_score !== null && empty($member->predicate)) {
            $member->update([
                'predicate' => ExtracurricularMember::calculatePredicate($member->final_score),
            ]);
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('extracurricular.certificate-pdf', [
            'member' => $member,
            'ekskul' => $ekskul,
            'student' => $member->student,
            'school' => $ekskul->school ?? $member->student->school,
        ])->setPaper('a4', 'landscape');

        $safeStudent = \Illuminate\Support\Str::slug($member->student->full_name ?? 'siswa');
        $safeEkskul = \Illuminate\Support\Str::slug($ekskul->name ?? 'ekskul');

        return $pdf->stream("Sertifikat-{$safeEkskul}-{$safeStudent}.pdf");
    }

    /**
     * Cetak Rekap Nilai Extra Ekstrakurikuler (PDF).
     */
    public function printGradesRecap(Extracurricular $extracurricular)
    {
        $this->checkSchoolAccess($extracurricular->school_id);

        if ($extracurricular->lms_course_id) {
            $this->ekskulService->syncAllMembersExtraGrades($extracurricular);
        }

        $extracurricular->load([
            'school',
            'advisor',
            'lmsCourse',
            'members' => fn($q) => $q->where('status', 'approved')->with(['student.currentClassroom', 'student.school']),
        ]);

        return view('admin.extracurricular.grades-print', compact('extracurricular'));
    }
}
