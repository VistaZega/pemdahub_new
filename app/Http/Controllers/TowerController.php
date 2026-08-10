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

        // Ambil bata terbaru (sampai 100 bata paling atas)
        $bricks = PembdaTowerBrick::with(['user:id,name,role,school_id', 'user.school:id,name,short_name'])
            ->orderBy('brick_number', 'desc')
            ->take(100)
            ->get()
            ->map(function ($brick) use ($user) {
                return [
                    'id' => $brick->id,
                    'brick_number' => $brick->brick_number,
                    'message' => $brick->message,
                    'color' => $brick->color,
                    'likes_count' => $brick->likes_count,
                    'is_liked' => $user ? $brick->isLikedBy($user) : false,
                    'user_name' => $brick->user ? $brick->user->name : 'Komunitas Pembda',
                    'user_role' => $brick->user ? $brick->user->role_label : 'Siswa',
                    'school_name' => $brick->user && $brick->user->school ? ($brick->user->school->short_name ?? $brick->user->school->name) : 'Yayasan',
                    'time_ago' => $brick->created_at ? $brick->created_at->diffForHumans() : null,
                ];
            });

        return response()->json([
            'success' => true,
            'stats' => [
                'total_bricks' => $totalBricks,
                'total_height' => ceil($totalBricks / 3), // 3 bata per tingkat lantai
            ],
            'bricks' => $bricks,
            'has_placed_today' => $hasPlacedToday,
        ])->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
          ->header('Pragma', 'no-cache')
          ->header('Expires', 'Sat, 01 Jan 1990 00:00:00 GMT');
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
            ['msg' => 'Selamat Datang di Menara Prestasi Pembda! Mari bersatu membangun masa depan.', 'color' => 'indigo'],
            ['msg' => 'Semangat belajar untuk seluruh siswa SD, SMP, SMA, dan SMK Pembda!', 'color' => 'amber'],
            ['msg' => 'Salam hormat untuk Bapak/Ibu Guru dan Pengurus Yayasan Perguruan Pembda Nias.', 'color' => 'emerald'],
            ['msg' => 'Inovasi, Integritas, dan Prestasi Tanpa Batas!', 'color' => 'purple'],
            ['msg' => 'Sukses untuk ujian dan kegiatan belajar mengajar minggu ini!', 'color' => 'rose'],
            ['msg' => 'Bersama Perguruan Pembda, kita pasti bisa menggapai cita-cita tinggi!', 'color' => 'cyan'],
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
