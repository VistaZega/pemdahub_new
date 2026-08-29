<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\PklPlacement;
use App\Models\PklMonitoring;
use App\Models\Teacher;
use App\Models\Dudi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class PklMonitoringController extends Controller
{
    private function getTeacher(): Teacher
    {
        return Teacher::where('user_id', Auth::id())->firstOrFail();
    }

    public function index()
    {
        $teacher = $this->getTeacher();
        
        $activeYear = \App\Models\AcademicYear::where('is_active', true)->first();
        
        // Group placements by DUDI and Shift
        $query = PklPlacement::with('dudi')
            ->where('teacher_id', $teacher->id);
            
        if ($activeYear) {
            $query->where('academic_year_id', $activeYear->id);
        }
            
        $groups = $query->select('dudi_id', 'shift', DB::raw('count(*) as total_students'), DB::raw('MAX(is_perangkat_ready) as is_perangkat_ready'))
            ->groupBy('dudi_id', 'shift')
            ->get();

        $pklHours = $teacher->getPklSupervisorHours($activeYear?->id);

        return view('guru.pkl_monitorings.index', compact('teacher', 'groups', 'activeYear', 'pklHours'));
    }

    public function show($dudi_id, $shift = null)
    {
        if ($shift === 'null') $shift = null;
        
        $teacher = $this->getTeacher();
        $dudi = Dudi::findOrFail($dudi_id);
        $activeYear = \App\Models\AcademicYear::where('is_active', true)->first();
        
        // Verify teacher owns these placements
        $placementsQuery = PklPlacement::with(['student.user', 'logs' => function($q) {
                $q->orderByDesc('log_date')->take(5); // Show latest 5 logs per student for quick preview
            }])
            ->where('teacher_id', $teacher->id)
            ->where('dudi_id', $dudi_id)
            ->where('shift', $shift);
            
        if ($activeYear) {
            $placementsQuery->where('academic_year_id', $activeYear->id);
        }
            
        $placements = $placementsQuery->get();

        if ($placements->isEmpty()) {
            abort(403, 'Anda bukan pembimbing di DUDI ini.');
        }

        $isPerangkatReady = $placements->first()->is_perangkat_ready;
        $perangkatFilePath = $placements->first()->perangkat_file_path;

        $monitorings = PklMonitoring::where('teacher_id', $teacher->id)
            ->where('dudi_id', $dudi_id)
            ->where('shift', $shift)
            ->orderByDesc('monitoring_date')
            ->get();

        return view('guru.pkl_monitorings.show', compact('teacher', 'dudi', 'shift', 'placements', 'monitorings', 'isPerangkatReady', 'perangkatFilePath'));
    }

    public function updatePerangkat(Request $request, $dudi_id, $shift = null)
    {
        if ($shift === 'null') $shift = null;
        $teacher = $this->getTeacher();
        
        $request->validate([
            'perangkat_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $path = $request->file('perangkat_file')->store('pkl_perangkat', 'public');

        PklPlacement::where('teacher_id', $teacher->id)
            ->where('dudi_id', $dudi_id)
            ->where('shift', $shift)
            ->update([
                'is_perangkat_ready' => true,
                'perangkat_file_path' => $path
            ]);

        return back()->with('success', 'Dokumen Perangkat PKL berhasil diunggah.');
    }

    public function store(Request $request, $dudi_id, $shift = null)
    {
        if ($shift === 'null') $shift = null;
        $teacher = $this->getTeacher();
        
        $validated = $request->validate([
            'monitoring_date' => 'required|date',
            'evaluation_student' => 'required|string|min:20',
            'feedback_dudi' => 'required|string|min:20',
            'guidance_action' => 'required|string|min:20',
            'assignment_letter' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'photo' => 'required|image|mimes:jpg,jpeg,png|max:5120',
        ], [
            'evaluation_student.required' => 'Evaluasi kinerja & kedisiplinan siswa wajib diisi.',
            'evaluation_student.min' => 'Evaluasi siswa terlalu singkat (minimal 20 karakter). Mohon uraikan secara jelas dan faktual.',
            'feedback_dudi.required' => 'Feedback dari instruktur DUDI wajib diisi.',
            'feedback_dudi.min' => 'Feedback DUDI terlalu singkat (minimal 20 karakter). Mohon tuliskan hasil koordinasi dengan pihak industri.',
            'guidance_action.required' => 'Arahan bimbingan & tindak lanjut guru wajib diisi.',
            'guidance_action.min' => 'Arahan bimbingan terlalu singkat (minimal 20 karakter).',
            'assignment_letter.required' => 'Surat tugas monitoring bertandatangan DUDI wajib diunggah.',
            'photo.required' => 'Foto bukti kunjungan langsung di DUDI wajib diunggah.',
        ]);

        $letterPath = $request->file('assignment_letter')->store('pkl/monitoring_letters', 'public');
        $photoPath = $request->file('photo')->store('pkl/monitoring_photos', 'public');

        $formattedNotes = "1. Evaluasi Kinerja & Kedisiplinan Siswa:\n" . trim($validated['evaluation_student']) . "\n\n" .
                          "2. Feedback dari Instruktur DUDI:\n" . trim($validated['feedback_dudi']) . "\n\n" .
                          "3. Arahan Bimbingan Guru & Solusi Tindak Lanjut:\n" . trim($validated['guidance_action']);

        PklMonitoring::create([
            'teacher_id' => $teacher->id,
            'dudi_id' => $dudi_id,
            'shift' => $shift,
            'monitoring_date' => $request->monitoring_date,
            'assignment_letter_path' => $letterPath,
            'photo_path' => $photoPath,
            'notes' => $formattedNotes,
            'status' => 'submitted',
        ]);

        return back()->with('success', 'Laporan monitoring mingguan resmi berhasil dikirim dan tercatat di sistem Yayasan & Sekolah.');
    }
}
