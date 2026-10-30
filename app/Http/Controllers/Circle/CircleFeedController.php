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

        $query = CirclePost::with(['user.circleProfile', 'user.school', 'media', 'reactions', 'comments'])
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
        }

        $posts = $query->latest()->paginate(12)->withQueryString();

        // Ambil Stories 24 Jam terbaru dari semua siswa/guru aktif
        $activeStories = CirclePost::with(['user.circleProfile', 'user.school', 'school', 'media'])
            ->where('post_type', 'story')
            ->where('expires_at', '>', now())
            ->latest()
            ->take(15)
            ->get();

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

        return view('circle.index', compact('posts', 'activeStories', 'trendingProfiles', 'matchedProfiles', 'schools', 'tabFilter', 'schoolFilter', 'search'));
    }
}
