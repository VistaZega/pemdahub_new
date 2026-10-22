<?php

namespace App\Http\Controllers;

use App\Models\ChallengeLog;
use App\Models\ReputationLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PembdaChallengeController extends Controller
{
    const COOLDOWN_MINUTES = 15;

    /**
     * Cek status tantangan & cooldown user saat ini
     */
    public function status(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['authenticated' => false]);
        }

        $gameKey = $request->input('game_key', 'missionaries_cannibals');

        // Cek apakah sedang cooldown
        $cooldownUntil = Cache::get("challenge_cooldown_{$user->id}_{$gameKey}");
        $remainingCooldownSeconds = 0;
        if ($cooldownUntil && $cooldownUntil > now()->timestamp) {
            $remainingCooldownSeconds = $cooldownUntil - now()->timestamp;
        }

        // Cek apakah sudah menang hari ini
        $hasWonToday = ChallengeLog::where('user_id', $user->id)
            ->where('game_key', $gameKey)
            ->where('status', 'won')
            ->whereDate('created_at', now()->toDateString())
            ->exists();

        // Rekor terbaik
        $bestRecord = ChallengeLog::where('user_id', $user->id)
            ->where('game_key', $gameKey)
            ->where('status', 'won')
            ->orderBy('moves', 'asc')
            ->orderBy('seconds_taken', 'asc')
            ->first();

        // Top 5 Solvers Hari Ini
        $topSolvers = ChallengeLog::with('user')
            ->where('game_key', $gameKey)
            ->where('status', 'won')
            ->whereDate('created_at', now()->toDateString())
            ->orderBy('moves', 'asc')
            ->orderBy('seconds_taken', 'asc')
            ->limit(5)
            ->get()
            ->map(function ($log) {
                return [
                    'name' => explode(' ', trim($log->user->name ?? 'Anonim'))[0],
                    'full_name' => $log->user->name ?? 'Anonim',
                    'moves' => $log->moves,
                    'stars' => $log->stars,
                    'seconds' => $log->seconds_taken,
                ];
            });

        return response()->json([
            'authenticated' => true,
            'is_in_cooldown' => $remainingCooldownSeconds > 0,
            'cooldown_seconds' => $remainingCooldownSeconds,
            'has_won_today' => $hasWonToday,
            'best_moves' => $bestRecord?->moves,
            'best_stars' => $bestRecord?->stars,
            'top_solvers' => $topSolvers,
        ]);
    }

    /**
     * Submit penyelesaian tantangan berhasil
     */
    public function complete(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'game_key' => 'required|string',
            'moves' => 'required|integer|min:7', // Minimal 7 langkah untuk Hanoi 3-cakram
            'lives_left' => 'required|integer|min:1|max:3',
            'seconds_taken' => 'required|integer|min:3',
            'disk_count' => 'nullable|integer|min:3|max:6',
        ]);

        $gameKey = $validated['game_key'];
        $moves = (int) $validated['moves'];
        $lives = (int) $validated['lives_left'];
        $seconds = (int) $validated['seconds_taken'];
        $diskCount = (int) ($validated['disk_count'] ?? 3);

        // Cek cooldown
        $cooldownUntil = Cache::get("challenge_cooldown_{$user->id}_{$gameKey}");
        if ($cooldownUntil && $cooldownUntil > now()->timestamp) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sedang dalam masa cooldown. Silakan istirahat sejenak.',
            ], 429);
        }

        // Tentukan Bintang & Poin Reputasi berdasarkan game & efisiensi langkah
        if ($gameKey === 'tower_of_hanoi') {
            $optimalMoves = (int) (pow(2, $diskCount) - 1);
            if ($moves <= $optimalMoves + 2) {
                $stars = 3;
                $potentialPoints = 50;
                $rankTitle = "Master Hanoi! Solusi Optimal ({$moves}/{$optimalMoves} langkah) ⭐⭐⭐";
            } elseif ($moves <= (int) ($optimalMoves * 1.5)) {
                $stars = 2;
                $potentialPoints = 35;
                $rankTitle = "Logika Matematis Hebat! Solusi Efisien ⭐⭐";
            } else {
                $stars = 1;
                $potentialPoints = 20;
                $rankTitle = "Berhasil Memindahkan Menara! ⭐";
            }
            $logTitle = "Menyelesaikan PembdaCHALLENGE: Menara Hanoi ({$diskCount} Cakram, {$moves} langkah, {$stars} Bintang)";
        } else {
            // Misionaris vs Kanibal (default)
            if ($moves <= 13) {
                $stars = 3;
                $potentialPoints = 50;
                $rankTitle = 'Genius STEAM! Solusi Sempurna ⭐⭐⭐';
            } elseif ($moves <= 17) {
                $stars = 2;
                $potentialPoints = 35;
                $rankTitle = 'Logika Hebat! Solusi Efisien ⭐⭐';
            } else {
                $stars = 1;
                $potentialPoints = 20;
                $rankTitle = 'Berhasil Menyeberang! ⭐';
            }
            $logTitle = "Menyelesaikan PembdaCHALLENGE: Misionaris vs Kanibal ({$moves} langkah, {$stars} Bintang)";
        }

        // Cek apakah sudah pernah klaim poin hari ini untuk game ini
        $alreadyClaimedToday = ChallengeLog::where('user_id', $user->id)
            ->where('game_key', $gameKey)
            ->where('status', 'won')
            ->where('points_awarded', '>', 0)
            ->whereDate('created_at', now()->toDateString())
            ->exists();

        $pointsAwarded = $alreadyClaimedToday ? 0 : $potentialPoints;

        DB::beginTransaction();
        try {
            // Berikan poin reputasi jika belum klaim hari ini
            if ($pointsAwarded > 0) {
                ReputationLog::log(
                    $user->id,
                    $pointsAwarded,
                    'challenge_won',
                    $logTitle,
                    null
                );
            }

            // Catat log tantangan
            ChallengeLog::create([
                'user_id' => $user->id,
                'game_key' => $gameKey,
                'moves' => $moves,
                'lives_left' => $lives,
                'stars' => $stars,
                'points_awarded' => $pointsAwarded,
                'seconds_taken' => $seconds,
                'status' => 'won',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'stars' => $stars,
                'moves' => $moves,
                'points_awarded' => $pointsAwarded,
                'already_claimed_today' => $alreadyClaimedToday,
                'rank_title' => $rankTitle,
                'message' => $pointsAwarded > 0 
                    ? "Luar Biasa! Anda memecahkan teka-teki logika ini dan memperoleh +{$pointsAwarded} Poin Reputasi!" 
                    : "Hebat! Rekor permainan Anda ({$moves} langkah) berhasil tercatat!",
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Catat kegagalan (3 nyawa habis) dan aktifkan cooldown
     */
    public function fail(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false], 401);
        }

        $gameKey = $request->input('game_key', 'missionaries_cannibals');

        // Pasang cooldown 15 menit
        $cooldownTimestamp = now()->addMinutes(self::COOLDOWN_MINUTES)->timestamp;
        Cache::put("challenge_cooldown_{$user->id}_{$gameKey}", $cooldownTimestamp, now()->addMinutes(self::COOLDOWN_MINUTES));

        ChallengeLog::create([
            'user_id' => $user->id,
            'game_key' => $gameKey,
            'moves' => (int) $request->input('moves', 0),
            'lives_left' => 0,
            'stars' => 0,
            'points_awarded' => 0,
            'seconds_taken' => (int) $request->input('seconds_taken', 0),
            'status' => 'failed',
        ]);

        $failMessage = $gameKey === 'tower_of_hanoi'
            ? "Kesempatan habis! Istirahat sejenak selama " . self::COOLDOWN_MINUTES . " menit untuk menganalisis strategi pemindahan rekursif Anda."
            : "Kesempatan habis! Misionaris terancam. Istirahat sejenak selama " . self::COOLDOWN_MINUTES . " menit untuk menganalisis strategi Anda.";

        return response()->json([
            'success' => true,
            'cooldown_minutes' => self::COOLDOWN_MINUTES,
            'cooldown_seconds' => self::COOLDOWN_MINUTES * 60,
            'message' => $failMessage,
        ]);
    }
}
