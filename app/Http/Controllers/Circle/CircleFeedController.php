<?php

namespace App\Http\Controllers\Circle;

use App\Http\Controllers\Controller;
use App\Models\CircleCommunity;
use App\Models\CircleProfile;
use App\Models\CirclePost;
use App\Models\FinalProject;
use App\Models\LmsSubmission;
use App\Models\School;
use App\Services\CircleCommunityService;
use App\Services\CirclePointService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class CircleFeedController extends Controller
{
    /**
     * Beranda Utama PembdaCIRCLE (FYP, Explore & Ruang Grup Komunitas)
     */
    public function index(Request $request)
    {
        CirclePointService::ensureExistingCircleInteractionsSynced();

        $communityService = app(CircleCommunityService::class);
        if (Auth::check()) {
            $communityService->syncUserCommunities(Auth::user());
        } else {
            $communityService->ensureEcosystemCommunities();
        }

        $schoolFilter = $request->query('school');
        $tabFilter = $request->query('tab', 'all'); // 'all', 'portfolio', 'wall', 'reels', 'gallery', 'live', 'groups'
        $groupTypeFilter = $request->query('group_type', 'all'); // 'all', 'class', 'sanggar', 'extracurricular', 'vocation', 'cohort', 'alumni', 'teachers'
        $communityParam = $request->query('community');
        $search = trim($request->query('q', ''));

        // Cek apakah pengguna sedang membuka Lounge Grup / Komunitas tertentu
        $activeCommunity = null;
        if ($communityParam && Schema::hasTable('circle_communities')) {
            $activeCommunity = CircleCommunity::with([
                'school',
                'leader.circleProfile',
                'classroom.homeroomTeacher',
                'extracurricular.advisor',
                'extracurricular.leader',
                'extracurricular.secretary',
                'extracurricular.treasurer',
                'members.user.circleProfile',
                'members.user.school',
                'members.user.student.classroom',
                'members.user.teacher',
            ])
                ->where(function ($q) use ($communityParam) {
                    if (is_numeric($communityParam)) {
                        $q->where('id', (int) $communityParam);
                    } else {
                        $q->where('slug', $communityParam);
                    }
                })
                ->first();
        }

        $query = CirclePost::with([
            'user.circleProfile',
            'user.school',
            'user.reputation',
            'user.student.classroom',
            'user.student.major',
            'user.teacher',
            'user.circleCommunities',
            'community.school',
            'media',
            'reactions',
            'comments',
            'poll.options',
            'poll.votes',
        ])
            ->active()
            ->meaningful()
            ->publicVisible();

        // Filter berdasarkan Grup / Komunitas aktif jika sedang dibuka
        if ($activeCommunity) {
            $query->where('community_id', $activeCommunity->id);
        } elseif ($groupTypeFilter && $groupTypeFilter !== 'all' && $tabFilter !== 'groups') {
            $query->whereHas('community', function ($cq) use ($groupTypeFilter) {
                $cq->where('type', $groupTypeFilter);
            });
        }

        // Filter kata kunci pencarian gelora (Judul, Isi, Lokasi, Nama Siswa, Keahlian, Nama Grup)
        if ($search !== '') {
            $query->where(function ($sq) use ($search) {
                $sq->where('title', 'like', "%{$search}%")
                   ->orWhere('content', 'like', "%{$search}%")
                   ->orWhere('location_tag', 'like', "%{$search}%")
                   ->orWhereHas('user', function ($uq) use ($search) {
                       $uq->where('name', 'like', "%{$search}%")
                          ->orWhere('username', 'like', "%{$search}%")
                          ->orWhereHas('circleProfile', function ($pq) use ($search) {
                              $pq->where('nickname', 'like', "%{$search}%")
                                 ->orWhere('bio', 'like', "%{$search}%");
                          });
                   })
                   ->orWhereHas('community', function ($cq) use ($search) {
                       $cq->where('name', 'like', "%{$search}%");
                   })
                   ->orWhereHas('skillEndorsements', function ($eq) use ($search) {
                       $eq->where('skill_name', 'like', "%{$search}%");
                   });
            });
        }

        // Filter unit sekolah jika dipilih
        if ($schoolFilter && $schoolFilter !== 'all') {
            if (is_numeric($schoolFilter)) {
                $query->where(function ($sq) use ($schoolFilter) {
                    $sq->where('school_id', $schoolFilter)
                       ->orWhereHas('user', function ($uq) use ($schoolFilter) {
                           $uq->where('school_id', $schoolFilter);
                       });
                });
            } else {
                $query->where(function ($sq) use ($schoolFilter) {
                    $sq->whereHas('school', function ($q) use ($schoolFilter) {
                        $q->where('name', 'like', "%{$schoolFilter}%")
                          ->orWhere('type', 'like', "%{$schoolFilter}%");
                    })->orWhereHas('user.school', function ($q) use ($schoolFilter) {
                        $q->where('name', 'like', "%{$schoolFilter}%")
                          ->orWhere('type', 'like', "%{$schoolFilter}%");
                    });
                });
            }
        }

        // Filter kategori tab
        if ($tabFilter === 'portfolio') {
            $query->where('tab_category', 'portfolio');
        } elseif ($tabFilter === 'wall') {
            $query->where('tab_category', 'wall');
        } elseif ($tabFilter === 'reels') {
            $query->where('post_type', 'reel');
        } elseif ($tabFilter === 'gallery') {
            $query->where(function ($q) {
                $q->where('tab_category', 'gallery')
                  ->orWhere('is_gallery', true);
            });
        } elseif ($tabFilter === 'live') {
            $query->where(function ($q) {
                $q->whereIn('post_type', ['live', 'report']);
                if (Schema::hasColumn('circle_posts', 'is_live')) {
                    $q->orWhere('is_live', true);
                }
            });
        } elseif ($tabFilter === 'interactive' || $tabFilter === 'challenge') {
            if (Schema::hasColumn('circle_posts', 'interactive_type')) {
                $query->where('interactive_type', '!=', 'none');
            }
        }

        $sharedPost = null;
        if ($request->filled('post') && is_numeric($request->query('post'))) {
            $sharedPostId = (int) $request->query('post');
            $sharedPost = CirclePost::with(['user.circleProfile', 'user.school', 'school', 'community', 'media'])->find($sharedPostId);
            $query->orderByRaw('CASE WHEN id = ? THEN 0 ELSE 1 END', [$sharedPostId]);
        }

        if ($activeCommunity) {
            $query->orderByDesc('is_pinned');
        }

        if (Schema::hasColumn('circle_posts', 'is_live')) {
            $query->orderByRaw("CASE WHEN is_live = 1 AND live_status = 'live' THEN 0 ELSE 1 END");
        }

        $posts = $query->latest()->paginate(12)->withQueryString();

        // ── DATA GRUP & KOMUNITAS PEMBDACIRCLE (Kelas, Sanggar, Ekskul, Rumpun SMK, Angkatan, Alumni, Guru) ──
        $myCommunities = collect();
        $allCommunities = collect();
        $communityCountsByType = [
            'all' => 0,
            'class' => 0,
            'sanggar' => 0,
            'extracurricular' => 0,
            'vocation' => 0,
            'cohort' => 0,
            'alumni' => 0,
            'teachers' => 0,
        ];

        if (Schema::hasTable('circle_communities')) {
            if (Auth::check()) {
                $myCommunities = Auth::user()->circleCommunities()
                    ->with('school')
                    ->orderByRaw("CASE type 
                        WHEN 'class' THEN 1 
                        WHEN 'sanggar' THEN 2 
                        WHEN 'extracurricular' THEN 3 
                        WHEN 'vocation' THEN 4 
                        WHEN 'cohort' THEN 5 
                        WHEN 'alumni' THEN 6 
                        WHEN 'teachers' THEN 7 
                        ELSE 8 END")
                    ->get();
            }

            // Hitung total grup per kategori
            $typeCountQuery = CircleCommunity::query();
            if (Schema::hasColumn('circle_communities', 'is_private')) {
                $typeCountQuery->where('is_private', false);
            }
            $rawTypeCounts = $typeCountQuery
                ->selectRaw('type, COUNT(*) as total')
                ->groupBy('type')
                ->pluck('total', 'type');

            foreach ($rawTypeCounts as $tKey => $tVal) {
                $communityCountsByType[$tKey] = (int) $tVal;
            }
            $communityCountsByType['all'] = (int) $rawTypeCounts->sum();

            // Ambil direktori grup sesuai filter
            $commQuery = CircleCommunity::with(['school', 'leader', 'extracurricular.advisor', 'classroom.homeroomTeacher']);
            if (Schema::hasColumn('circle_communities', 'is_private')) {
                $commQuery->where('is_private', false);
            }

            if ($groupTypeFilter && $groupTypeFilter !== 'all') {
                $commQuery->where('type', $groupTypeFilter);
            }

            if ($schoolFilter && $schoolFilter !== 'all') {
                if (is_numeric($schoolFilter)) {
                    $commQuery->where(function ($cq) use ($schoolFilter) {
                        $cq->where('school_id', $schoolFilter)->orWhereNull('school_id');
                    });
                } else {
                    $commQuery->where(function ($cq) use ($schoolFilter) {
                        $cq->whereHas('school', function ($sq) use ($schoolFilter) {
                            $sq->where('name', 'like', "%{$schoolFilter}%")
                               ->orWhere('type', 'like', "%{$schoolFilter}%");
                        })->orWhereNull('school_id');
                    });
                }
            }

            if ($search !== '') {
                $commQuery->where(function ($cq) use ($search) {
                    $cq->where('name', 'like', "%{$search}%")
                       ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $allCommunities = $commQuery
                ->orderByRaw("CASE type 
                    WHEN 'sanggar' THEN 1 
                    WHEN 'extracurricular' THEN 2 
                    WHEN 'vocation' THEN 3 
                    WHEN 'class' THEN 4 
                    WHEN 'cohort' THEN 5 
                    WHEN 'alumni' THEN 6 
                    WHEN 'teachers' THEN 7 
                    ELSE 8 END")
                ->orderByDesc('members_count')
                ->orderBy('name')
                ->get();
        }

        // ── KARYA AKADEMIK SISWA YANG BISA DIKAITKAN KE POSTINGAN (Verified Reference) ──
        $userAcademicWorks = collect();
        if (Auth::check() && Auth::user()->student) {
            $studentId = Auth::user()->student->id;

            if (class_exists(FinalProject::class) && Schema::hasTable('final_projects')) {
                $fps = FinalProject::where('student_id', $studentId)->latest()->take(10)->get();
                foreach ($fps as $fp) {
                    $userAcademicWorks->push([
                        'ref_value' => 'final_project:' . $fp->id,
                        'badge' => $fp->type === 'penelitian_ilmiah' ? '🔬 Penelitian SMA' : '🛠️ Project Akhir SMK',
                        'title' => $fp->title,
                        'score' => $fp->final_score ? round($fp->final_score) : null,
                    ]);
                }
            }

            if (class_exists(LmsSubmission::class) && Schema::hasTable('lms_submissions')) {
                $subs = LmsSubmission::with('assignment.module.course')
                    ->where('student_id', $studentId)
                    ->latest('submitted_at')
                    ->take(15)
                    ->get();
                foreach ($subs as $sub) {
                    $courseTitle = $sub->assignment?->module?->course?->course_name ?? 'LMS';
                    $userAcademicWorks->push([
                        'ref_value' => 'lms_submission:' . $sub->id,
                        'badge' => '📚 Tugas LMS (' . $courseTitle . ')',
                        'title' => $sub->assignment?->title ?? 'Tugas Terkumpul',
                        'score' => $sub->score,
                    ]);
                }
            }
        }

        // Ambil Stories 24 Jam terbaru dari semua siswa/guru aktif
        $rawStories = CirclePost::with(['user.circleProfile', 'user.school', 'school', 'media'])
            ->where('post_type', 'story')
            ->where('expires_at', '>', now())
            ->latest()
            ->take(25)
            ->get();

        // Bersihkan duplikasi story kembar yang terkirim tidak sengaja dalam jeda waktu 2 menit
        $activeStories = collect();
        foreach ($rawStories as $st) {
            $isDuplicate = $activeStories->contains(function ($existing) use ($st) {
                return $existing->user_id === $st->user_id
                    && $existing->content === $st->content
                    && abs($existing->created_at->diffInSeconds($st->created_at)) < 120;
            });
            if (!$isDuplicate) {
                $activeStories->push($st);
            }
        }
        $activeStories = $activeStories->take(15);

        // 5 Profil Paling Aktif / Trending
        $trendingProfiles = CircleProfile::with('user.school')
            ->where('is_public', true)
            ->orderBy('views_count', 'desc')
            ->take(6)
            ->get();

        // Profil Siswa/Guru yang cocok dengan kata kunci pencarian
        $matchedProfiles = collect();
        if ($search !== '') {
            $matchedProfiles = CircleProfile::with('user.school')
                ->where('is_public', true)
                ->where(function ($pq) use ($search) {
                    $pq->where('nickname', 'like', "%{$search}%")
                       ->orWhere('username', 'like', "%{$search}%")
                       ->orWhere('bio', 'like', "%{$search}%")
                       ->orWhereHas('user', function ($uq) use ($search) {
                           $uq->where('name', 'like', "%{$search}%");
                       });
                })
                ->take(4)
                ->get();
        }

        // 3 Unit Sekolah Aktif
        $schools = School::schoolsOnly()->get();

        // 🎯 Tantangan Gelora Hari Ini (Daily Viral Prompt)
        $dailyPrompts = [
            'Sunday'    => ['emoji' => '🔋', 'title' => 'Minggu Recharging', 'desc' => 'Foto persiapan apa yang sudah beres buat sekolah besok? Buku, seragam, atau semangat baru?'],
            'Monday'    => ['emoji' => '🌅', 'title' => 'Senin Menyala', 'desc' => 'Apa target terbesarmu yang ingin kamu capai di kelas atau bengkel/lab minggu ini?'],
            'Tuesday'   => ['emoji' => '🛠️', 'title' => 'Selasa Ngulik', 'desc' => 'Spill sudut bengkel, meja praktek, atau layar komputermu sekarang!'],
            'Wednesday' => ['emoji' => '☕', 'title' => 'Rabu Bestie', 'desc' => 'Momen nongki santai di kantin atau obrolan terlucu bareng kawan satu meja hari ini!'],
            'Thursday'  => ['emoji' => '💡', 'title' => 'Kamis Ide', 'desc' => 'Satu ide atau hal baru yang lagi penasaran banget pengen kamu ciptakan atau pelajari!'],
            'Friday'    => ['emoji' => '✨', 'title' => 'Jumat Penuh Berkah', 'desc' => 'Nasihat, kutipan keren, atau ucapan terima kasih buat bapak/ibu guru atau bestie-mu hari ini!'],
            'Saturday'  => ['emoji' => '⚽', 'title' => 'Sabtu Kreasi & Ekskul', 'desc' => 'Pamer momen kegiatan ekskul, sanggar, olahraga, atau santai akhir pekanmu!'],
        ];
        $todayDay = now()->format('l');
        $todayChallenge = $dailyPrompts[$todayDay] ?? $dailyPrompts['Friday'];

        // 👑 Bintang Gelora Minggu Ini: Top 3 Siswa & 1 Guru Paling Menyala
        $topStudents = CircleProfile::with(['user.school', 'user.reputation'])
            ->whereHas('user', function ($uq) {
                $uq->where('role', 'siswa');
            })
            ->where('is_public', true)
            ->select('circle_profiles.*')
            ->selectSub(function ($q) {
                $q->from('circle_posts')
                  ->whereColumn('circle_posts.user_id', 'circle_profiles.user_id')
                  ->selectRaw('COALESCE(SUM(likes_count), 0)');
            }, 'total_likes')
            ->selectSub(function ($q) {
                $q->from('reputations')
                  ->whereColumn('reputations.user_id', 'circle_profiles.user_id')
                  ->selectRaw('COALESCE(MAX(total_points), 0)');
            }, 'reputation_points')
            ->orderByDesc('total_likes')
            ->orderByDesc('reputation_points')
            ->orderByDesc('views_count')
            ->take(5)
            ->get();

        $topTeacher = CircleProfile::with(['user.school', 'user.reputation'])
            ->whereHas('user', function ($uq) {
                $uq->where('role', 'guru');
            })
            ->select('circle_profiles.*')
            ->selectSub(function ($q) {
                $q->from('circle_skill_endorsements')
                  ->whereColumn('circle_skill_endorsements.teacher_user_id', 'circle_profiles.user_id')
                  ->selectRaw('COUNT(*)');
            }, 'total_endorsements')
            ->selectSub(function ($q) {
                $q->from('reputations')
                  ->whereColumn('reputations.user_id', 'circle_profiles.user_id')
                  ->selectRaw('COALESCE(MAX(total_points), 0)');
            }, 'reputation_points')
            ->orderByDesc('total_endorsements')
            ->orderByDesc('reputation_points')
            ->orderByDesc('views_count')
            ->first();

        return view('circle.index', compact(
            'posts',
            'sharedPost',
            'activeStories',
            'trendingProfiles',
            'matchedProfiles',
            'schools',
            'tabFilter',
            'groupTypeFilter',
            'schoolFilter',
            'search',
            'todayChallenge',
            'topStudents',
            'topTeacher',
            'activeCommunity',
            'myCommunities',
            'allCommunities',
            'communityCountsByType',
            'userAcademicWorks'
        ));
    }
}
