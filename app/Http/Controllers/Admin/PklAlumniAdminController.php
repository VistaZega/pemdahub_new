<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PklPlacement;
use App\Models\PklLog;
use App\Models\PklGrade;
use App\Models\AlumniProfile;
use App\Models\TracerStudy;
use App\Models\JobPosting;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\AcademicYear;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PklAlumniAdminController extends Controller
{
    private function isSuperAdmin(): bool
    {
        return Auth::user()->isSuperAdmin();
    }

    private function getSchoolId()
    {
        return Auth::user()->school_id;
    }

    // ==========================================
    // 1. PKL PLACEMENTS CRUD
    // ==========================================

    public function placementsIndex(Request $request)
    {
        $isSA = $this->isSuperAdmin();
        $schoolId = $this->getSchoolId();

        $query = PklPlacement::with(['student.school', 'teacher.user', 'grade']);

        if (!$isSA) {
            $query->whereHas('student', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            });
        } elseif ($request->filled('school_id')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('school_id', $request->school_id);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                  ->orWhereHas('student', function ($sq) use ($search) {
                      $sq->where('full_name', 'like', "%{$search}%");
                  });
            });
        }
        
        // Filter by academic year (default to active year)
        $activeYear = \App\Models\AcademicYear::where('is_active', true)->first();
        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        } elseif ($activeYear) {
            $query->where('academic_year_id', $activeYear->id);
        }

        $placements = $query->latest()->paginate(15)->withQueryString();
        $schools = School::where('type', 'SMK')->get();
        $academicYears = \App\Models\AcademicYear::orderByDesc('start_date')->get();

        return view('admin.pkl_alumni.placements.index', compact('placements', 'schools', 'academicYears', 'isSA', 'activeYear'));
    }

    public function placementsMap(Request $request)
    {
        $isSA = $this->isSuperAdmin();
        $schoolId = $this->getSchoolId();

        $query = PklPlacement::with(['student', 'logs' => function($q) {
            $q->whereNotNull('latitude')
              ->whereNotNull('longitude')
              ->latest('log_date');
        }]);

        if (!$isSA) {
            $query->whereHas('student', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            });
        }

        $activeYear = \App\Models\AcademicYear::where('is_active', true)->first();
        if ($activeYear) {
            $query->where('academic_year_id', $activeYear->id);
        }

        $placements = $query->get();

        $mapData = [];
        foreach ($placements as $p) {
            if ($p->logs->isNotEmpty()) {
                $latestLog = $p->logs->first();
                $mapData[] = [
                    'student_name' => $p->student->full_name,
                    'company_name' => $p->company_name,
                    'lat' => $latestLog->latitude,
                    'lng' => $latestLog->longitude,
                    'photo' => $p->student->photo_url,
                    'log_date' => $latestLog->log_date->format('d M Y'),
                ];
            }
        }

        return view('admin.pkl_alumni.placements.map', compact('mapData'));
    }

    public function placementsCreate()
    {
        $isSA = $this->isSuperAdmin();
        $schoolId = $this->getSchoolId();

        $studentsQuery = Student::where('status', 'aktif')->with('school', 'classroom');
        $teachersQuery = \App\Models\Teacher::with('user');
        
        if (!$isSA) {
            $studentsQuery->where('school_id', $schoolId);
            $teachersQuery->where('school_id', $schoolId);
        }
        
        // Hanya siswa SMK tingkat 11/12 yang biasanya PKL
        $students = $studentsQuery->whereHas('school', function($q) {
            $q->where('type', 'SMK');
        })->get();

        $teachers = $teachersQuery->get();
        $academicYears = \App\Models\AcademicYear::orderByDesc('start_date')->get();
        $activeYear = \App\Models\AcademicYear::where('is_active', true)->first();
        $dudis = \App\Models\Dudi::orderBy('name')->get();

        return view('admin.pkl_alumni.placements.create', compact('students', 'teachers', 'academicYears', 'activeYear', 'dudis'));
    }

    public function placementsStore(Request $request)
    {
        $validated = $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:students,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'dudi_id' => 'required|exists:dudis,id',
            'shift' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'teacher_id' => 'required|exists:teachers,id',
        ]);

        $dudi = \App\Models\Dudi::findOrFail($validated['dudi_id']);

        foreach ($validated['student_ids'] as $student_id) {
            $student = Student::findOrFail($student_id);
            // Double check student role scope (auth)
            if (!$this->isSuperAdmin() && $student->school_id !== $this->getSchoolId()) {
                continue; // Skip if unauthorized
            }

            PklPlacement::create([
                'student_id' => $student_id,
                'academic_year_id' => $validated['academic_year_id'],
                'dudi_id' => $dudi->id,
                'company_name' => $dudi->name, // Keep historical data sync
                'shift' => $validated['shift'],
                'company_address' => $dudi->address,
                'mentor_name' => $dudi->mentor_name,
                'mentor_phone' => $dudi->mentor_phone,
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'teacher_id' => $validated['teacher_id'],
                'status' => 'active',
                'signed_token' => Str::random(32),
            ]);
        }

        return redirect()->route('admin.pkl-alumni.placements.index')->with('success', count($validated['student_ids']) . ' Penempatan PKL berhasil dibuat.');
    }

    public function placementsShow($id)
    {
        $isSA = $this->isSuperAdmin();
        $schoolId = $this->getSchoolId();

        $placement = PklPlacement::with(['student.school', 'teacher.user', 'grade', 'logs' => function($q) {
            $q->orderByDesc('log_date');
        }])->findOrFail($id);

        if (!$isSA && $placement->student->school_id !== $schoolId) {
            abort(403);
        }

        return view('admin.pkl_alumni.placements.show', compact('placement'));
    }

    public function approveLog(Request $request, $placementId, $logId)
    {
        $isSA = $this->isSuperAdmin();
        $schoolId = $this->getSchoolId();

        $placement = PklPlacement::with('student')->findOrFail($placementId);
        $log = \App\Models\PklLog::findOrFail($logId);

        if (!$isSA && $placement->student->school_id !== $schoolId) {
            abort(403);
        }

        if ($log->pkl_placement_id !== $placement->id) {
            abort(403);
        }

        $log->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        if ($placement->student && $placement->student->user_id) {
            \App\Models\ReputationLog::log(
                $placement->student->user_id,
                10,
                'pkl_log_approved',
                'Logbook PKL tanggal ' . $log->log_date->format('d/m/Y') . ' disetujui (oleh Admin/Kepsek)',
                $log
            );
        }

        return redirect()->back()->with('success', 'Logbook harian berhasil disetujui.');
    }

    public function rejectLog(Request $request, $placementId, $logId)
    {
        $isSA = $this->isSuperAdmin();
        $schoolId = $this->getSchoolId();

        $placement = PklPlacement::with('student')->findOrFail($placementId);
        $log = \App\Models\PklLog::findOrFail($logId);

        if (!$isSA && $placement->student->school_id !== $schoolId) {
            abort(403);
        }

        if ($log->pkl_placement_id !== $placement->id) {
            abort(403);
        }

        $validated = $request->validate([
            'mentor_notes' => 'required|string|max:1000',
        ]);

        $log->update([
            'status' => 'rejected',
            'mentor_notes' => $validated['mentor_notes'],
            'approved_at' => null,
        ]);

        if ($placement->student && $placement->student->user_id) {
            \App\Models\ReputationLog::removeLog($placement->student->user_id, get_class($log), $log->id);

            \App\Models\Notification::create([
                'user_id' => $placement->student->user_id,
                'school_id' => $placement->student->school_id,
                'title' => '⚠️ Logbook PKL Perlu Direvisi',
                'message' => 'Logbook PKL tanggal ' . \Carbon\Carbon::parse($log->log_date)->format('d/m/Y') . ' diminta revisi oleh Admin/Sekolah. Catatan: ' . $validated['mentor_notes'],
                'type' => 'warning',
                'related_model' => 'PklLog',
                'related_id' => $log->id,
            ]);

            try {
                if ($placement->student->phone) {
                    $waService = app(\App\Services\WhatsAppService::class);
                    if ($waService && $waService->isEnabled()) {
                        $msg = "Halo {$placement->student->full_name}, logbook PKL Anda untuk tanggal " . \Carbon\Carbon::parse($log->log_date)->format('d/m/Y') . " perlu direvisi oleh Pembimbing/Sekolah.\n\n*Catatan Revisi:* {$validated['mentor_notes']}\n\nSilakan perbaiki melalui PembdaHUB: " . url('/m/pkl');
                        $waService->sendMessage($placement->student->phone, $msg);
                    }
                }
            } catch (\Throwable $e) {
                \Log::warning('Gagal kirim notifikasi WA revisi PKL: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('success', 'Catatan revisi logbook berhasil dikirim ke siswa.');
    }

    public function placementsEdit($id)
    {
        $isSA = $this->isSuperAdmin();
        $schoolId = $this->getSchoolId();

        $placement = PklPlacement::findOrFail($id);
        
        if (!$isSA && $placement->student->school_id !== $schoolId) {
            abort(403);
        }

        $teachers = \App\Models\Teacher::with('user');
        if (!$isSA) {
            $teachers->where('school_id', $schoolId);
        }
        $teachers = $teachers->get();
        
        $academicYears = \App\Models\AcademicYear::orderByDesc('start_date')->get();
        $dudis = \App\Models\Dudi::orderBy('name')->get();

        return view('admin.pkl_alumni.placements.edit', compact('placement', 'teachers', 'academicYears', 'dudis'));
    }

    public function placementsUpdate(Request $request, $id)
    {
        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'dudi_id' => 'required|exists:dudis,id',
            'shift' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'teacher_id' => 'required|exists:teachers,id',
            'status' => 'required|in:active,completed,cancelled',
        ]);

        $placement = PklPlacement::findOrFail($id);
        
        if (!$this->isSuperAdmin() && $placement->student->school_id !== $this->getSchoolId()) {
            abort(403);
        }

        $dudi = \App\Models\Dudi::findOrFail($validated['dudi_id']);

        $placement->update([
            'academic_year_id' => $validated['academic_year_id'],
            'dudi_id' => $dudi->id,
            'company_name' => $dudi->name,
            'shift' => $validated['shift'],
            'company_address' => $dudi->address,
            'mentor_name' => $dudi->mentor_name,
            'mentor_phone' => $dudi->mentor_phone,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'teacher_id' => $validated['teacher_id'],
            'status' => $validated['status'],
        ]);

        return redirect()->route('admin.pkl-alumni.placements.index')->with('success', 'Penempatan PKL berhasil diperbarui.');
    }

    public function placementsDestroy($id)
    {
        $placement = PklPlacement::findOrFail($id);

        if (!$this->isSuperAdmin() && $placement->student->school_id !== $this->getSchoolId()) {
            abort(403);
        }

        $placement->delete();

        return redirect()->route('admin.pkl-alumni.placements.index')->with('success', 'Penempatan PKL berhasil dihapus.');
    }

    // ==========================================
    // 2. TRACER STUDY PENELUSURAN ALUMNI
    // ==========================================

    public function tracerIndex(Request $request)
    {
        $isSA = $this->isSuperAdmin();
        $schoolId = $this->getSchoolId();

        $query = TracerStudy::with(['alumni.student', 'alumni.school']);

        if (!$isSA) {
            $query->whereHas('alumni', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            });
        } elseif ($request->filled('school_id')) {
            $query->whereHas('alumni', function ($q) use ($request) {
                $q->where('school_id', $request->school_id);
            });
        }

        if ($request->filled('status')) {
            $query->where('employment_status', $request->status);
        }

        if ($request->filled('graduation_year')) {
            $query->whereHas('alumni', function ($q) use ($request) {
                $q->where('graduation_year', $request->graduation_year);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('alumni', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%");
            });
        }

        $tracers = $query->latest('survey_date')->paginate(15)->withQueryString();
        $schools = School::where('type', 'SMK')->get();

        return view('admin.pkl_alumni.tracer.index', compact('tracers', 'schools', 'isSA'));
    }

    // ==========================================
    // 3. JOB POSTINGS CRUD
    // ==========================================

    public function jobsIndex(Request $request)
    {
        $isSA = $this->isSuperAdmin();
        $schoolId = $this->getSchoolId();

        $query = JobPosting::with('creator');

        if (!$isSA) {
            $query->whereHas('creator', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        $jobs = $query->latest()->paginate(15)->withQueryString();

        return view('admin.pkl_alumni.jobs.index', compact('jobs'));
    }

    public function jobsCreate()
    {
        return view('admin.pkl_alumni.jobs.create');
    }

    public function jobsStore(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'requirements' => 'nullable|string',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'salary_range' => 'nullable|string|max:100',
        ]);

        JobPosting::create(array_merge($validated, [
            'is_active' => true,
            'created_by' => Auth::id(),
        ]));

        return redirect()->route('admin.pkl-alumni.jobs.index')->with('success', 'Lowongan pekerjaan berhasil diterbitkan.');
    }

    public function jobsEdit($id)
    {
        $job = JobPosting::findOrFail($id);

        // Security check
        if (!$this->isSuperAdmin() && $job->creator->school_id !== $this->getSchoolId()) {
            abort(403);
        }

        return view('admin.pkl_alumni.jobs.edit', compact('job'));
    }

    public function jobsUpdate(Request $request, $id)
    {
        $job = JobPosting::findOrFail($id);

        if (!$this->isSuperAdmin() && $job->creator->school_id !== $this->getSchoolId()) {
            abort(403);
        }

        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'requirements' => 'nullable|string',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'salary_range' => 'nullable|string|max:100',
            'is_active' => 'required|boolean',
        ]);

        $job->update($validated);

        return redirect()->route('admin.pkl-alumni.jobs.index')->with('success', 'Lowongan pekerjaan berhasil diperbarui.');
    }

    public function jobsDestroy($id)
    {
        $job = JobPosting::findOrFail($id);

        if (!$this->isSuperAdmin() && $job->creator->school_id !== $this->getSchoolId()) {
            abort(403);
        }

        $job->delete();

        return redirect()->route('admin.pkl-alumni.jobs.index')->with('success', 'Lowongan pekerjaan berhasil dihapus.');
    }
}
