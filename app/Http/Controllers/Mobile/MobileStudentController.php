<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Schedule;
use App\Models\Grade;
use App\Models\StudentBill;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\CbtExam;
use App\Models\PklPlacement;
use App\Models\PklLog;
use App\Models\FinalProject;
use App\Models\FinalProjectLog;
use App\Models\FinalProjectFormat;
use App\Models\FinalProjectMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class MobileStudentController extends Controller
{
    private function getStudent()
    {
        $student = Student::where('user_id', Auth::id())->with('school')->first();
        if ($student) return $student;

        // Fallback for Super Admin / Testers switching role to Siswa
        $user = Auth::user();
        if ($user) {
            $student = Student::when($user->school_id, fn($q) => $q->where('school_id', $user->school_id))
                ->with('school')
                ->first();
        }

        return $student;
    }

    /**
     * Jadwal Pelajaran Siswa Mobile
     */
    public function jadwal()
    {
        $student = $this->getStudent();
        $classroom = $student ? ($student->currentClassroom()->first() ?? $student->classroom ?? $student->classrooms()->latest()->first()) : null;

        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        $dayLabels = [
            'monday' => 'Senin', 'tuesday' => 'Selasa', 'wednesday' => 'Rabu',
            'thursday' => 'Kamis', 'friday' => 'Jumat', 'saturday' => 'Sabtu'
        ];

        $today = strtolower(now()->format('l'));
        $activeDay = in_array($today, $days) ? $today : 'monday';

        $schedulesByDay = [];
        foreach ($days as $day) {
            $schedulesByDay[$day] = collect();
        }

        if ($classroom) {
            $schedules = Schedule::where('classroom_id', $classroom->id)
                ->orWhereHas('teachingAssignment', fn($q) => $q->where('classroom_id', $classroom->id))
                ->with(['subject', 'teacher.user', 'teachingAssignment.subject', 'teachingAssignment.teacher.user', 'timeSlot'])
                ->get()
                ->sortBy(function ($sch) {
                    return $sch->timeSlot->slot_order ?? ($sch->timeSlot->start_time ?? ($sch->start_time ?? '00:00'));
                });

            foreach ($schedules as $sch) {
                $dayKey = strtolower($sch->day_of_week ?? '');
                if (isset($schedulesByDay[$dayKey])) {
                    $schedulesByDay[$dayKey]->push($sch);
                }
            }
        }

        return view('mobile.student.jadwal', compact('student', 'classroom', 'days', 'dayLabels', 'activeDay', 'schedulesByDay'));
    }

    /**
     * Rekap Nilai Siswa Mobile
     */
    public function nilai()
    {
        $student = $this->getStudent();
        $activeSemester = Cache::remember('active_semester', 3600, fn() => Semester::where('is_active', true)->first());

        $grades = collect();
        $avgScore = 0;

        if ($student) {
            $query = Grade::where('student_id', $student->id)->with(['subject']);
            if ($activeSemester) {
                $query->where('semester_id', $activeSemester->id);
            }
            $grades = $query->orderBy('created_at', 'desc')->get();
            $avgScore = round($grades->avg('score') ?? 0, 1);
        }

        // Group by subject
        $gradesBySubject = $grades->groupBy(fn($g) => $g->subject->name ?? 'Lainnya');

        return view('mobile.student.nilai', compact('student', 'activeSemester', 'grades', 'gradesBySubject', 'avgScore'));
    }

    /**
     * Tagihan & Keuangan SPP Mobile
     */
    public function tagihan()
    {
        $student = $this->getStudent();
        $bills = collect();
        $totalAmount = 0;
        $totalPaid = 0;
        $totalOutstanding = 0;

        if ($student) {
            $bills = StudentBill::where('student_id', $student->id)
                ->with(['academicYear', 'paymentType', 'payments'])
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            $monthNames = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];

            foreach ($bills as $bill) {
                $typeName = $bill->paymentType->type_name ?? 'SPP / Uang Sekolah';
                if ($bill->month && isset($monthNames[$bill->month])) {
                    $bill->display_title = $typeName . ' (' . $monthNames[$bill->month] . ' ' . ($bill->year ?? '') . ')';
                } else {
                    $bill->display_title = $typeName;
                }
                $bill->sisa_tunggakan = max(0, $bill->amount - $bill->paid_amount);
            }

            $totalAmount = $bills->sum('amount');
            $totalPaid = $bills->sum('paid_amount');
            $totalOutstanding = max(0, $totalAmount - $totalPaid);
        }

        return view('mobile.student.tagihan', compact('student', 'bills', 'totalAmount', 'totalPaid', 'totalOutstanding'));
    }

    /**
     * CBT / Ujian Online Siswa Mobile
     */
    public function cbt()
    {
        $student = $this->getStudent();
        $classroom = $student ? $student->currentClassroom()->first() : null;
        $exams = collect();

        if ($classroom) {
            $exams = CbtExam::where(function ($query) use ($classroom) {
                $query->whereHas('classrooms', function ($q) use ($classroom) {
                    $q->where('classroom_id', $classroom->id);
                })->orWhere('exam_scope', 'school');
            })->whereIn('status', ['published', 'active'])
              ->with('subject')
              ->latest()
              ->get();
        }

        return view('mobile.student.cbt', compact('student', 'classroom', 'exams'));
    }

    /**
     * PKL & Jurnal Siswa Mobile (Khusus Siswa Kelas XII SMK)
     */
    public function pkl()
    {
        $student = $this->getStudent();
        if (!$student) {
            return redirect()->route('mobile.dashboard')->with('error', 'Profil siswa tidak ditemukan.');
        }

        $classroom = $student->currentClassroom()->first();
        $gradeLevel = $classroom?->grade_level ?? $student->grade_level;
        $schoolType = strtoupper($student->school->type ?? '');

        if ($schoolType !== 'SMK' || $gradeLevel != 12) {
            return redirect()->route('mobile.dashboard')->with('error', 'Akses ditolak: Menu PKL hanya diperuntukkan bagi siswa Kelas XII SMK.');
        }

        $pklPlacement = PklPlacement::where('student_id', $student->id)
            ->with(['dudi', 'teacher', 'academicYear', 'grade'])
            ->first();

        $logs = collect();
        if ($pklPlacement) {
            $logs = PklLog::where('pkl_placement_id', $pklPlacement->id)
                ->orderBy('log_date', 'desc')
                ->get();
        }

        return view('mobile.student.pkl', compact('student', 'classroom', 'pklPlacement', 'logs'));
    }

    /**
     * Submit PKL Daily Log Entry
     */
    public function storePklLog(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'activity_description' => 'required|string',
        ]);

        $student = $this->getStudent();
        if (!$student) {
            return back()->with('error', 'Data siswa tidak ditemukan.');
        }

        $classroom = $student->currentClassroom()->first();
        $gradeLevel = $classroom?->grade_level ?? $student->grade_level;
        $schoolType = strtoupper($student->school->type ?? '');

        if ($schoolType !== 'SMK' || $gradeLevel != 12) {
            return redirect()->route('mobile.dashboard')->with('error', 'Akses ditolak: Jurnal PKL hanya untuk siswa Kelas XII SMK.');
        }

        $pklPlacement = PklPlacement::where('student_id', $student->id)->first();
        if (!$pklPlacement) {
            return back()->with('error', 'Data penempatan PKL Anda belum ditentukan oleh Panitia.');
        }

        PklLog::create([
            'pkl_placement_id' => $pklPlacement->id,
            'log_date' => $request->input('date'),
            'activity' => $request->input('activity_description'),
            'status' => 'pending',
        ]);

        return back()->with('success', 'Jurnal harian PKL berhasil dikirim untuk diverifikasi Pembimbing.');
    }

    /**
     * Project Akhir (SMK) / Penelitian Akhir (SMA) Siswa Mobile
     */
    public function finalProject()
    {
        $student = $this->getStudent();
        if (!$student) {
            return redirect()->route('mobile.dashboard')->with('error', 'Profil siswa tidak ditemukan.');
        }

        $classroom = $student->currentClassroom()->first();
        $gradeLevel = $classroom?->grade_level ?? $student->grade_level;
        $schoolType = strtoupper($student->school->type ?? '');

        if (!in_array($schoolType, ['SMA', 'SMK']) || $gradeLevel != 12) {
            return redirect()->route('mobile.dashboard')->with('error', 'Akses ditolak: Menu Project/Penelitian Akhir hanya untuk siswa Kelas XII SMA atau SMK.');
        }

        $project = $student->currentFinalProject();
        $formats = FinalProjectFormat::where('school_id', $student->school_id)->get();
        $stages = FinalProject::getStages();
        $classmates = collect();

        if ($project) {
            $project->load(['advisor.user', 'examiner.user', 'members.student.user']);
            $logs = $project->logs()->orderByDesc('log_date')->get();
        } else {
            $logs = collect();
            if ($schoolType === 'SMA' && $classroom) {
                // Teman sekelas yang belum memiliki kelompok
                $classmates = $classroom->students()
                    ->where('students.id', '!=', $student->id)
                    ->whereDoesntHave('finalProjectMemberships')
                    ->orderBy('full_name')
                    ->get();
            }
        }

        return view('mobile.student.final_project', compact('student', 'classroom', 'schoolType', 'project', 'logs', 'formats', 'stages', 'classmates'));
    }

    /**
     * Pengajuan Proposal Penelitian Akhir Siswa (Khusus SMA)
     */
    public function proposeFinalProject(Request $request)
    {
        $student = $this->getStudent();
        if (!$student) {
            return back()->with('error', 'Data siswa tidak ditemukan.');
        }

        $classroom = $student->currentClassroom()->first();
        $schoolType = strtoupper($student->school->type ?? '');
        $gradeLevel = $classroom?->grade_level ?? $student->grade_level;

        if ($schoolType !== 'SMA' || $gradeLevel != 12) {
            return back()->with('error', 'Pengajuan proposal mandiri hanya untuk siswa Kelas XII SMA.');
        }

        if ($student->currentFinalProject()) {
            return back()->with('error', 'Anda sudah terdaftar dalam kelompok Penelitian Akhir.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'abstract' => 'required|string',
            'member_ids' => 'nullable|array',
            'member_ids.*' => 'exists:students,id',
        ]);

        DB::beginTransaction();
        try {
            $project = FinalProject::create([
                'student_id' => $student->id,
                'academic_year_id' => $classroom->academic_year_id,
                'type' => 'penelitian_ilmiah',
                'title' => $validated['title'],
                'abstract' => $validated['abstract'],
                'status' => 'pending',
            ]);

            // Add leader
            FinalProjectMember::create([
                'final_project_id' => $project->id,
                'student_id' => $student->id,
                'role' => 'leader'
            ]);

            // Add members
            if (!empty($validated['member_ids'])) {
                foreach ($validated['member_ids'] as $memberId) {
                    FinalProjectMember::create([
                        'final_project_id' => $project->id,
                        'student_id' => $memberId,
                        'role' => 'member'
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('mobile.final-project')->with('success', 'Proposal Penelitian Akhir berhasil diajukan dan menunggu persetujuan Panitia.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengajukan proposal: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Submit Logbook Konsultasi / Bimbingan Project/Penelitian Akhir
     */
    public function storeFinalProjectLog(Request $request)
    {
        $student = $this->getStudent();
        if (!$student) {
            return back()->with('error', 'Data siswa tidak ditemukan.');
        }

        $project = $student->currentFinalProject();
        if (!$project) {
            return back()->with('error', 'Data Project / Penelitian Akhir tidak ditemukan.');
        }

        $validated = $request->validate([
            'stage' => 'required|string',
            'activity' => 'required|string',
            'log_date' => 'required|date',
            'file_attachment' => 'nullable|file|mimes:pdf,doc,docx,zip,rar,jpg,png|max:10240',
            'drive_link' => 'nullable|url|max:255',
        ]);

        $filePath = null;
        if ($request->hasFile('file_attachment')) {
            $filePath = $request->file('file_attachment')->store('final_project_logs', 'public');
        }

        FinalProjectLog::create([
            'final_project_id' => $project->id,
            'stage' => $validated['stage'],
            'activity' => $validated['activity'],
            'log_date' => $validated['log_date'],
            'file_attachment' => $filePath,
            'drive_link' => $validated['drive_link'] ?? null,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Jurnal bimbingan berhasil dikirim ke Guru Pembimbing.');
    }

    /**
     * Unduh Berkas Panduan / Format
     */
    public function downloadFinalProjectFormat($formatId)
    {
        $student = $this->getStudent();
        $format = FinalProjectFormat::where('school_id', $student->school_id)->findOrFail($formatId);

        if (!Storage::disk('public')->exists($format->file_path)) {
            return back()->with('error', 'Berkas panduan tidak ditemukan di server.');
        }

        return response()->download(storage_path('app/public/' . $format->file_path));
    }
}
