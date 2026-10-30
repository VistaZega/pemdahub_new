<?php

namespace App\Http\Controllers\Circle;

use App\Http\Controllers\Controller;
use App\Models\CircleProfile;
use App\Models\CirclePost;
use App\Models\CirclePostMedia;
use App\Models\CircleReaction;
use App\Models\CircleComment;
use App\Models\CircleSkillEndorsement;
use App\Models\Notification;
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

            // Kirim notifikasi jika pembuat post adalah user terdaftar dan bukan dirinya sendiri
            if ($post->user_id && (!$userId || $userId !== $post->user_id)) {
                try {
                    $reactorName = $userId ? (Auth::user()?->name ?? 'Warga Pembda') : ($request->guest_name ?? 'Sahabat Pembda');
                    $emoji = match($request->reaction_type) {
                        'lit' => '🔥 Menyala!',
                        'clap' => '👏 Keren Pol!',
                        'nongki' => '💡 Memicu Ide!',
                        'love' => '❤️ Bangga!',
                        default => '✨ Apresiasi',
                    };
                    $postTitle = $post->title ? "\"{$post->title}\"" : 'postinganmu';
                    Notification::create([
                        'user_id' => $post->user_id,
                        'school_id' => $post->school_id,
                        'type' => 'circle_reaction',
                        'title' => "Apresiasi {$emoji}",
                        'message' => "{$reactorName} memberikan apresiasi pada {$postTitle}.",
                        'related_model' => 'CirclePost',
                        'related_id' => $post->id,
                        'is_read' => false,
                    ]);
                } catch (\Throwable $e) {
                    \Log::warning('Failed to create circle reaction notification: ' . $e->getMessage());
                }
            }
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

        $post = CirclePost::where('id', $request->circle_post_id)->first();
        if ($post) {
            $post->increment('comments_count');

            // Kirim notifikasi ke pemilik postingan
            if ($post->user_id && (!$user || $user->id !== $post->user_id)) {
                try {
                    $postTitle = $post->title ? "\"{$post->title}\"" : 'karyamu';
                    Notification::create([
                        'user_id' => $post->user_id,
                        'school_id' => $post->school_id,
                        'type' => 'circle_comment',
                        'title' => 'Komentar Baru di PembdaCIRCLE 💬',
                        'message' => "{$authorName} mengomentari {$postTitle}: \"" . Str::limit($request->comment_text, 60) . "\"",
                        'related_model' => 'CirclePost',
                        'related_id' => $post->id,
                        'is_read' => false,
                    ]);
                } catch (\Throwable $e) {
                    \Log::warning('Failed to create circle comment notification: ' . $e->getMessage());
                }
            }
        }

        return redirect()->back()->with('success', 'Komentarmu berhasil dikirim! 💬');
    }

    /**
     * Berikan Lencana Validasi Keahlian Resmi (Oleh Guru/Mentor)
     */
    public function storeSkillEndorsement(Request $request)
    {
        $request->validate([
            'student_user_id' => 'required|exists:users,id',
            'skill_name' => 'required|string|max:100',
            'endorsement_note' => 'nullable|string|max:500',
        ]);

        $teacher = Auth::user();
        if (!$teacher || ($teacher->role !== 'guru' && !$teacher->isSuperAdmin() && !$teacher->isAdminSekolah())) {
            return redirect()->back()->with('error', 'Hanya Guru, Penguji atau Admin Resmi yang dapat memvalidasi keahlian siswa.');
        }

        // Cek apakah keahlian ini sudah divalidasi oleh guru yang sama
        $existing = CircleSkillEndorsement::where('student_user_id', $request->student_user_id)
            ->where('teacher_user_id', $teacher->id)
            ->whereRaw('LOWER(skill_name) = ?', [strtolower(trim($request->skill_name))])
            ->first();

        $endId = null;
        if ($existing) {
            $existing->update([
                'endorsement_note' => $request->endorsement_note ?? $existing->endorsement_note,
            ]);
            $endId = $existing->id;
        } else {
            $newEnd = CircleSkillEndorsement::create([
                'student_user_id' => $request->student_user_id,
                'teacher_user_id' => $teacher->id,
                'skill_name' => trim($request->skill_name),
                'endorsement_note' => $request->endorsement_note,
                'created_at' => now(),
            ]);
            $endId = $newEnd->id;
        }

        // Kirim notifikasi ke siswa penerima lencana
        try {
            Notification::create([
                'user_id' => $request->student_user_id,
                'school_id' => $teacher->school_id,
                'type' => 'circle_endorsement',
                'title' => 'Lencana Piawai Resmi Disahkan! 🏅',
                'message' => "Guru {$teacher->name} mengesahkan keahlian \"{$request->skill_name}\" pada portofoliomu.",
                'related_model' => 'CircleSkillEndorsement',
                'related_id' => $endId,
                'is_read' => false,
            ]);
        } catch (\Throwable $e) {
            \Log::warning('Failed to create circle endorsement notification: ' . $e->getMessage());
        }

        if ($existing) {
            return redirect()->back()->with('success', 'Catatan lencana keahlian siswa berhasil diperbarui! 🏅');
        }

        return redirect()->back()->with('success', 'Lencana keahlian resmi berhasil divalidasi dan disematkan pada portofolio siswa! 🏅✨');
    }

    /**
     * Tandai semua notifikasi pengguna sebagai terbaca
     */
    public function markNotificationsRead(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            Notification::where('user_id', $user->id)
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Semua kabar sudah ditandai terbaca.');
    }

    /**
     * Hapus Validasi Keahlian Siswa
     */
    public function destroySkillEndorsement($id)
    {
        $endorsement = CircleSkillEndorsement::findOrFail($id);
        $user = Auth::user();

        if ($endorsement->teacher_user_id === $user?->id || $user?->isSuperAdmin()) {
            $endorsement->delete();
            return redirect()->back()->with('success', 'Lencana keahlian berhasil dihapus.');
        }

        return redirect()->back()->with('error', 'Anda tidak berwenang menghapus lencana ini.');
    }

    /**
     * Hapus Postingan (Oleh Pemilik atau SuperAdmin)
     */
    public function destroyPost($id)
    {
        $post = CirclePost::findOrFail($id);
        $user = Auth::user();

        if ($post->user_id === $user?->id || $user?->isSuperAdmin()) {
            // Hapus media terkait dari disk
            foreach ($post->media as $media) {
                if ($media->file_path) {
                    Storage::disk('public')->delete($media->file_path);
                }
            }
            $post->delete();
            return redirect()->back()->with('success', 'Postingan berhasil dihapus dari PembdaCIRCLE.');
        }

        return redirect()->back()->with('error', 'Anda tidak berwenang menghapus postingan ini.');
    }
}
