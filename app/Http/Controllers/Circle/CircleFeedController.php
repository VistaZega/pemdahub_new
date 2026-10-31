<?php

namespace App\Http\Controllers\Circle;

use App\Http\Controllers\Controller;
use App\Models\CircleProfile;
use App\Models\CirclePost;
use App\Models\School;
use Illuminate\Http\Request;

class CircleFeedController extends Controller
{
    /**
     * Beranda Utama PembdaCIRCLE (FYP & Explore)
     */
    public function index(Request $request)
    {
        $schoolFilter = $request->query('school');
        $tabFilter = $request->query('tab', 'all'); // 'all', 'portfolio', 'wall', 'reels'
        $search = trim($request->query('q', ''));

        $query = CirclePost::with(['user.circleProfile', 'user.school', 'media', 'reactions', 'comments', 'poll.options', 'poll.votes'])
            ->active()
            ->publicVisible();

        // Filter kata kunci pencarian gelora (Judul, Isi, Lokasi, Nama Siswa, Keahlian)
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
                if (\Illuminate\Support\Facades\Schema::hasColumn('circle_posts', 'is_live')) {
                    $q->orWhere('is_live', true);
                }
            });
        }

        $sharedPost = null;
        if ($request->filled('post') && is_numeric($request->query('post'))) {
            $sharedPostId = (int) $request->query('post');
            $sharedPost = CirclePost::with(['user.circleProfile', 'user.school', 'school', 'media'])->find($sharedPostId);
            $query->orderByRaw('CASE WHEN id = ? THEN 0 ELSE 1 END', [$sharedPostId]);
        }

        if (\Illuminate\Support\Facades\Schema::hasColumn('circle_posts', 'is_live')) {
            $query->orderByRaw("CASE WHEN is_live = 1 AND live_status = 'live' THEN 0 ELSE 1 END");
        }

        $posts = $query->latest()->paginate(12)->withQueryString();

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
            'Saturday'  => ['emoji' => '⚽', 'title' => 'Sabtu Kreasi & Ekskul', 'desc' => 'Pamer momen kegiatan ekskul, hobi, olahraga, atau santai akhir pekanmu!'],
        ];
        $todayDay = now()->format('l');
        $todayChallenge = $dailyPrompts[$todayDay] ?? $dailyPrompts['Friday'];

        // 👑 Bintang Gelora Minggu Ini: Top 3 Siswa & 1 Guru Paling Menyala
        $topStudents = CircleProfile::with('user.school')
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
            ->orderByDesc('total_likes')
            ->orderByDesc('views_count')
            ->take(3)
            ->get();

        $topTeacher = CircleProfile::with('user.school')
            ->whereHas('user', function ($uq) {
                $uq->where('role', 'guru');
            })
            ->select('circle_profiles.*')
            ->selectSub(function ($q) {
                $q->from('circle_skill_endorsements')
                  ->whereColumn('circle_skill_endorsements.teacher_user_id', 'circle_profiles.user_id')
                  ->selectRaw('COUNT(*)');
            }, 'total_endorsements')
            ->orderByDesc('total_endorsements')
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
            'schoolFilter', 
            'search',
            'todayChallenge',
            'topStudents',
            'topTeacher'
        ));
    }
}
