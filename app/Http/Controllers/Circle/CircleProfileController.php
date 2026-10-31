<?php

namespace App\Http\Controllers\Circle;

use App\Http\Controllers\Controller;
use App\Models\CircleProfile;
use App\Models\CircleProfileVisitor;
use App\Models\CircleProfileGuestbook;
use App\Models\CirclePost;
use App\Models\CircleReaction;
use App\Models\CircleComment;
use App\Models\CircleSkillEndorsement;
use App\Models\Notification;
use App\Models\User;
use App\Models\FinalProject;
use App\Models\PklLog;
use App\Models\StudentCounselingRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        // Jejak Kunjungan Profil ("Siapa yang Baru Mampir?")
        if (Auth::check() && Auth::id() !== $user->id) {
            $visitorId = Auth::id();
            $visitSessionKey = 'visited_profile_' . $profile->id . '_by_' . $visitorId;
            if (!session()->has($visitSessionKey)) {
                $visitorRecord = CircleProfileVisitor::firstOrNew([
                    'circle_profile_id' => $profile->id,
                    'visitor_user_id' => $visitorId,
                ]);

                $visitorRecord->visit_count = ($visitorRecord->visit_count ?? 0) + 1;
                $visitorRecord->last_visited_at = now();
                $visitorRecord->save();

                session()->put($visitSessionKey, true);

                // Kirim notifikasi ramah ke pemilik profil jika belum ada notif dari visitor ini dalam 6 jam terakhir
                $recentNotif = Notification::where('user_id', $user->id)
                    ->where('type', 'circle_visitor')
                    ->where('related_id', $visitorId)
                    ->where('created_at', '>=', now()->subHours(6))
                    ->first();

                if (!$recentNotif) {
                    $visitorUser = Auth::user();
                    $visitorNick = $visitorUser->circleProfile->nickname ?? explode(' ', $visitorUser->name)[0];
                    Notification::create([
                        'user_id' => $user->id,
                        'school_id' => $visitorUser->school_id,
                        'type' => 'circle_visitor',
                        'title' => "👀 {$visitorNick} baru saja mampir!",
                        'message' => "{$visitorUser->name} mengunjungi dinding profilmu. Yuk mampir balik!",
                        'related_model' => 'User',
                        'related_id' => $visitorId,
                        'is_read' => false,
                    ]);
                }
            }
        }

        // Ambil jejak pengunjung terakhir (Recent Visitors)
        $recentVisitors = CircleProfileVisitor::with([
            'visitor.circleProfile',
            'visitor.school',
            'visitor.student.classroom',
            'visitor.teacher',
        ])
            ->where('circle_profile_id', $profile->id)
            ->latest('last_visited_at')
            ->take(12)
            ->get();

        // Ambil catatan buku tamu & stiker dinding (Guestbook)
        $guestbookEntries = CircleProfileGuestbook::with([
            'sender.circleProfile',
            'sender.school',
            'sender.student.classroom',
            'sender.teacher',
        ])
            ->where('circle_profile_id', $profile->id)
            ->orderByDesc('is_pinned')
            ->latest()
            ->get();

        // Ambil postingan Tab Dinding (Wall / Nongki)
        $wallPosts = CirclePost::with(['media', 'reactions', 'comments.replies', 'user', 'poll.options', 'poll.votes'])
            ->where('user_id', $user->id)
            ->where('tab_category', 'wall')
            ->where('post_type', '!=', 'story')
            ->active()
            ->orderByDesc('is_pinned')
            ->latest()
            ->get();

        // Ambil postingan Tab Karya & Portofolio
        $portfolioPosts = CirclePost::with(['media', 'reactions', 'skillEndorsements.teacher', 'user'])
            ->where('user_id', $user->id)
            ->where('tab_category', 'portfolio')
            ->active()
            ->orderByDesc('is_pinned')
            ->latest()
            ->get();

        // Ambil postingan Tab Galeri Media Pribadi (Foto, Video, Audio)
        $galleryPosts = CirclePost::with(['media', 'reactions', 'comments', 'user'])
            ->where('user_id', $user->id)
            ->where(function ($q) {
                $q->where('tab_category', 'gallery')
                  ->orWhere('is_gallery', true);
            })
            ->active()
            ->orderByDesc('is_pinned')
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
                $approvedPklLogs = PklLog::whereHas('placement', function($q) use ($studentId) {
                        $q->where('student_id', $studentId);
                    })
                    ->where('status', 'approved')
                    ->whereNotNull('photo')
                    ->latest('log_date')
                    ->take(6)
                    ->get();
            }

            // Prestasi & Penghargaan BK
            if (class_exists(StudentCounselingRecord::class)) {
                $achievements = StudentCounselingRecord::where('student_id', $studentId)
                    ->where('record_type', 'penghargaan')
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
            'galleryPosts',
            'activeStories',
            'finalProjects',
            'approvedPklLogs',
            'achievements',
            'skillEndorsements',
            'recentVisitors',
            'guestbookEntries'
        ));
    }
}
