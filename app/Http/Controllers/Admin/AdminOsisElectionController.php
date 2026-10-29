<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\OsisElection;
use App\Models\OsisCandidate;
use App\Models\OsisVoter;
use App\Models\School;
use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;

class AdminOsisElectionController extends Controller
{
    /**
     * Daftar Semua Event Pemilihan OSIS
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = OsisElection::with(['school', 'candidates', 'academicYear'])
            ->withCount(['candidates', 'voters', 'ballots']);

        // Jika admin sekolah, filter hanya sekolahnya
        if ($user->role === 'admin' && $user->school_id) {
            $query->where('school_id', $user->school_id);
        }

        $elections = $query->latest()->paginate(10);
        $schools = School::schoolsOnly()->get();
        $academicYears = AcademicYear::orderBy('year', 'desc')->get();

        return view('admin.osis.index', compact('elections', 'schools', 'academicYears'));
    }

    /**
     * Form Tambah Event Pemilihan OSIS
     */
    public function create()
    {
        $schools = School::schoolsOnly()->get();
        $academicYears = AcademicYear::orderBy('year', 'desc')->get();
        $activeYear = AcademicYear::where('is_active', true)->first();

        return view('admin.osis.create', compact('schools', 'academicYears', 'activeYear'));
    }

    /**
     * Simpan Event Baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'school_id' => 'required|exists:schools,id',
            'title' => 'required|string|max:255',
            'period' => 'required|string|max:50',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
        ]);

        $school = School::findOrFail($request->school_id);
        $slug = Str::slug($request->title . ' ' . $request->period . ' ' . Str::random(4));

        $bannerPath = null;
        if ($request->hasFile('banner_image')) {
            $bannerPath = $request->file('banner_image')->store('osis/banners', 'public');
        }

        $election = OsisElection::create([
            'school_id' => $school->id,
            'academic_year_id' => $request->academic_year_id,
            'title' => $request->title,
            'slug' => $slug,
            'period' => $request->period,
            'theme_tagline' => $request->theme_tagline ?? 'Bersama Membangun Masa Depan yang Lebih Baik',
            'description' => $request->description,
            'banner_image' => $bannerPath,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'status' => $request->status ?? 'voting_open',
            'allow_teachers' => $request->has('allow_teachers'),
            'allow_staff' => $request->has('allow_staff'),
            'show_realtime_count' => $request->has('show_realtime_count'),
            'is_active' => true,
        ]);

        return redirect()->route('admin.osis.candidates', $election->id)
            ->with('success', 'Event Pemilihan OSIS berhasil dibuat! Silakan input daftar Paslon.');
    }

    /**
     * Form Edit Event
     */
    public function edit($id)
    {
        $election = OsisElection::with(['school', 'academicYear'])->findOrFail($id);
        $schools = School::schoolsOnly()->get();
        $academicYears = AcademicYear::orderBy('year', 'desc')->get();

        return view('admin.osis.edit', compact('election', 'schools', 'academicYears'));
    }

    /**
     * Update Event
     */
    public function update(Request $request, $id)
    {
        $election = OsisElection::findOrFail($id);

        $request->validate([
            'school_id' => 'required|exists:schools,id',
            'title' => 'required|string|max:255',
            'period' => 'required|string|max:50',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
        ]);

        $data = [
            'school_id' => $request->school_id,
            'academic_year_id' => $request->academic_year_id,
            'title' => $request->title,
            'period' => $request->period,
            'theme_tagline' => $request->theme_tagline,
            'description' => $request->description,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'status' => $request->status,
            'allow_teachers' => $request->has('allow_teachers'),
            'allow_staff' => $request->has('allow_staff'),
            'show_realtime_count' => $request->has('show_realtime_count'),
            'is_active' => $request->has('is_active'),
        ];

        if ($request->hasFile('banner_image')) {
            $data['banner_image'] = $request->file('banner_image')->store('osis/banners', 'public');
        }

        $election->update($data);

        return redirect()->route('admin.osis.index')
            ->with('success', 'Pengaturan Pemilihan OSIS berhasil diperbarui!');
    }

    /**
     * Kelola Paslon untuk Event Tertentu
     */
    public function candidates($id)
    {
        $election = OsisElection::with(['school', 'candidates'])->findOrFail($id);
        return view('admin.osis.candidates', compact('election'));
    }

