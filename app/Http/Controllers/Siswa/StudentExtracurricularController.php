<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Extracurricular;
use App\Models\ExtracurricularMember;
use App\Services\ExtracurricularService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentExtracurricularController extends Controller
{
    protected ExtracurricularService $ekskulService;

    public function __construct(ExtracurricularService $ekskulService)
    {
        $this->ekskulService = $ekskulService;
    }

    /**
     * Display student extracurricular catalog and joined units.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $student = $user->student;

        if (!$student) {
            return redirect()->route('siswa.dashboard')->with('error', 'Data profil siswa tidak ditemukan.');
        }

        // Unit Ekskul yang Diikuti Siswa (dengan eager load Squad Roster & Pembina)
        $myMemberships = ExtracurricularMember::with([
            'extracurricular.school',
            'extracurricular.advisor',
            'extracurricular.leader.classroom',
            'extracurricular.secretary.classroom',
            'extracurricular.treasurer.classroom',
            'extracurricular.activeMembers.student.school',
            'extracurricular.activeMembers.student.classroom',
            'extracurricular.activities'
        ])
            ->where('student_id', $student->id)
            ->get();

        $joinedEkskulIds = $myMemberships->pluck('extracurricular_id')->toArray();

        // Katalog Ekskul Tersedia: Unit Sekolah Siswa + Unit Tingkat Yayasan (Marching Band dll.)
        $availableEkskuls = Extracurricular::with([
            'school',
            'advisor',
            'leader.classroom',
            'secretary.classroom',
            'treasurer.classroom',
            'activeMembers.student.school',
            'activeMembers.student.classroom'
        ])
            ->withCount(['activeMembers', 'activities'])
            ->where(function ($q) use ($student) {
                $q->where('school_id', $student->school_id)
                  ->orWhere('scope', 'yayasan')
                  ->orWhereNull('school_id');
            })
            ->where('is_active', true)
            ->orderByRaw("CASE WHEN scope = 'yayasan' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get();

        return view('siswa.extracurricular.index', compact('student', 'myMemberships', 'availableEkskuls', 'joinedEkskulIds'));
    }

    /**
     * Student claims or joins an Extracurricular unit.
     */
    public function claim(Request $request, Extracurricular $extracurricular)
    {
        $user = Auth::user();
        $student = $user->student;

        if (!$student) {
            return back()->with('error', 'Profil siswa tidak ditemukan.');
        }

        if (!$extracurricular->isFoundationLevel() && $extracurricular->school_id !== $student->school_id) {
            return back()->with('error', 'Anda hanya dapat mendaftar ekstrakurikuler di unit sekolah Anda atau unit naungan Yayasan.');
        }

        $section = $request->input('section');
        $notes = $request->input('notes');
        $this->ekskulService->claimMembership($student, $extracurricular, 'anggota', $notes, $section);

        $sectionMsg = $section ? " (Section: {$section})" : "";
        return back()->with('success', "Selamat! Anda resmi terdaftar sebagai anggota {$extracurricular->name}{$sectionMsg} (+15 Poin Reputasi). Kanal Pembda Space Anda kini telah aktif!");
    }

    /**
     * Direct Squad Lounge opener: Auto-provisions forum group, registers member if needed, and redirects.
     */
    public function openSpace(Request $request, Extracurricular $extracurricular)
    {
        $user = Auth::user();
        $student = $user->student;

        // Auto-provision or verify ForumGroup
        $forumGroup = $this->ekskulService->ensureForumGroup($extracurricular);

        // Ensure user is enrolled into group members
        if ($user) {
            $membership = null;
            if ($student) {
                $membership = ExtracurricularMember::where('extracurricular_id', $extracurricular->id)
                    ->where('student_id', $student->id)
                    ->where('status', 'approved')
                    ->first();
            }

            \App\Models\ForumGroupMember::firstOrCreate(
                [
                    'group_id' => $forumGroup->id,
                    'user_id' => $user->id,
                ],
                [
                    'role' => ($membership && in_array($membership->role, ['ketua', 'wakil_ketua', 'sekretaris', 'bendahara'])) ? 'moderator' : 'member',
                    'joined_at' => now(),
                ]
            );
        }

        // Detect if request is from mobile app/view
        if ($request->header('User-Agent') && (str_contains(strtolower($request->header('User-Agent')), 'mobile') || str_contains(strtolower($request->header('User-Agent')), 'android') || str_contains(strtolower($request->header('User-Agent')), 'iphone'))) {
            return redirect()->route('mobile.space.group.show', $forumGroup->id);
        }

        return redirect()->route('forum.index', ['group' => $forumGroup->id]);
    }
}
