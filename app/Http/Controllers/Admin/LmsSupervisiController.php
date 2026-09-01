<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LmsCourse;
use App\Models\LmsModule;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LmsSupervisiController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $isSuperAdmin = $user->isSuperAdmin() || $user->isKetuaYayasan();
        $schoolId = $request->get('school_id', $user->school_id);

        $query = LmsCourse::with(['teacher.user', 'subject', 'reviewer', 'lmsClasses.classroom'])
            ->withCount(['materials', 'assignments', 'quizzes', 'modules']);

        if (!$isSuperAdmin) {
            $query->where('school_id', $user->school_id);
        } elseif ($schoolId) {
            $query->where('school_id', $schoolId);
        }

        $reviewStatus = $request->get('review_status');
        if ($reviewStatus) {
            $query->where('review_status', $reviewStatus);
        }

        $courses = $query->orderByRaw("FIELD(review_status, 'pending') DESC")
            ->orderByDesc('created_at')
            ->paginate(15);

        $schools = $isSuperAdmin
            ? School::where('is_active', true)->schoolsOnly()->orderBy('name')->get()
            : collect();

        $stats = [
            'total' => (clone $query)->count(),
            'pending' => (clone $query)->where('review_status', 'pending')->count(),
            'approved' => (clone $query)->where('review_status', 'approved')->count(),
            'rejected' => (clone $query)->where('review_status', 'rejected')->count(),
        ];

        return view('admin.lms.supervisi', compact('courses', 'schools', 'schoolId', 'isSuperAdmin', 'stats', 'reviewStatus'));
    }

    public function preview(LmsCourse $course)
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin() && !$user->isKetuaYayasan() && $course->school_id !== $user->school_id) {
            abort(403);
        }

        $course->load([
            'teacher.user', 'subject',
            'modules' => fn($q) => $q->where('is_active', true)->orderBy('sequence')->with([
                'materials' => fn($mq) => $mq->where('is_published', true)->orderBy('order_number'),
            ]),
            'assignments' => fn($q) => $q->where('is_published', true),
            'quizzes' => fn($q) => $q->where('is_published', true),
        ]);

        return view('admin.lms.supervisi-preview', compact('course'));
    }

    public function review(Request $request, LmsCourse $course)
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin() && !$user->isKetuaYayasan() && $course->school_id !== $user->school_id) {
            abort(403);
        }

        $validated = $request->validate([
            'action' => 'required|in:approve,reject,pending',
            'review_note' => 'nullable|string|max:1000',
        ]);

        $statusMap = [
            'approve' => 'approved',
            'reject' => 'rejected',
            'pending' => 'pending',
        ];

        $course->update([
            'review_status' => $statusMap[$validated['action']],
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
            'review_note' => $validated['review_note'] ?? $course->review_note,
        ]);

        // Notify teacher
        try {
            $teacherUser = $course->teacher?->user;
            if ($teacherUser) {
                $statusLabel = match ($course->review_status) {
                    'approved' => 'Disetujui ✅',
                    'rejected' => 'Ditolak ❌',
                    'pending' => 'Diminta Review Ulang ⏳',
                    default => $course->review_status,
                };
                \App\Models\Notification::create([
                    'user_id' => $teacherUser->id,
                    'type' => 'info',
                    'title' => 'Review Kursus: ' . $course->course_name,
                    'message' => 'Kursus Anda "' . $course->course_name . '" telah direview: ' . $statusLabel . ($course->review_note ? ' — Catatan: ' . $course->review_note : ''),
                    'related_model' => 'LmsCourse',
                    'related_id' => $course->id,
                ]);
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to send review notification: ' . $e->getMessage());
        }

        $statusLabel = match ($course->review_status) {
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'pending' => 'Diminta Review Ulang',
            default => $course->review_status,
        };

        return redirect()->route('admin.lms.supervisi.index')
            ->with('success', "Status '{$course->course_name}' → {$statusLabel}");
    }
}