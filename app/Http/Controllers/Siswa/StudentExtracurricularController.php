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

        // Unit Ekskul yang Diikuti Siswa
        $myMemberships = ExtracurricularMember::with(['extracurricular.advisor', 'extracurricular.leader', 'extracurricular.activities'])
            ->where('student_id', $student->id)
            ->get();

        $joinedEkskulIds = $myMemberships->pluck('extracurricular_id')->toArray();

        // Katalog Ekskul Tersedia di Sekolah Siswa
        $availableEkskuls = Extracurricular::with(['advisor', 'leader', 'secretary', 'treasurer', 'activeMembers'])
            ->withCount(['activeMembers', 'activities'])
            ->where('school_id', $student->school_id)
            ->where('is_active', true)
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

        if ($extracurricular->school_id !== $student->school_id) {
            return back()->with('error', 'Anda hanya dapat mendaftar ekstrakurikuler di unit sekolah Anda.');
        }

        $notes = $request->input('notes');
        $this->ekskulService->claimMembership($student, $extracurricular, 'anggota', $notes);

        return back()->with('success', "Selamat! Anda resmi terdaftar sebagai anggota {$extracurricular->name} (+15 Poin Reputasi). Kanal Pembda Space Anda kini telah aktif!");
    }
}
