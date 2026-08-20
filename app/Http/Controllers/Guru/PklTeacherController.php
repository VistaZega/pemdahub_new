<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\PklPlacement;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PklTeacherController extends Controller
{
    private function getTeacher(): Teacher
    {
        return Teacher::where('user_id', Auth::id())->firstOrFail();
    }

    public function index()
    {
        $teacher = $this->getTeacher();
        $activeYear = \App\Models\AcademicYear::where('is_active', true)->first();
        
        $placements = PklPlacement::where('teacher_id', $teacher->id)
            ->with(['student', 'logs', 'grade', 'dudi'])
            ->orderByDesc('id')
            ->get();

        $pklHours = $teacher->getPklSupervisorHours($activeYear?->id);

        return view('guru.pkl.index', compact('teacher', 'placements', 'pklHours', 'activeYear'));
    }

    public function show(PklPlacement $placement)
    {
        $teacher = $this->getTeacher();

        if ($placement->teacher_id !== $teacher->id) {
            abort(403, 'Anda bukan pembimbing untuk siswa ini.');
        }

        $placement->load(['student', 'logs' => function($q) {
            $q->orderByDesc('log_date');
        }, 'grade']);

        return view('guru.pkl.show', compact('teacher', 'placement'));
    }

    public function approveLog(PklPlacement $placement, \App\Models\PklLog $log)
    {
        $teacher = $this->getTeacher();

        if ($placement->teacher_id !== $teacher->id || $log->pkl_placement_id !== $placement->id) {
            abort(403, 'Akses ditolak.');
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
                'Logbook PKL tanggal ' . $log->log_date->format('d/m/Y') . ' disetujui (oleh Pembimbing Sekolah)',
                $log
            );
        }

        return redirect()->back()->with('success', 'Logbook harian berhasil disetujui.');
    }

    public function rejectLog(PklPlacement $placement, \App\Models\PklLog $log, Request $request)
    {
        $teacher = $this->getTeacher();

        if ($placement->teacher_id !== $teacher->id || $log->pkl_placement_id !== $placement->id) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'mentor_notes' => 'required|string|max:1000',
        ]);

        $log->update([
            'status' => 'rejected',
            'mentor_notes' => $validated['mentor_notes'],
            'approved_at' => null,
        ]);

        // Revoke points if previously approved
        if ($placement->student && $placement->student->user_id) {
            \App\Models\ReputationLog::removeLog($placement->student->user_id, get_class($log), $log->id);

            // In-app Notification for student
            \App\Models\Notification::create([
                'user_id' => $placement->student->user_id,
                'school_id' => $placement->student->school_id,
                'title' => '⚠️ Logbook PKL Perlu Direvisi',
                'message' => 'Logbook PKL tanggal ' . \Carbon\Carbon::parse($log->log_date)->format('d/m/Y') . ' diminta revisi oleh Pembimbing. Catatan: ' . $validated['mentor_notes'],
                'type' => 'warning',
                'related_model' => 'PklLog',
                'related_id' => $log->id,
            ]);

            // WhatsApp Notification if available
            try {
                if ($placement->student->phone) {
                    $waService = app(\App\Services\WhatsAppService::class);
                    if ($waService && $waService->isEnabled()) {
                        $msg = "Halo {$placement->student->full_name}, logbook PKL Anda untuk tanggal " . \Carbon\Carbon::parse($log->log_date)->format('d/m/Y') . " perlu direvisi oleh Pembimbing ({$teacher->full_name}).\n\n*Catatan Revisi:* {$validated['mentor_notes']}\n\nSilakan segera perbaiki melalui PembdaHUB: " . url('/m/pkl');
                        $waService->sendMessage($placement->student->phone, $msg);
                    }
                }
            } catch (\Throwable $e) {
                \Log::warning('Gagal kirim notifikasi WA revisi PKL: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('success', 'Catatan revisi logbook berhasil dikirim ke siswa.');
    }
}
