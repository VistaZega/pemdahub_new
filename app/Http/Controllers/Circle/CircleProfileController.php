<?php

namespace App\Http\Controllers\Circle;

use App\Http\Controllers\Controller;
use App\Models\CircleProfile;
use App\Models\CirclePost;
use App\Models\CircleReaction;
use App\Models\CircleComment;
use App\Models\CircleSkillEndorsement;
use App\Models\User;
use App\Models\FinalProject;
use App\Models\PklLog;
use App\Models\StudentCounselingRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CircleProfileController extends Controller
{
    /**
     * Tampilkan Halaman Profil Publik (@username)
     */
    public function show(Request $request, string $username)
    {
        // Bersihkan tanda '@' jika ada di URL
        $cleanUsername = ltrim(trim($username), '@');

        // 1. Cari berdasarkan username di circle_profiles
        $profile = CircleProfile::with('user.student.classroom', 'user.student.major', 'user.teacher', 'user.school')
            ->where('username', $cleanUsername)
            ->first();

        // 2. Jika tidak ditemukan, cari berdasarkan username di tabel users
        if (!$profile) {
            $user = User::with(['student.classroom', 'student.major', 'teacher', 'school'])
                ->where('username', $cleanUsername)
                ->first();

            // Atau cari fallback case-insensitive
            if (!$user) {
                $user = User::whereRaw('LOWER(username) = ?', [strtolower($cleanUsername)])->first();
            }

            if ($user) {
                $profile = CircleProfile::getOrCreateForUser($user);
            }
        }

        // 3. Jika masih tidak ditemukan, cari berdasarkan nama slug siswa
        if (!$profile) {
            $user = User::whereHas('student', function ($q) use ($cleanUsername) {
                $q->whereRaw('LOWER(REPLACE(full_name, " ", "-")) = ?', [strtolower($cleanUsername)])
                  ->orWhere('nisn', $cleanUsername);
            })->first();

            if ($user) {
                $profile = CircleProfile::getOrCreateForUser($user);
            }
        }

        if (!$profile) {
            abort(404, 'Profil PembdaCIRCLE tidak ditemukan.');
        }

        $user = $profile->user;

        // Throttled view count increment (1 view per session)
        $viewSessionKey = 'viewed_circle_profile_' . $profile->id;
        if (!session()->has($viewSessionKey)) {
            $profile->increment('views_count');
            session()->put($viewSessionKey, true);
        }

        // Ambil postingan Tab Dinding (Wall / Nongki)
        $wallPosts = CirclePost::with(['media', 'reactions', 'comments.replies', 'user'])
            ->where('user_id', $user->id)
            ->where('tab_category', 'wall')
            ->where('post_type', '!=', 'story')
            ->active()
            ->latest()
            ->get();

        // Ambil postingan Tab Karya & Portofolio
        $portfolioPosts = CirclePost::with(['media', 'reactions', 'skillEndorsements.teacher', 'user'])
            ->where('user_id', $user->id)
            ->where('tab_category', 'portfolio')
            ->active()
            ->latest()
            ->get();

        // Ambil Stories 24 Jam yang masih aktif
        $activeStories = CirclePost::with('media')
            ->where('user_id', $user->id)
            ->where('post_type', 'story')
            ->where('expires_at', '>', now())
            ->latest()
            ->get();

        // Dapatkan data akademik legacy yang bisa dijadikan etalase (Final Project, PKL, Prestasi)
        $finalProjects = collect();
        $approvedPklLogs = collect();
        $achievements = collect();

        if ($user->student) {
            $studentId = $user->student->id;
            
            // Tugas Akhir / Penelitian Ilmiah
            if (class_exists(FinalProject::class)) {
                $finalProjects = FinalProject::where('student_id', $studentId)
                    ->whereIn('status', ['submitted', 'approved', 'graded'])
                    ->latest()
                    ->get();
            }

            // Logbook PKL yang memiliki foto dokumentasi
            if (class_exists(PklLog::class)) {
                $approvedPklLogs = PklLog::where('student_id', $studentId)
                    ->where('status', 'approved')
                    ->whereNotNull('documentation_file')
                    ->latest('activity_date')
                    ->take(6)
                    ->get();
            }

            // Prestasi & Penghargaan BK
            if (class_exists(StudentCounselingRecord::class)) {
                $achievements = StudentCounselingRecord::where('student_id', $studentId)
                    ->where('type', 'penghargaan')
                    ->latest('incident_date')
                    ->get();
            }
        }

        // Endorsement Keahlian dari Guru
        $skillEndorsements = CircleSkillEndorsement::with('teacher')
            ->where('student_user_id', $user->id)
            ->latest()
            ->get();

        return view('circle.profile', compact(
            'profile',
            'user',
            'wallPosts',
            'portfolioPosts',
            'activeStories',
            'finalProjects',
            'approvedPklLogs',
            'achievements',
            'skillEndorsements'
        ));
    }
}
