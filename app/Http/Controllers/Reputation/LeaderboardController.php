<?php

namespace App\Http\Controllers\Reputation;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Reputation;
use App\Models\Badge;

class LeaderboardController extends Controller
{
    /**
     * Show global leaderboard (Hall of Fame)
     */
    public function index()
    {
        $user = auth()->user();
        if ($user) {
            if (($user->isSiswa() || $user->isOrangTua()) && !\App\Models\Setting::getValue('siswa_view_reputation_leaderboard', true)) {
                abort(403, 'Akses Papan Peringkat (Hall of Fame) telah dinonaktifkan oleh administrator.');
            }
            if ($user->isGuru() && !\App\Models\Setting::getValue('guru_view_reputation_leaderboard', true)) {
                abort(403, 'Akses Papan Peringkat (Hall of Fame) telah dinonaktifkan oleh administrator.');
            }
        }

        $topStudents = Reputation::with([
            'user.student.classroom.school', 
            'user.student.school',
            'user.school',
            'user.badges', 
            'user.reputationLogs' => fn($q) => $q->orderBy('id', 'desc')->take(5)
        ])
            ->whereHas('user', function($q) {
                $q->where('role', 'siswa');
            })
            ->orderBy('total_points', 'desc')
            ->take(10)
            ->get();

        $topTeachers = Reputation::with([
            'user.teacher', 
            'user.badges', 
            'user.reputationLogs' => fn($q) => $q->orderBy('id', 'desc')->take(5)
        ])
            ->whereHas('user', function($q) {
                $q->where('role', 'guru')
                  ->where('username', '!=', 'yulzega')
                  ->where('name', 'NOT LIKE', '%Yulianus Zega%');
            })
            ->orderBy('total_points', 'desc')
            ->take(10)
            ->get();

        $userRanking = null;
        if (auth()->check()) {
            $currentUser = auth()->user();
            $isYulianusZega = $currentUser->username === 'yulzega' || str_contains(strtolower($currentUser->name ?? ''), 'yulianus zega');
            if (!$isYulianusZega) {
                $userRanking = Reputation::where('total_points', '>', $currentUser->reputation?->total_points ?? 0)
                    ->count() + 1;
            }
        }

        return view('reputation.leaderboard', compact('topStudents', 'topTeachers', 'userRanking'));
    }
}
