<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlumniDashboardController extends Controller
{
    /**
     * Tampilkan halaman dashboard alumni.
     */
    public function index()
    {
        $user = Auth::user();
        $alumni = $user->alumniDirectory;

        if (!$alumni) {
            // High-priority fallback for Admin / Staff preview
            $roleLabel = ucfirst(str_replace('_', ' ', $user->role));
            $alumni = (object) [
                'full_name' => $user->name,
                'photo_url' => 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=312e81&color=fff',
                'graduation_year' => $roleLabel,
                'school' => (object) ['name' => 'Yayasan Perguruan Pembda Nias'],
                'occupation' => 'Pengurus / Pengelola',
                'company_name' => 'Yayasan Perguruan Pembda Nias',
                'is_approved' => true,
                'school_id' => $user->school_id ?? 1,
            ];
        }

        // Cek status Tracer Study (ambil AlumniProfile yang terhubung dengan email user)
        $alumniProfile = \App\Models\AlumniProfile::where('email', $user->email)->first();
        $hasFilledTracer = false;
        if ($alumniProfile) {
            $hasFilledTracer = \App\Models\TracerStudy::where('alumni_profile_id', $alumniProfile->id)->exists();
        }

        // Ambil lowongan kerja terbaru (maksimal 3)
        $latestJobs = \App\Models\JobPosting::where('is_active', true)->latest()->take(3)->get();

        // Ambil obrolan terbaru di Forum Alumni (khusus unit sekolah)
        $schoolId = $alumni->school_id ?? null;
        $latestThreads = \App\Models\AlumniForum::with(['user'])
                            ->where('school_id', $schoolId)
                            ->latest()
                            ->take(3)
                            ->get();

        return view('alumni.dashboard', compact('user', 'alumni', 'hasFilledTracer', 'latestJobs', 'latestThreads'));
    }
}
