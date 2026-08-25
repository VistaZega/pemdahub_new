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

    /**
     * Display list of Extracurricular units.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $schoolId = $request->get('school_id');

        $query = Extracurricular::with(['school', 'advisor', 'leader', 'secretary', 'treasurer', 'activeMembers'])
            ->withCount(['members', 'activeMembers', 'activities']);

        // Filter school: if regular school admin / teacher, restrict to their school
        if ($user && $user->school_id && !in_array($user->role, ['superadmin', 'admin_yayasan', 'yayasan'])) {
            $query->where('school_id', $user->school_id);
        } elseif ($schoolId) {
            $query->where('school_id', $schoolId);
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

        $extracurriculars = $query->orderBy('name')->paginate(12)->withQueryString();

        // 3 Active Schools only (excluding Yayasan oversight entity)
        $schools = School::schoolsOnly()->get();
        if ($schools->isEmpty()) {
            $schools = School::where('type', '!=', 'yayasan')->get();
        }

        $stats = [
            'total_units' => Extracurricular::where('is_active', true)->count(),
            'total_members' => ExtracurricularMember::where('status', 'approved')->count(),
            'total_activities' => ExtracurricularActivity::count(),
        ];

        return view('admin.extracurricular.index', compact('extracurriculars', 'schools', 'stats', 'schoolId'));
    }

    /**
     * Store newly created Extracurricular unit.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',
            'schedule_day_time' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
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

        $ekskul = $this->ekskulService->createExtracurricular($validated, Auth::id());

        return redirect()->route('admin.extracurricular.show', $ekskul)
            ->with('success', 'Unit Ekstrakurikuler berhasil dibuat dan kanal Pembda Space telah aktif!');
    }

    /**
     * Display detailed unit page with members and activities.
     */
    public function show(Extracurricular $extracurricular)
    {
        $extracurricular->load([
            'school',
            'advisor',
            'leader',
            'secretary',
            'treasurer',
            'forumGroup',
            'members.student.currentClassroom',
            'activities.creator'
        ]);

        $students = Student::where('school_id', $extracurricular->school_id)
            ->where('status', 'aktif')
            ->orderBy('full_name')
            ->get();

        $teachers = Teacher::where('school_id', $extracurricular->school_id)
            ->where('is_active', true)
            ->orderBy('full_name')
            ->get();

        return view('admin.extracurricular.show', compact('extracurricular', 'students', 'teachers'));
    }

    /**
     * Update Extracurricular unit.
     */
    public function update(Request $request, Extracurricular $extracurricular)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',
            'schedule_day_time' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
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
        $validated = $request->validate([
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
        $this->ekskulService->approveMember($member, Auth::id());

        return back()->with('success', "Keanggotaan {$member->student->full_name} telah disetujui (+15 Poin Reputasi).");
    }

    /**
     * Add student manually to extracurricular.
     */
    public function addMember(Request $request, Extracurricular $extracurricular)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'role' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $student = Student::findOrFail($validated['student_id']);
        $this->ekskulService->claimMembership($student, $extracurricular, $validated['role'], $validated['notes'] ?? null);

        return back()->with('success', "Siswa {$student->full_name} berhasil ditambahkan sebagai {$validated['role']}.");
    }

    /**
     * Remove or reject member.
     */
    public function removeMember(ExtracurricularMember $member)
    {
        $name = $member->student?->full_name ?? 'Siswa';
        $member->delete();

        return back()->with('success', "Anggota {$name} telah dihapus dari unit ekstrakurikuler.");
    }

    /**
     * Log new activity or exercise session.
     */
    public function addActivity(Request $request, Extracurricular $extracurricular)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'activity_date' => 'required|date',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $validated['extracurricular_id'] = $extracurricular->id;
        $validated['created_by'] = Auth::id();

        ExtracurricularActivity::create($validated);

        return back()->with('success', 'Kegiatan latihan / event ekstrakurikuler berhasil dicatat.');
    }
}
