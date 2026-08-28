<?php

namespace App\Http\Controllers;

use App\Models\PklPlacement;
use App\Models\PklLog;
use App\Models\PklGrade;
use Illuminate\Http\Request;

class PklMentorController extends Controller
{
    private function getPlacement($token): PklPlacement
    {
        return PklPlacement::where('signed_token', $token)
            ->with(['student', 'grade'])
            ->firstOrFail();
    }

    public function portal($token)
    {
        $placement = $this->getPlacement($token);
        
        $logs = PklLog::where('pkl_placement_id', $placement->id)
            ->orderByDesc('log_date')
            ->get();

        return view('mentor.pkl_portal', compact('placement', 'logs', 'token'));
    }

    public function approveLog($token, PklLog $log)
    {
        $placement = $this->getPlacement($token);

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
                'pkl',
                'Logbook PKL harian disetujui (Tanggal: ' . $log->log_date->format('Y-m-d') . ')',
                $log
            );
        }

        return redirect()->route('mentor.pkl.portal', $token)->with('success', 'Log harian berhasil disetujui.');
    }

    public function rejectLog($token, PklLog $log, Request $request)
    {
        $placement = $this->getPlacement($token);

        if ($log->pkl_placement_id !== $placement->id) {
            abort(403);
        }

        $validated = $request->validate([
            'mentor_notes' => 'required|string|max:500',
        ]);

        $log->update([
            'status' => 'rejected',
            'mentor_notes' => $validated['mentor_notes'],
            'approved_at' => null,
        ]);

        if ($placement->student && $placement->student->user_id) {
            \App\Models\ReputationLog::removeLog($placement->student->user_id, get_class($log), $log->id);

            // In-app Notification
            \App\Models\Notification::create([
                'user_id' => $placement->student->user_id,
                'title' => '⚠️ Logbook PKL Perlu Direvisi',
                'message' => 'Logbook PKL tanggal ' . \Carbon\Carbon::parse($log->log_date)->format('d/m/Y') . ' diminta revisi oleh Mentor Industri (' . $placement->mentor_name . '). Catatan: ' . $validated['mentor_notes'],
                'type' => 'warning',
                'related_model' => 'PklLog',
                'related_id' => $log->id,
            ]);

            // WhatsApp Notification
            try {
                if ($placement->student->phone) {
                    $waService = app(\App\Services\WhatsAppService::class);
                    if ($waService && $waService->isEnabled()) {
                        $msg = "Halo {$placement->student->full_name}, logbook PKL Anda untuk tanggal " . \Carbon\Carbon::parse($log->log_date)->format('d/m/Y') . " perlu direvisi oleh Mentor DUDI ({$placement->mentor_name}).\n\n*Catatan Revisi:* {$validated['mentor_notes']}\n\nSilakan segera perbaiki melalui PembdaHUB: " . url('/m/pkl');
                        $waService->sendMessage($placement->student->phone, $msg);
                    }
                }
            } catch (\Throwable $e) {
                \Log::warning('Gagal kirim notifikasi WA revisi PKL: ' . $e->getMessage());
            }
        }

        return redirect()->route('mentor.pkl.portal', $token)->with('success', 'Catatan revisi logbook berhasil dikirim ke siswa.');
    }

    public function submitGrade($token, Request $request)
    {
        $placement = $this->getPlacement($token);

        $validated = $request->validate([
            'score_discipline' => 'required|integer|between:0,100',
            'score_teamwork' => 'required|integer|between:0,100',
            'score_technical' => 'required|integer|between:0,100',
            'score_safety' => 'required|integer|between:0,100',
            'notes' => 'nullable|string|max:1000',
        ]);

        $average = ($validated['score_discipline'] + $validated['score_teamwork'] + $validated['score_technical'] + $validated['score_safety']) / 4;

        $grade = PklGrade::updateOrCreate(
            ['pkl_placement_id' => $placement->id],
            [
                'score_discipline' => $validated['score_discipline'],
                'score_teamwork' => $validated['score_teamwork'],
                'score_technical' => $validated['score_technical'],
                'score_safety' => $validated['score_safety'],
                'score_average' => $average,
                'notes' => $validated['notes'],
                'submitted_at' => now(),
            ]
        );

        // Award student (+100 points) for completing PKL
        if ($placement->student && $placement->student->user_id) {
            \App\Models\ReputationLog::log(
                $placement->student->user_id,
                100,
                'pkl_completed',
                'Menyelesaikan PKL dengan Nilai Rata-rata: ' . number_format($average, 1),
                $grade
            );
        }

        // Award teacher (+50 points) for supervising PKL
        if ($placement->teacher && $placement->teacher->user_id) {
            \App\Models\ReputationLog::log(
                $placement->teacher->user_id,
                50,
                'pkl_monitoring',
                'Siswa bimbingan PKL (' . $placement->student->full_name . ') telah selesai dinilai',
                $grade
            );
        }

        return redirect()->route('mentor.pkl.portal', $token)->with('success', 'Penilaian akhir PKL berhasil disimpan.');
    }
}