    /**
     * Simpan / Tambah Paslon
     */
    public function storeCandidate(Request $request, $id)
    {
        $election = OsisElection::findOrFail($id);

        $request->validate([
            'candidate_number' => 'required|integer|min:1',
            'leader_name' => 'required|string|max:255',
            'quote_slogan' => 'nullable|string',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('osis/candidates', 'public');
        }

        $posterPath = null;
        if ($request->hasFile('poster_flyer')) {
            $posterPath = $request->file('poster_flyer')->store('osis/posters', 'public');
        }

        // Parse missions & programs from textarea lines
        $missions = array_filter(array_map('trim', explode("\n", $request->mission ?? '')));
        $programs = array_filter(array_map('trim', explode("\n", $request->work_programs ?? '')));

        OsisCandidate::create([
            'election_id' => $election->id,
            'candidate_number' => $request->candidate_number,
            'candidate_type' => $request->candidate_type ?? 'presidium_ksb',
            'leader_name' => $request->leader_name,
            'leader_class' => $request->leader_class,
            'secretary_name' => $request->secretary_name,
            'treasurer_name' => $request->treasurer_name,
            'vice_name' => $request->vice_name,
            'quote_slogan' => $request->quote_slogan,
            'vision' => $request->vision,
            'mission' => array_values($missions),
            'work_programs' => array_values($programs),
            'photo' => $photoPath,
            'poster_flyer' => $posterPath,
            'video_url' => $request->video_url,
            'color_badge' => $request->color_badge ?? '#3b82f6',
        ]);

        return back()->with('success', 'Paslon No. ' . $request->candidate_number . ' berhasil ditambahkan!');
    }

    /**
     * Update Data Paslon
     */
    public function updateCandidate(Request $request, $electionId, $candidateId)
    {
        $candidate = OsisCandidate::where('election_id', $electionId)->findOrFail($candidateId);

        $request->validate([
            'candidate_number' => 'required|integer|min:1',
            'leader_name' => 'required|string|max:255',
        ]);

        $data = [
            'candidate_number' => $request->candidate_number,
            'candidate_type' => $request->candidate_type ?? $candidate->candidate_type,
            'leader_name' => $request->leader_name,
            'leader_class' => $request->leader_class,
            'secretary_name' => $request->secretary_name,
            'treasurer_name' => $request->treasurer_name,
            'vice_name' => $request->vice_name,
            'quote_slogan' => $request->quote_slogan,
            'vision' => $request->vision,
            'video_url' => $request->video_url,
            'color_badge' => $request->color_badge ?? $candidate->color_badge,
        ];

        if ($request->has('mission')) {
            $missions = array_filter(array_map('trim', explode("\n", $request->mission ?? '')));
            $data['mission'] = array_values($missions);
        }

        if ($request->has('work_programs')) {
            $programs = array_filter(array_map('trim', explode("\n", $request->work_programs ?? '')));
            $data['work_programs'] = array_values($programs);
        }

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('osis/candidates', 'public');
        }

        if ($request->hasFile('poster_flyer')) {
            $data['poster_flyer'] = $request->file('poster_flyer')->store('osis/posters', 'public');
        }

        $candidate->update($data);

        return back()->with('success', 'Data Paslon No. ' . $candidate->candidate_number . ' berhasil diperbarui!');
    }

    /**
     * Hapus Paslon
     */
    public function destroyCandidate($electionId, $candidateId)
    {
        $candidate = OsisCandidate::where('election_id', $electionId)->findOrFail($candidateId);
        $candidate->delete();

        return back()->with('success', 'Paslon berhasil dihapus!');
    }

    /**
     * Generate / Sinkronkan DPT Otomatis dari Database Siswa & Guru
     */
    public function generateVoters($id)
    {
        $election = OsisElection::findOrFail($id);
        $schoolId = $election->school_id;

        // Ambil Siswa Aktif di unit sekolah ini
        $activeStudents = Student::where('school_id', $schoolId)
            ->where('status', 'aktif')
            ->whereNotNull('user_id')
            ->get();

        $countAdded = 0;

        foreach ($activeStudents as $student) {
            $voter = OsisVoter::firstOrCreate(
                [
                    'election_id' => $election->id,
                    'user_id' => $student->user_id,
                ],
                [
                    'voter_type' => 'student',
                    'has_voted' => false,
                ]
            );
            if ($voter->wasRecentlyCreated) {
                $countAdded++;
            }
        }

        // Ambil Guru jika diizinkan
        if ($election->allow_teachers) {
            $teachers = Teacher::where('school_id', $schoolId)
                ->where('is_active', true)
                ->whereNotNull('user_id')
                ->get();

            foreach ($teachers as $teacher) {
                $voter = OsisVoter::firstOrCreate(
                    [
                        'election_id' => $election->id,
                        'user_id' => $teacher->user_id,
                    ],
                    [
                        'voter_type' => 'teacher',
                        'has_voted' => false,
                    ]
                );
                if ($voter->wasRecentlyCreated) {
                    $countAdded++;
                }
            }
        }

        return back()->with('success', 'Sinkronisasi DPT selesai! ' . $countAdded . ' pemilih baru berhasil didaftarkan ke DPT.');
    }

    /**
     * Buka / Tutup TPS Manual
     */
    public function toggleStatus($id)
    {
        $election = OsisElection::findOrFail($id);
        if ($election->status === 'voting_open') {
            $election->update(['status' => 'voting_closed']);
            $msg = 'Bilik suara OSIS berhasil DITUTUP.';
        } else {
            $election->update(['status' => 'voting_open']);
            $msg = 'Bilik suara OSIS berhasil DIBUKA.';
        }

        return back()->with('success', $msg);
    }
}
