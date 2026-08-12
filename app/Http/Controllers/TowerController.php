<?php

namespace App\Http\Controllers;

use App\Models\PembdaTowerBrick;
use App\Models\PembdaTowerBrickLike;
use App\Models\ReputationLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TowerController extends Controller
{
    /**
     * Ambil data status Menara Prestasi Pembda
     */
    public function getState()
    {
        try {
            // Auto-seed sample bricks jika menara masih kosong
            $totalBricks = PembdaTowerBrick::count();
            if ($totalBricks === 0) {
                $this->seedInitialBricks();
                $totalBricks = PembdaTowerBrick::count();
            }

            $user = Auth::user();
            $hasPlacedToday = false;
            if ($user) {
                $hasPlacedToday = PembdaTowerBrick::where('user_id', $user->id)
                    ->whereDate('created_at', Carbon::today())
                    ->exists();
            }

            // Ambil bata terbaru (sampai 100 bata paling atas) — gunakan join langsung agar lebih robust
            $bricksRaw = \Illuminate\Support\Facades\DB::table('pembda_tower_bricks')
                ->leftJoin('users', 'pembda_tower_bricks.user_id', '=', 'users.id')
                ->leftJoin('schools', 'users.school_id', '=', 'schools.id')
                ->select(
                    'pembda_tower_bricks.id',
                    'pembda_tower_bricks.brick_number',
                    'pembda_tower_bricks.message',
                    'pembda_tower_bricks.color',
                    'pembda_tower_bricks.likes_count',
                    'pembda_tower_bricks.user_id',
                    'pembda_tower_bricks.created_at',
                    'users.name as user_name',
                    'users.role as user_role',
                    'schools.name as school_name'
                )
                ->orderBy('pembda_tower_bricks.brick_number', 'asc')
                ->limit(55)
                ->get();

            $bricks = $bricksRaw->map(function ($b) use ($user) {
                $isLiked = false;
                if ($user) {
                    $isLiked = \Illuminate\Support\Facades\DB::table('pembda_tower_brick_likes')
                        ->where('brick_id', $b->id)
                        ->where('user_id', $user->id)
                        ->exists();
                }
                return [
                    'id' => $b->id,
                    'brick_number' => $b->brick_number,
                    'message' => $b->message,
                    'color' => $b->color,
                    'likes_count' => $b->likes_count ?? 0,
                    'is_liked' => $isLiked,
                    'user_name' => $b->user_name ?? 'Komunitas Pembda',
                    'user_role' => $b->user_role ?? 'siswa',
                    'school_name' => $b->school_name ?? 'Yayasan',
                    'time_ago' => $b->created_at ? Carbon::parse($b->created_at)->diffForHumans() : null,
                ];
            });

            return response()->json([
                'success' => true,
                'stats' => [
                    'total_bricks' => $totalBricks,
                    'total_height' => ceil((-1 + sqrt(1 + 8 * $totalBricks)) / 2),
                ],
                'bricks' => $bricks->values()->toArray(),
                'has_placed_today' => $hasPlacedToday,
            ])->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
              ->header('Pragma', 'no-cache')
              ->header('Expires', 'Sat, 01 Jan 1990 00:00:00 GMT');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'stats' => ['total_bricks' => 0, 'total_height' => 0],
                'bricks' => [],
                'has_placed_today' => false,
            ], 200);
        }
    }

    /**
     * Meletakkan 1 bata motivasi baru ke Menara Prestasi
     */
    public function placeBrick(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Anda harus login terlebih dahulu.']);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:140',
            'color' => 'required|string|in:indigo,emerald,amber,rose,purple,cyan',
        ]);

        // Cek jatah harian 1 bata / hari
        $hasPlacedToday = PembdaTowerBrick::where('user_id', $user->id)
            ->whereDate('created_at', Carbon::today())
            ->exists();

        if ($hasPlacedToday) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah meletakkan bata motivasi hari ini! Kembali lagi besok.'
            ]);
        }

        // Auto reset jika sudah 55 bata (10 tingkat piramida penuh)
        $totalCurrent = PembdaTowerBrick::count();
        if ($totalCurrent >= 55) {
            \Illuminate\Support\Facades\DB::table('pembda_tower_brick_likes')->delete();
            \Illuminate\Support\Facades\DB::table('pembda_tower_bricks')->delete();
        }

        $nextBrickNumber = (PembdaTowerBrick::max('brick_number') ?? 0) + 1;

        $brick = PembdaTowerBrick::create([
            'user_id' => $user->id,
            'school_id' => $user->school_id,
            'message' => trim($validated['message']),
            'color' => $validated['color'],
            'brick_number' => $nextBrickNumber,
        ]);

        // Beri Poin Reputasi (+10)
        try {
            ReputationLog::log($user->id, 10, 'forum', 'Meletakkan Bata di Menara Prestasi Pembda');
        } catch (\Exception $e) {}

        return response()->json([
            'success' => true,
            'message' => '🎉 Mantap! Bata Anda berhasil dipasang di Menara Prestasi Pembda (+10 Poin Reputasi)!'
        ]);
    }

    /**
     * Memberikan apresiasi (Like) pada bata orang lain
     */
    public function likeBrick($id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Anda harus login']);
        }

        $brick = PembdaTowerBrick::find($id);
        if (!$brick) {
            return response()->json(['success' => false, 'message' => 'Bata tidak ditemukan']);
        }

        $existing = PembdaTowerBrickLike::where('brick_id', $brick->id)->where('user_id', $user->id)->first();
        if ($existing) {
            $existing->delete();
            $brick->decrement('likes_count');
            $liked = false;
        } else {
            PembdaTowerBrickLike::create(['brick_id' => $brick->id, 'user_id' => $user->id]);
            $brick->increment('likes_count');
            $liked = true;
        }

        return response()->json([
            'success' => true,
            'liked' => $liked,
            'likes_count' => $brick->likes_count,
        ]);
    }

    /**
     * Seed sampel bata awal dari Sistem
     */
    private function seedInitialBricks()
    {
        $samples = [
            ['msg' => 'Mari bersatu membangun masa depan dan meraih prestasi di Perguruan Pembda!', 'color' => 'indigo'],
        ];

        // Dapatkan user ID pertama dari database
        $firstUser = \Illuminate\Support\Facades\DB::table('users')->first();
        if (!$firstUser) return;

        $pastDate = Carbon::now()->subDays(2)->format('Y-m-d H:i:s');

        // Gunakan DB::table()->insert() agar bypass mass-assignment $fillable
        foreach ($samples as $idx => $s) {
            \Illuminate\Support\Facades\DB::table('pembda_tower_bricks')->insert([
                'user_id' => $firstUser->id,
                'school_id' => $firstUser->school_id ?? null,
                'message' => $s['msg'],
                'color' => $s['color'],
                'brick_number' => $idx + 1,
                'likes_count' => rand(3, 12),
                'created_at' => $pastDate,
                'updated_at' => $pastDate,
            ]);
        }
    }
}
