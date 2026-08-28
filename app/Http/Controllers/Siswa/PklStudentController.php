<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\PklPlacement;
use App\Models\PklLog;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PklStudentController extends Controller
{
    private function getStudent(): Student
    {
        return Student::where('user_id', Auth::id())->firstOrFail();
    }

    public function index()
    {
        $student = $this->getStudent();
        
        $placement = PklPlacement::where('student_id', $student->id)
            ->where('status', 'active')
            ->with(['teacher', 'logs' => function($q) {
                $q->orderByDesc('log_date');
            }, 'grade'])
            ->first();

        return view('siswa.pkl.index', compact('student', 'placement'));
    }

    public function storeLog(Request $request)
    {
        $student = $this->getStudent();
        
        $placement = PklPlacement::where('student_id', $student->id)
            ->where('status', 'active')
            ->firstOrFail();

        $validated = $request->validate([
            'log_date' => 'required|date|before_or_equal:today',
            'activity' => 'required|string|min:50',
            'photo' => 'nullable|image|max:5120', // max 5MB
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ], [
            'activity.min' => 'Deskripsi kegiatan terlalu singkat. Minimal 50 karakter agar lebih detail dan bermutu.',
            'activity.required' => 'Deskripsi kegiatan wajib diisi.'
        ]);

        // Check if log for this date already exists
        $existingLog = PklLog::where('pkl_placement_id', $placement->id)
            ->where('log_date', $validated['log_date'])
            ->first();

        if ($existingLog) {
            if ($existingLog->status === 'rejected') {
                // Update / Revisi logbook yang ditolak
                $photoPath = $existingLog->photo;
                if ($request->hasFile('photo')) {
                    if ($photoPath && \Storage::disk('public')->exists($photoPath)) {
                        \Storage::disk('public')->delete($photoPath);
                    }
                    $photoPath = $request->file('photo')->store('pkl_proofs', 'public');
                }

                $existingLog->update([
                    'activity' => $validated['activity'],
                    'photo' => $photoPath,
                    'latitude' => $validated['latitude'] ?? $existingLog->latitude,
                    'longitude' => $validated['longitude'] ?? $existingLog->longitude,
                    'status' => 'submitted',
                ]);

                // Notifikasi ke guru pembimbing
                if ($placement->teacher && $placement->teacher->user_id) {
                    \App\Models\Notification::create([
                        'user_id' => $placement->teacher->user_id,
                        'school_id' => $student->school_id,
                        'title' => '📝 Revisi Jurnal PKL Dikirim',
                        'message' => 'Siswa ' . $student->full_name . ' telah mengirimkan revisi logbook PKL untuk tanggal ' . \Carbon\Carbon::parse($validated['log_date'])->format('d/m/Y') . '.',
                        'type' => 'info',
                        'related_model' => 'PklLog',
                        'related_id' => $existingLog->id,
                    ]);
                }

                return redirect()->route('siswa.pkl.index')->with('success', 'Revisi logbook harian berhasil dikirim dan menunggu verifikasi pembimbing.');
            }

            return redirect()->back()->with('error', 'Anda sudah mengisi logbook untuk tanggal ini dan sedang diproses/telah disetujui.');
        }

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('pkl_proofs', 'public');
        }

        PklLog::create([
            'pkl_placement_id' => $placement->id,
            'log_date' => $validated['log_date'],
            'activity' => $validated['activity'],
            'photo' => $photoPath,
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'status' => 'submitted',
        ]);

        return redirect()->route('siswa.pkl.index')->with('success', 'Logbook harian berhasil dikirim.');
    }
}
