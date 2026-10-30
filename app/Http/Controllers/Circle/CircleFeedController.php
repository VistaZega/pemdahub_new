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

        $query = CirclePost::with(['user.circleProfile', 'user.school', 'media', 'reactions', 'comments'])
            ->active()
            ->publicVisible();

        // Filter unit sekolah jika dipilih
        if ($schoolFilter) {
            $query->whereHas('school', function ($q) use ($schoolFilter) {
                $q->where('name', 'like', "%{$schoolFilter}%")
                  ->orWhere('code', 'like', "%{$schoolFilter}%");
            });
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

        // 3 Unit Sekolah Aktif
        $schools = School::schoolsOnly()->get();

        return view('circle.index', compact('posts', 'activeStories', 'trendingProfiles', 'schools', 'tabFilter', 'schoolFilter'));
    }
}
