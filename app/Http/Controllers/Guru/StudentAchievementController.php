<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\ReputationLog;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentAchievementController extends Controller
{
    /**
     * Get the authenticated teacher record.
     */
    private function getTeacher()
    {
        $user = Auth::user();
        return Teacher::where('user_id', $user->id)->first();
    }

    /**
     * Display achievements of students in Homeroom classes for justification.
     */
    public function index(Request $request)
    {
        $teacher = $this->getTeacher();
        $activeYear = AcademicYear::where('is_active', true)->first() ?? AcademicYear::latest()->first();

        // Get homeroom classrooms assigned to this teacher
        $tIds = Auth::user() ? Auth::user()->teacherIds() : ($teacher ? $teacher->allTeacherIds() : []);
        $homeroomClassrooms = Classroom::whereIn('homeroom_teacher_id', $tIds)
            ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->with(['school', 'students' => function ($q) use ($activeYear) {
                $q->whereIn('student_classes.status', ['aktif', 'enrolled', 'active']);
                if ($activeYear) {
                    $q->where('student_classes.academic_year_id', $activeYear->id);
                }
            }])
            ->get();

        if ($homeroomClassrooms->isEmpty()) {
            $homeroomClassrooms = Classroom::whereIn('homeroom_teacher_id', $tIds)
                ->where('is_active', true)
                ->with(['school', 'students' => function ($q) {
                    $q->whereIn('student_classes.status', ['aktif', 'enrolled', 'active']);
                }])
                ->get();
        }

        $isWaliKelas = $homeroomClassrooms->isNotEmpty();

        // Get all student IDs under homeroom classes
        $studentIds = collect();
        if ($isWaliKelas) {
            foreach ($homeroomClassrooms as $cls) {
                $studentIds = $studentIds->merge($cls->students->pluck('id'));
            }
            $studentIds = $studentIds->unique()->values();
        }

        // Base query
        $baseQuery = StudentAchievement::whereIn('student_id', $studentIds)
            ->with(['student.currentClassroom', 'academicYear', 'verifiedBy']);

        // Stats calculation
        $stats = [
            'total'    => (clone $baseQuery)->count(),
            'pending'  => (clone $baseQuery)->where('status', 'pending')->count(),
            'verified' => (clone $baseQuery)->where('status', 'verified')->count(),
            'rejected' => (clone $baseQuery)->where('status', 'rejected')->count(),
        ];

        // Filter by status
        $statusFilter = $request->query('status', $stats['pending'] > 0 ? 'pending' : 'all');
        $query = clone $baseQuery;

        if ($statusFilter && in_array($statusFilter, ['pending', 'verified', 'rejected'])) {
            $query->where('status', $statusFilter);
        }

        // Filter by classroom
        if ($request->filled('classroom_id')) {
            $query->whereHas('student.classrooms', function ($q) use ($request) {
                $q->where('classrooms.id', $request->classroom_id);
            });
        }

        // Search by student name or achievement title
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('student', fn($sq) => $sq->where('full_name', 'like', "%{$search}%")->orWhere('nisn', 'like', "%{$search}%"));
            });
        }

        // Order: pending first, then latest date
        $achievements = $query->orderByRaw("CASE WHEN status = 'pending' THEN 0 WHEN status = 'verified' THEN 1 ELSE 2 END")
            ->orderByDesc('achievement_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('guru.prestasi.index', compact(
            'teacher',
            'homeroomClassrooms',
            'isWaliKelas',
            'achievements',
            'stats',
            'statusFilter',
            'activeYear'
        ));
    }

    /**
     * Justify an achievement (Approve / Reject).
     */
    public function justify(Request $request, StudentAchievement $achievement)
    {
        $teacher = $this->getTeacher();
        $student = $achievement->student;

        if (!$student) {
            return back()->with('error', 'Data siswa tidak ditemukan.');
        }

        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'notes'  => 'nullable|string|max:500',
        ], [
            'action.required' => 'Aksi justifikasi wajib dipilih.',
            'action.in'       => 'Pilihan aksi tidak valid.',
        ]);

        if ($validated['action'] === 'reject' && empty(trim($validated['notes'] ?? ''))) {
            return back()->withErrors(['notes' => 'Alasan penolakan / pencabutan poin wajib diisi saat menolak prestasi.'])->withInput();
        }

        if ($validated['action'] === 'approve') {
            // Update achievement to verified
            $achievement->update([
                'status'             => 'verified',
                'verified_by'        => Auth::id(),
                'verified_at'        => now(),
                'verification_notes' => $validated['notes'] ?? 'Prestasi telah diverifikasi dan diakui oleh Wali Kelas.',
            ]);

            // Ensure reputation points are logged / retained
            if ($student->user_id) {
                try {
                    ReputationLog::log(
                        $student->user_id,
                        $achievement->points,
                        'achievement',
                        "Penghargaan Prestasi: {$achievement->title} (" . strtoupper($achievement->level_label) . ")",
                        $achievement
                    );
                } catch (\Exception $e) {
                    \Log::warning('Gagal mencatat poin reputasi justifikasi: ' . $e->getMessage());
                }
            }

            return back()->with('success', "Prestasi '{$achievement->title}' milik {$student->full_name} berhasil DIAKUI. Poin reputasi (+{$achievement->points} Pts) tetap sah!");
        }

        if ($validated['action'] === 'reject') {
            // Update achievement to rejected
            $achievement->update([
                'status'             => 'rejected',
                'verified_by'        => Auth::id(),
                'verified_at'        => now(),
                'verification_notes' => $validated['notes'],
            ]);

            // Revoke / Deduct reputation points from student
            if ($student->user_id) {
                try {
                    ReputationLog::removeLog(
                        $student->user_id,
                        StudentAchievement::class,
                        $achievement->id
                    );
                } catch (\Exception $e) {
                    \Log::warning('Gagal mencabut poin reputasi justifikasi tolak: ' . $e->getMessage());
                }
            }

            return back()->with('success', "Prestasi '{$achievement->title}' milik {$student->full_name} TIDAK DIAKUI. Poin reputasi ({$achievement->points} Pts) telah berhasil ditarik kembali!");
        }

        return back();
    }
}
