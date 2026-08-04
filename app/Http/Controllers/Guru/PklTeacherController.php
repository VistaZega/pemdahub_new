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
        
        $placements = PklPlacement::where('teacher_id', $teacher->id)
            ->with(['student', 'logs', 'grade'])
            ->orderByDesc('id')
            ->get();

        return view('guru.pkl.index', compact('teacher', 'placements'));
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
}
