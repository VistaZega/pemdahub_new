<?php

namespace App\Http\Controllers\Circle;

use App\Http\Controllers\Controller;
use App\Models\CircleProfile;
use App\Models\CircleProfileVisitor;
use App\Models\CircleProfileGuestbook;
use App\Models\CirclePost;
use App\Models\CirclePostMedia;
use App\Models\CircleReaction;
use App\Models\CircleComment;
use App\Models\CircleSkillEndorsement;
use App\Models\CirclePoll;
use App\Models\CirclePollOption;
use App\Models\CirclePollVote;
use App\Models\Notification;
use App\Models\Message;
use App\Models\User;
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
            'tab_category' => 'required|in:wall,portfolio,gallery',
            'post_type' => 'required|in:status,story,reel,project,devlog,media',
            'gallery_category' => 'nullable|in:photo,video,audio',
            'community_id' => 'nullable|exists:circle_communities,id',
            'mood_emoji' => 'nullable|string|max:10',
            'mood_text' => 'nullable|string|max:100',
            'location_tag' => 'nullable|string|max:100',
            'media' => 'nullable|file|max:51200', // max 50MB (video, audio, gambar)
        ]);

        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Silakan masuk terlebih dahulu untuk memposting.');
        }

        // Proteksi Anti Double-Click / Duplicate Post dalam 10 detik
        $recentDuplicate = CirclePost::where('user_id', $user->id)
            ->where('post_type', $request->post_type)
            ->where('content', $request->content)
            ->where('created_at', '>=', now()->subSeconds(10))
            ->first();

        if ($recentDuplicate) {
            return redirect()->back()->with('warning', 'Postingan kembar terdeteksi dan diabaikan untuk menjaga kerapian panggung.');
        }

        $expiresAt = null;
        if ($request->post_type === 'story') {
            $expiresAt = now()->addHours(24);
        }

        $majorCode = null;
        if ($user->student && $user->student->major) {
            $majorCode = $user->student->major->code ?? null;
        }

        $isGallery = $request->tab_category === 'gallery' || $request->boolean('is_gallery');
        $galleryCat = $request->gallery_category;

        $post = CirclePost::create([
            'user_id' => $user->id,
            'post_type' => $request->post_type,
            'tab_category' => $request->tab_category,
            'is_gallery' => $isGallery,
            'gallery_category' => $galleryCat,
            'community_id' => $request->community_id,
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

        // Tangani Upload Media (Gambar, Video, atau Audio)
        if ($request->hasFile('media')) {
            $file = $request->file('media');
            $mime = $file->getMimeType();

            if (str_starts_with($mime, 'video')) {
                $mediaType = 'video';
                $galleryCat = $galleryCat ?? 'video';
            } elseif (str_starts_with($mime, 'audio')) {
                $mediaType = 'audio';
                $galleryCat = $galleryCat ?? 'audio';
            } else {
                $mediaType = 'image';
                $galleryCat = $galleryCat ?? 'photo';
            }
            
            $path = $file->store('circle/media', 'public');

            CirclePostMedia::create([
                'circle_post_id' => $post->id,
                'media_type' => $mediaType,
                'file_path' => $path,
                'aspect_ratio' => $request->post_type === 'reel' || $request->post_type === 'story' ? '9:16' : '1:1',
            ]);

            if ($isGallery) {
                $post->update([
                    'gallery_category' => $galleryCat,
                ]);
            }
        }

        if ($post->community_id) {
            $post->community?->increment('posts_count');
        }

        // Tangani Polling Interaktif
        if ($request->filled('poll_question') && is_array($request->poll_options)) {
            $filteredOptions = array_values(array_filter(array_map('trim', $request->poll_options)));
            if (count($filteredOptions) >= 2) {
                $poll = CirclePoll::create([
                    'circle_post_id' => $post->id,
                    'question' => trim($request->poll_question),
                ]);

                foreach ($filteredOptions as $optText) {
                    CirclePollOption::create([
                        'circle_poll_id' => $poll->id,
                        'option_text' => $optText,
                        'votes_count' => 0,
                    ]);
                }
            }
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
            'theme_color' => 'nullable|string|in:pink,indigo,amber,emerald,sky,rose,purple,dark',
            'ambient_effect' => 'nullable|string|in:none,sakura,sparkles,cyber,bubbles,autumn',
            'sticky_note_text' => 'nullable|string|max:255',
            'sticky_note_color' => 'nullable|string|in:yellow,pink,mint,sky,purple',
            'anthem_url' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $profile = CircleProfile::getOrCreateForUser($user);

        // Parsing Passions (Minat & Hobi)
        $passions = $request->input('passions');
        if (is_string($passions)) {
            $passions = json_decode($passions, true) ?? array_filter(array_map('trim', explode(',', $passions)));
        }
        if (is_array($passions)) {
            $passions = array_values(array_slice(array_unique(array_filter($passions)), 0, 8));
        } else {
            $passions = null;
        }

        $updateData = [
            'nickname' => $request->nickname ?? $profile->nickname,
            'bio' => $request->bio,
            'mood_emoji' => $request->mood_emoji,
            'mood_text' => $request->mood_text,
            'theme_color' => $request->theme_color ?? $profile->theme_color ?? 'pink',
            'ambient_effect' => $request->ambient_effect ?? $profile->ambient_effect ?? 'none',
            'sticky_note_text' => $request->filled('sticky_note_text') ? trim($request->sticky_note_text) : null,
            'sticky_note_color' => $request->sticky_note_color ?? 'yellow',
            'passions' => $passions,
            'anthem_url' => $request->filled('anthem_url') ? trim($request->anthem_url) : null,
        ];

        if ($request->hasFile('banner')) {
            if ($profile->banner_path) {
                Storage::disk('public')->delete($profile->banner_path);
            }
            $updateData['banner_path'] = $request->file('banner')->store('circle/banners', 'public');
        }

        $profile->update($updateData);

        return redirect()->back()->with('success', 'Profil dan suasana dinding estetikmu berhasil diperbarui! ✨');
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

    /**
     * Perbarui Data Postingan / Media Galeri
     */
    public function updatePost(Request $request, $id)
    {
        $post = CirclePost::findOrFail($id);
        $user = Auth::user();

        if ($post->user_id !== $user?->id && !$user?->isSuperAdmin()) {
            return redirect()->back()->with('error', 'Anda tidak berwenang mengedit media ini.');
        }

        $request->validate([
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string|max:5000',
            'gallery_category' => 'nullable|in:photo,video,audio',
            'visibility' => 'nullable|in:public,school_only,circle_only',
        ]);

        $updateData = [
            'title' => $request->title,
            'content' => $request->content,
        ];

        if ($request->has('gallery_category')) {
            $updateData['gallery_category'] = $request->gallery_category;
        }

        if ($request->has('visibility')) {
            $updateData['visibility'] = $request->visibility;
        }

        $post->update($updateData);

        return redirect()->back()->with('success', 'Media galeri berhasil diperbarui! ✨');
    }

    /**
     * Sematkan / Lepas Sematan Postingan / Media Galeri (Pin to Top)
     */
    public function togglePinPost($id)
    {
        $post = CirclePost::findOrFail($id);
        $user = Auth::user();

        if ($post->user_id !== $user?->id && !$user?->isSuperAdmin()) {
            return redirect()->back()->with('error', 'Anda tidak berwenang menyematkan media ini.');
        }

        $newState = !$post->is_pinned;
        $post->update(['is_pinned' => $newState]);

        $msg = $newState 
            ? '📌 Karya berhasil disematkan di bagian teratas profil!' 
            : 'Sematkan karya telah dilepas.';

        return redirect()->back()->with('success', $msg);
    }

    /**
     * Berikan Suara pada Jajak Pendapat / Polling
     */
    public function votePoll(Request $request)
    {
        $request->validate([
            'circle_poll_id' => 'required|exists:circle_polls,id',
            'circle_poll_option_id' => 'required|exists:circle_poll_options,id',
        ]);

        $poll = CirclePoll::with(['options', 'post'])->findOrFail($request->circle_poll_id);
        $user = Auth::user();
        $userId = $user?->id;
        $ip = $request->ip();

        // Cek apakah sudah pernah memilih
        $existing = CirclePollVote::where('circle_poll_id', $poll->id)
            ->where(function ($q) use ($userId, $ip) {
                if ($userId) {
                    $q->where('user_id', $userId);
                } else {
                    $q->where('guest_ip', $ip);
                }
            })
            ->first();

        if ($existing) {
            return response()->json([
                'status' => 'already_voted',
                'message' => 'Kamu sudah memberikan suara pada polling ini! 📊',
            ], 422);
        }

        // Simpan suara
        CirclePollVote::create([
            'circle_poll_id' => $poll->id,
            'circle_poll_option_id' => $request->circle_poll_option_id,
            'user_id' => $userId,
            'guest_ip' => $userId ? null : $ip,
        ]);

        // Increment count pada opsi yang dipilih
        CirclePollOption::where('id', $request->circle_poll_option_id)->increment('votes_count');

        // Notifikasi ke pembuat postingan jika bukan dirinya sendiri
        if ($poll->post && $poll->post->user_id && (!$userId || $userId !== $poll->post->user_id)) {
            try {
                $voterName = $userId ? ($user->name ?? 'Warga Pembda') : 'Sahabat Pembda';
                Notification::create([
                    'user_id' => $poll->post->user_id,
                    'school_id' => $poll->post->school_id,
                    'type' => 'circle_poll_vote',
                    'title' => 'Suara Baru di Pollingmu! 📊',
                    'message' => "{$voterName} ikut memberikan suara pada polling \"{$poll->question}\"",
                    'related_model' => 'CirclePost',
                    'related_id' => $poll->post->id,
                    'is_read' => false,
                ]);
            } catch (\Throwable $e) {
                \Log::warning('Failed to create poll vote notification: ' . $e->getMessage());
            }
        }

        // Ambil data options terbaru dan persentase
        $options = CirclePollOption::where('circle_poll_id', $poll->id)->get();
        $totalVotes = (int) $options->sum('votes_count');

        $optionsData = $options->map(function ($opt) use ($totalVotes, $request) {
            return [
                'id' => $opt->id,
                'option_text' => $opt->option_text,
                'votes_count' => $opt->votes_count,
                'percentage' => $totalVotes > 0 ? (int) round(($opt->votes_count / $totalVotes) * 100) : 0,
                'is_selected' => $opt->id == $request->circle_poll_option_id,
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Suaramu berhasil dicatat! ✨',
            'total_votes' => $totalVotes,
            'voted_option_id' => (int) $request->circle_poll_option_id,
            'options' => $optionsData,
        ]);
    }

    /**
     * Ambil Daftar Kotak Masuk / Percakapan Pribadi (DM) Pengguna
     */
    public function getInbox()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $authId = $user->id;

        // Ambil ID kontak yang pernah saling berkirim pesan
        $recentMessages = Message::where('sender_id', $authId)
            ->orWhere('receiver_id', $authId)
            ->latest('id')
            ->take(150)
            ->get(['id', 'sender_id', 'receiver_id', 'created_at']);

        $contactIds = $recentMessages->map(function ($msg) use ($authId) {
            return $msg->sender_id === $authId ? $msg->receiver_id : $msg->sender_id;
        })->unique()->filter(function ($id) use ($authId) {
            return $id && $id !== $authId;
        })->take(25)->values();

        $threads = [];
        foreach ($contactIds as $contactId) {
            $contact = User::with(['circleProfile', 'student.school', 'teacher.school'])->find($contactId);
            if (!$contact) continue;

            $lastMsg = Message::where(function($q) use ($authId, $contactId) {
                $q->where('sender_id', $authId)->where('receiver_id', $contactId);
            })->orWhere(function($q) use ($authId, $contactId) {
                $q->where('sender_id', $contactId)->where('receiver_id', $authId);
            })->latest('id')->first();

            $unread = Message::where('sender_id', $contactId)
                ->where('receiver_id', $authId)
                ->where('is_read', false)
                ->count();

            $schoolName = $contact->student->school->name ?? ($contact->teacher->school->name ?? 'Perguruan PEMBDA');
            $roleLabel = match($contact->role) {
                'guru' => '👨‍🏫 Guru',
                'pegawai' => '💼 Pegawai',
                'siswa' => '🎓 Siswa',
                'superadmin' => '👑 Admin',
                'kepala_sekolah' => '🏫 Kepsek',
                default => '🌟 Warga Pembda'
            };

            $threads[] = [
                'contact' => [
                    'id' => $contact->id,
                    'name' => $contact->name,
                    'avatar_url' => $contact->avatar_url,
                    'username' => $contact->circle_username,
                    'role_label' => $roleLabel,
                    'school_name' => $schoolName,
                ],
                'last_message' => [
                    'text' => $lastMsg ? Str::limit($lastMsg->message, 45) : '',
                    'time' => $lastMsg ? $lastMsg->created_at->diffForHumans(null, true) : '',
                    'is_me' => $lastMsg ? ($lastMsg->sender_id === $authId) : false,
                ],
                'unread_count' => $unread,
            ];
        }

        $totalUnread = Message::where('receiver_id', $authId)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'status' => 'success',
            'threads' => $threads,
            'total_unread' => $totalUnread,
        ]);
    }

    /**
     * Ambil Seluruh Pesan Percakapan dengan Pengguna Tertentu
     */
    public function getConversation($userId)
    {
        $currentUser = Auth::user();
        if (!$currentUser) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $targetUser = User::with(['circleProfile', 'student.school', 'teacher.school'])->find($userId);
        if (!$targetUser) {
            return response()->json(['status' => 'error', 'message' => 'Pengguna tidak ditemukan'], 404);
        }

        $authId = $currentUser->id;
        $targetId = (int) $userId;

        // Tandai pesan dari lawan bicara sebagai sudah dibaca
        Message::where('sender_id', $targetId)
            ->where('receiver_id', $authId)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        // Ambil riwayat percakapan (50 pesan terakhir)
        $messages = Message::where(function($q) use ($authId, $targetId) {
            $q->where('sender_id', $authId)->where('receiver_id', $targetId);
        })->orWhere(function($q) use ($authId, $targetId) {
            $q->where('sender_id', $targetId)->where('receiver_id', $authId);
        })
        ->orderBy('created_at', 'asc')
        ->take(100)
        ->get();

        $formattedMessages = $messages->map(function ($msg) use ($authId) {
            return [
                'id' => $msg->id,
                'sender_id' => $msg->sender_id,
                'receiver_id' => $msg->receiver_id,
                'message' => $msg->message,
                'time' => $msg->created_at ? $msg->created_at->format('H:i') : '',
                'date' => $msg->created_at ? $msg->created_at->isoFormat('D MMMM Y') : '',
                'is_me' => $msg->sender_id === $authId,
                'is_read' => (bool) $msg->is_read,
            ];
        });

        $schoolName = $targetUser->student->school->name ?? ($targetUser->teacher->school->name ?? 'Perguruan PEMBDA');
        $roleLabel = match($targetUser->role) {
            'guru' => '👨‍🏫 Guru',
            'pegawai' => '💼 Pegawai',
            'siswa' => '🎓 Siswa',
            'superadmin' => '👑 Admin',
            'kepala_sekolah' => '🏫 Kepsek',
            default => '🌟 Warga Pembda'
        };

        return response()->json([
            'status' => 'success',
            'contact' => [
                'id' => $targetUser->id,
                'name' => $targetUser->name,
                'avatar_url' => $targetUser->avatar_url,
                'username' => $targetUser->circle_username,
                'role_label' => $roleLabel,
                'school_name' => $schoolName,
            ],
            'messages' => $formattedMessages,
        ]);
    }

    /**
     * Kirim Pesan Pribadi (DM) ke Pengguna Lain
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string|max:3000',
        ]);

        $currentUser = Auth::user();
        if (!$currentUser) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $receiverId = (int) $request->receiver_id;
        if ($currentUser->id === $receiverId) {
            return response()->json(['status' => 'error', 'message' => 'Tidak bisa mengirim pesan ke diri sendiri.'], 422);
        }

        $text = trim($request->message);
        if ($text === '') {
            return response()->json(['status' => 'error', 'message' => 'Pesan tidak boleh kosong.'], 422);
        }

        $msg = Message::create([
            'sender_id' => $currentUser->id,
            'receiver_id' => $receiverId,
            'message' => $text,
            'is_read' => false,
        ]);

        // Buat notifikasi ke penerima
        try {
            Notification::create([
                'user_id' => $receiverId,
                'type' => 'circle_dm',
                'title' => "Pesan Pribadi dari {$currentUser->name} ✉️",
                'message' => Str::limit($text, 80),
                'related_model' => 'User',
                'related_id' => $currentUser->id,
                'is_read' => false,
            ]);
        } catch (\Throwable $e) {
            \Log::warning('Failed creating DM notification: ' . $e->getMessage());
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Pesan terkirim! 🚀',
            'data' => [
                'id' => $msg->id,
                'sender_id' => $msg->sender_id,
                'receiver_id' => $msg->receiver_id,
                'message' => $msg->message,
                'time' => $msg->created_at->format('H:i'),
                'is_me' => true,
                'is_read' => false,
            ]
        ]);
    }

    /**
     * Cari Pengguna untuk Memulai Obrolan Baru
     */
    public function searchUsersForMessage(Request $request)
    {
        $currentUser = Auth::user();
        if (!$currentUser) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $query = trim($request->get('q', ''));
        if (strlen($query) < 2) {
            return response()->json(['status' => 'success', 'users' => []]);
        }

        $users = User::with(['circleProfile', 'student.school', 'teacher.school'])
            ->where('id', '!=', $currentUser->id)
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('username', 'like', "%{$query}%")
                  ->orWhereHas('circleProfile', function($cq) use ($query) {
                      $cq->where('nickname', 'like', "%{$query}%")
                        ->orWhere('username', 'like', "%{$query}%");
                  });
            })
            ->take(10)
            ->get();

        $results = $users->map(function ($u) {
            $schoolName = $u->student->school->name ?? ($u->teacher->school->name ?? 'Perguruan PEMBDA');
            $roleLabel = match($u->role) {
                'guru' => '👨‍🏫 Guru',
                'pegawai' => '💼 Pegawai',
                'siswa' => '🎓 Siswa',
                'superadmin' => '👑 Admin',
                'kepala_sekolah' => '🏫 Kepsek',
                default => '🌟 Warga Pembda'
            };

            return [
                'id' => $u->id,
                'name' => $u->name,
                'avatar_url' => $u->avatar_url,
                'username' => $u->circle_username,
                'role_label' => $roleLabel,
                'school_name' => $schoolName,
            ];
        });

        return response()->json([
            'status' => 'success',
            'users' => $results,
        ]);
    }

    /**
     * Tempelkan Catatan Buku Tamu / Stiker Apresiasi di Dinding Profil
     */
    public function storeGuestbook(Request $request)
    {
        $request->validate([
            'circle_profile_id' => 'required|exists:circle_profiles,id',
            'sticker_code' => 'required|string|max:50',
            'message' => 'nullable|string|max:500',
        ]);

        $sender = Auth::user();
        if (!$sender) {
            return redirect()->back()->with('error', 'Silakan masuk terlebih dahulu untuk menempelkan stiker di dinding.');
        }

        $profile = CircleProfile::with('user')->findOrFail($request->circle_profile_id);

        // Kamus Stiker Apresiasi Kreatif
        $stickers = [
            'menyala' => ['emoji' => '🔥', 'label' => 'Menyala!'],
            'kopi' => ['emoji' => '☕', 'label' => 'Titip Kopi'],
            'sukses' => ['emoji' => '🚀', 'label' => 'Calon Sukses!'],
            'idola' => ['emoji' => '✨', 'label' => 'Idola Kampus'],
            'bengkel' => ['emoji' => '🛠️', 'label' => 'Anak Teknik Top'],
            'rajin' => ['emoji' => '📚', 'label' => 'Rajin Banget'],
            'keren' => ['emoji' => '💖', 'label' => 'Keren Pol!'],
            'salam' => ['emoji' => '🤝', 'label' => 'Salam Kenal'],
            'bintang' => ['emoji' => '⭐', 'label' => 'Bintang Panutan'],
            'senyum' => ['emoji' => '🥰', 'label' => 'Bikin Kagum'],
        ];

        $code = $request->sticker_code;
        $meta = $stickers[$code] ?? ['emoji' => '✨', 'label' => 'Keren!'];

        // Proteksi Anti-Spam (1 stiker per 15 detik ke profil yang sama)
        $recentSpam = CircleProfileGuestbook::where('circle_profile_id', $profile->id)
            ->where('sender_user_id', $sender->id)
            ->where('created_at', '>=', now()->subSeconds(15))
            ->first();

        if ($recentSpam) {
            return redirect()->back()->with('warning', 'Terima kasih atas antusiasmemu! Tunggu beberapa saat sebelum menempelkan stiker berikutnya ya.');
        }

        $guestbook = CircleProfileGuestbook::create([
            'circle_profile_id' => $profile->id,
            'sender_user_id' => $sender->id,
            'sticker_code' => $code,
            'sticker_emoji' => $meta['emoji'],
            'sticker_label' => $meta['label'],
            'message' => $request->message ? trim($request->message) : null,
            'is_pinned' => false,
        ]);

        // Kirim Notifikasi ke Pemilik Profil jika pengirim bukan diri sendiri
        if ($sender->id !== $profile->user_id) {
            $senderNick = $sender->circleProfile->nickname ?? explode(' ', $sender->name)[0];
            $msgSnippet = $request->message ? ': "' . Str::limit($request->message, 45) . '"' : '';
            Notification::create([
                'user_id' => $profile->user_id,
                'school_id' => $sender->school_id,
                'type' => 'circle_guestbook',
                'title' => "💌 Stiker Baru dari {$senderNick}!",
                'message' => "{$sender->name} menempelkan stiker {$meta['emoji']} '{$meta['label']}' di dindingmu{$msgSnippet}.",
                'related_model' => 'CircleProfile',
                'related_id' => $profile->id,
                'is_read' => false,
            ]);
        }

        return redirect()->back()->with('success', "Stiker {$meta['emoji']} '{$meta['label']}' berhasil ditempelkan di dinding!");
    }

    /**
     * Hapus Catatan Dinding / Stiker Buku Tamu
     */
    public function destroyGuestbook($id)
    {
        $guestbook = CircleProfileGuestbook::with('circleProfile')->findOrFail($id);
        $user = Auth::user();

        if (!$user) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        // Boleh dihapus oleh pengirim ATAU pemilik dinding profil ATAU superadmin
        $isSender = $guestbook->sender_user_id === $user->id;
        $isOwner = $guestbook->circleProfile->user_id === $user->id;
        $isAdmin = $user->isSuperAdmin();

        if (!$isSender && !$isOwner && !$isAdmin) {
            return redirect()->back()->with('error', 'Kamu tidak memiliki izin untuk menghapus catatan ini.');
        }

        $guestbook->delete();

        return redirect()->back()->with('success', 'Catatan stiker berhasil dibersihkan dari dinding.');
    }

    /**
     * Sematkan / Lepas Sematan Catatan Dinding (Pin Favorite Note)
     */
    public function togglePinGuestbook($id)
    {
        $guestbook = CircleProfileGuestbook::with('circleProfile')->findOrFail($id);
        $user = Auth::user();

        if (!$user) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        // Hanya pemilik dinding profil atau superadmin yang berhak menyematkan
        $isOwner = $guestbook->circleProfile->user_id === $user->id;
        $isAdmin = $user->isSuperAdmin();

        if (!$isOwner && !$isAdmin) {
            return redirect()->back()->with('error', 'Hanya pemilik profil yang dapat menyematkan catatan favorit.');
        }

        // Jika akan disematkan, batasi maksimal 2 catatan yang dipin
        if (!$guestbook->is_pinned) {
            $pinnedCount = CircleProfileGuestbook::where('circle_profile_id', $guestbook->circle_profile_id)
                ->where('is_pinned', true)
                ->count();

            if ($pinnedCount >= 2) {
                // Lepas pin catatan tertua yang dipin
                $oldestPinned = CircleProfileGuestbook::where('circle_profile_id', $guestbook->circle_profile_id)
                    ->where('is_pinned', true)
                    ->oldest('updated_at')
                    ->first();
                $oldestPinned?->update(['is_pinned' => false]);
            }
        }

        $guestbook->update(['is_pinned' => !$guestbook->is_pinned]);

        $statusText = $guestbook->is_pinned ? 'Catatan manis berhasil disematkan di atas dinding!' : 'Sematan catatan dilepas.';

        return redirect()->back()->with('success', $statusText);
    }
}
