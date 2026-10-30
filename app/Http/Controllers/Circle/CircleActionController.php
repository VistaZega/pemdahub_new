<?php

namespace App\Http\Controllers\Circle;

use App\Http\Controllers\Controller;
use App\Models\CircleProfile;
use App\Models\CirclePost;
use App\Models\CirclePostMedia;
use App\Models\CircleReaction;
use App\Models\CircleComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CircleActionController extends Controller
{
    /**
     * Buat Postingan Baru (Status Nongki, Story 24 Jam, Reels, atau Karya Portofolio)
     */
    public function storePost(Request $request)
    {
        $request->validate([
            'content' => 'nullable|string|max:5000',
            'title' => 'nullable|string|max:255',
            'tab_category' => 'required|in:wall,portfolio',
            'post_type' => 'required|in:status,story,reel,project,devlog',
            'mood_emoji' => 'nullable|string|max:10',
            'mood_text' => 'nullable|string|max:100',
            'location_tag' => 'nullable|string|max:100',
            'media' => 'nullable|file|max:30720', // max 30MB
        ]);

        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Silakan masuk terlebih dahulu untuk memposting.');
        }

        $expiresAt = null;
        if ($request->post_type === 'story') {
            $expiresAt = now()->addHours(24);
        }

        $majorCode = null;
        if ($user->student && $user->student->major) {
            $majorCode = $user->student->major->code ?? null;
        }

        $post = CirclePost::create([
            'user_id' => $user->id,
            'post_type' => $request->post_type,
            'tab_category' => $request->tab_category,
            'title' => $request->title,
            'content' => $request->content,
            'mood_emoji' => $request->mood_emoji,
            'mood_text' => $request->mood_text,
            'location_tag' => $request->location_tag,
            'school_id' => $user->school_id,
            'major_code' => $majorCode,
            'visibility' => 'public',
            'expires_at' => $expiresAt,
        ]);

        // Tangani Upload Media
        if ($request->hasFile('media')) {
            $file = $request->file('media');
            $mime = $file->getMimeType();
            $mediaType = str_starts_with($mime, 'video') ? 'video' : 'image';
            
            $path = $file->store('circle/media', 'public');

            CirclePostMedia::create([
                'circle_post_id' => $post->id,
                'media_type' => $mediaType,
                'file_path' => $path,
                'aspect_ratio' => $request->post_type === 'reel' || $request->post_type === 'story' ? '9:16' : '1:1',
            ]);
        }

        return redirect()->back()->with('success', 'Postinganmu berhasil diterbitkan di PembdaCIRCLE! ✨');
    }

    /**
     * Perbarui Profil & Suasana Hati (Mood)
     */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'nickname' => 'nullable|string|max:50',
            'bio' => 'nullable|string|max:500',
            'mood_emoji' => 'nullable|string|max:10',
            'mood_text' => 'nullable|string|max:100',
            'banner' => 'nullable|image|max:10240', // max 10MB
        ]);

        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $profile = CircleProfile::getOrCreateForUser($user);

        $updateData = [
            'nickname' => $request->nickname ?? $profile->nickname,
            'bio' => $request->bio,
            'mood_emoji' => $request->mood_emoji,
            'mood_text' => $request->mood_text,
        ];

        if ($request->hasFile('banner')) {
            if ($profile->banner_path) {
                Storage::disk('public')->delete($profile->banner_path);
            }
            $updateData['banner_path'] = $request->file('banner')->store('circle/banners', 'public');
        }

        $profile->update($updateData);

        return redirect()->back()->with('success', 'Profil dan suasana hatimu berhasil diperbarui! 🌸');
    }

    /**
     * Toggle Reaksi Apresiasi (Lit, Clap, Inspirasi, Love)
     */
    public function toggleReaction(Request $request)
    {
        $request->validate([
            'circle_post_id' => 'required|exists:circle_posts,id',
            'reaction_type' => 'required|in:lit,clap,nongki,love,proud_parent',
        ]);

        $post = CirclePost::findOrFail($request->circle_post_id);
        $userId = Auth::id();
        $ip = $request->ip();

        $query = CircleReaction::where('circle_post_id', $post->id);
        if ($userId) {
            $query->where('user_id', $userId);
        } else {
            $query->where('guest_ip', $ip);
        }

        $existing = $query->first();

        if ($existing) {
            if ($existing->reaction_type === $request->reaction_type) {
                // Hapus reaksi jika mengklik tipe yang sama
                $existing->delete();
                $post->decrement('likes_count');
                $status = 'removed';
            } else {
                // Ganti tipe reaksi
                $existing->update(['reaction_type' => $request->reaction_type]);
                $status = 'updated';
            }
        } else {
            CircleReaction::create([
                'circle_post_id' => $post->id,
                'user_id' => $userId,
                'guest_ip' => $userId ? null : $ip,
                'guest_name' => $userId ? null : ($request->guest_name ?? 'Sahabat Pembda'),
                'reaction_type' => $request->reaction_type,
                'created_at' => now(),
            ]);
            $post->increment('likes_count');
            $status = 'added';
        }

        if ($request->wantsJson()) {
            return response()->json([
                'status' => $status,
                'likes_count' => $post->fresh()->likes_count,
            ]);
        }

        return redirect()->back();
    }

    /**
     * Tambah Komentar
     */
    public function storeComment(Request $request)
    {
        $request->validate([
            'circle_post_id' => 'required|exists:circle_posts,id',
            'comment_text' => 'required|string|max:1000',
        ]);

        $user = Auth::user();
        $authorName = $user ? $user->name : ($request->guest_name ?? 'Tamu Pembda');

        CircleComment::create([
            'circle_post_id' => $request->circle_post_id,
            'user_id' => $user?->id,
            'author_name' => $authorName,
            'is_guest' => !$user,
            'comment_text' => $request->comment_text,
        ]);

        CirclePost::where('id', $request->circle_post_id)->increment('comments_count');

        return redirect()->back()->with('success', 'Komentarmu berhasil dikirim! 💬');
    }
}
