<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\ForumThread;
use App\Models\ForumReply;
use App\Models\ForumLike;
use App\Models\ForumGroup;
use App\Models\ForumGroupMember;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class MobileSpaceController extends Controller
{
    /**
     * Tampilan Utama Pembda Space (WA Groups & Kanal Forum Publik)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $tab = $request->input('tab', 'groups'); // 'groups' atau 'kanal'
        $groupFilter = $request->input('filter', 'semua');
        $category = $request->input('category');
        $search = $request->input('search');

        // Otomatis sinkronkan grup pengguna berbasis peran & Rombel
        $this->syncUserGroups($user);

        // 1. Ambil Data WA Groups
        $groupsQuery = ForumGroup::whereHas('members', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->with(['latestThread.user', 'members']);

        if ($groupFilter === 'kelas') {
            $groupsQuery->where('type', 'classroom');
        } elseif ($groupFilter === 'mapel') {
            $groupsQuery->where('type', 'subject');
        } elseif ($groupFilter === 'lobi') {
            $groupsQuery->whereIn('type', ['lobby', 'broadcast', 'extracurricular']);
        }

        if ($search) {
            $groupsQuery->where('name', 'like', "%{$search}%");
        }

        $groups = $groupsQuery->get()->map(function ($grp) {
            // Hitung statistik anggota riil berdasarkan tipe grup
            if ($grp->type === 'lobby') {
                $studentCount = Student::count();
                $teacherCount = Teacher::count();
                $grp->calculated_member_count = ($studentCount > 0) ? ($studentCount + $teacherCount) : User::whereIn('role', ['siswa', 'guru', 'admin'])->count();
            } elseif ($grp->type === 'broadcast' || $grp->slug === 'ruang-guru-pembda') {
                $grp->calculated_member_count = Teacher::count() + User::where('role', 'admin')->count();
            } elseif ($grp->classroom_id) {
                $cls = Classroom::find($grp->classroom_id);
                if ($cls) {
                    $pivotCount = $cls->students()->count();
                    $grp->calculated_member_count = $pivotCount + 1; // Siswa + Wali Kelas
                } else {
                    $grp->calculated_member_count = count($grp->members);
                }
            } else {
                $grp->calculated_member_count = count($grp->members);
            }
            return $grp;
        })->sortByDesc(function ($g) {
            return $g->latestThread?->created_at ?? $g->created_at;
        })->values();

        // 2. Ambil Data Kanal / Kategori Forum Publik (Legacy & Multi-Channel)
        $categories = ForumThread::CATEGORIES;

        $threadsQuery = ForumThread::with(['user', 'replies'])
            ->withCount('replies', 'likes');

        if ($category) {
            $threadsQuery->where('category', $category);
        }

        if ($search) {
            $threadsQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $threads = $threadsQuery->orderBy('is_pinned', 'desc')
            ->latest()
            ->paginate(15);

        return view('mobile.space.index', compact('groups', 'tab', 'groupFilter', 'category', 'categories', 'search', 'threads'));
    }

    /**
     * Halaman Ruang Chat / Diskusi Spesifik Grup
     */
    public function showGroup($groupId)
    {
        $user = Auth::user();
        $group = ForumGroup::with(['members.user', 'classroom'])->findOrFail($groupId);

        // Pastikan pengguna terdaftar sebagai anggota grup
        $membership = ForumGroupMember::where('group_id', $group->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$membership) {
            return redirect()->route('mobile.space.index')->with('error', 'Anda tidak memiliki akses ke grup ini.');
        }

        // Hitung real member count
        if ($group->type === 'lobby') {
            $studentCount = Student::count();
            $teacherCount = Teacher::count();
            $group->calculated_member_count = ($studentCount > 0) ? ($studentCount + $teacherCount) : User::whereIn('role', ['siswa', 'guru', 'admin'])->count();
        } elseif ($group->slug === 'ruang-guru-pembda') {
            $group->calculated_member_count = Teacher::count() + User::where('role', 'admin')->count();
        } elseif ($group->classroom_id) {
            $cls = Classroom::find($group->classroom_id);
            if ($cls) {
                $pivotCount = $cls->students()->count();
                $group->calculated_member_count = $pivotCount + 1; // Siswa + Wali Kelas
            } else {
                $group->calculated_member_count = count($group->members);
            }
        } else {
            $group->calculated_member_count = count($group->members);
        }

        // Update last_read_at
        $membership->update(['last_read_at' => now()]);

        // Ambil postingan / pesan di dalam grup ini
        $threads = ForumThread::where('group_id', $group->id)
            ->with(['user', 'replies.user', 'poll.options', 'poll.votes'])
            ->withCount('replies', 'likes')
            ->orderBy('is_pinned', 'desc')
            ->latest()
            ->paginate(20);

        return view('mobile.space.group_show', compact('group', 'membership', 'threads'));
    }

    /**
     * Kirim Pesan / Postingan Baru ke Dalam Grup
     */
    public function storeGroupThread(Request $request, $groupId)
    {
        $user = Auth::user();
        $group = ForumGroup::findOrFail($groupId);

        $isMember = \App\Models\ForumGroupMember::where('group_id', $groupId)->where('user_id', Auth::id())->exists();
        if (!$isMember) {
            return redirect()->back()->with('error', 'Anda tidak tergabung dalam grup ini.');
        }

        // Cek batasan posting admin
        if ($group->only_admin_can_post) {
            $membership = ForumGroupMember::where('group_id', $group->id)
                ->where('user_id', $user->id)
                ->first();

            if (!$membership || $membership->role !== 'admin') {
                return back()->with('error', 'Hanya Admin / Guru yang dapat mengirim pesan di grup pengumuman ini.');
            }
        }

        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts('space_group_post:' . $user->id, 1)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn('space_group_post:' . $user->id);
            return back()->withInput()->with('error', "Anda mengirim postingan terlalu cepat. Harap tunggu {$seconds} detik lagi untuk mencegah spam.");
        }

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'content' => 'required|string|min:15',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,zip|max:10240',
            'voice_note' => 'nullable|file|mimes:mp3,wav,m4a,ogg,webm|max:10240',
            'poll_question' => 'nullable|string|max:255',
            'poll_options' => 'nullable|array',
            'poll_options.*' => 'nullable|string|max:255',
        ], [
            'content.min' => 'Isi postingan minimal 15 karakter.',
        ]);

        \Illuminate\Support\Facades\RateLimiter::hit('space_group_post:' . $user->id, 300); // 5 menit cooldown

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('forum/images', 'public');
        }

        $attachmentPath = null;
        $attachmentName = null;
        $fileCategory = null;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentName = $file->getClientOriginalName();
            $attachmentPath = $file->store('forum/attachments', 'public');
            $fileCategory = 'document';
        }

        if ($request->hasFile('voice_note')) {
            $file = $request->file('voice_note');
            $attachmentName = 'VoiceNote_' . now()->format('Ymd_His') . '.' . $file->getClientOriginalExtension();
            $attachmentPath = $file->store('forum/voicenotes', 'public');
            $fileCategory = 'voice_note';
        }

        $title = $validated['title'] ?? Str::limit(strip_tags($validated['content']), 50);

        $thread = ForumThread::create([
            'group_id' => $group->id,
            'user_id' => $user->id,
            'title' => $title,
            'content' => $validated['content'],
            'category' => $group->type === 'classroom' ? 'diskusi' : 'info',
            'image_path' => $imagePath,
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
            'file_category' => $fileCategory,
            'views_count' => 0,
        ]);

        // Buat Polling 3D jika opsi diisi
        if (!empty($validated['poll_question']) && !empty($validated['poll_options'])) {
            $filteredOptions = array_filter($validated['poll_options']);
            if (count($filteredOptions) >= 2) {
                $poll = \App\Models\ForumPoll::create([
                    'forum_thread_id' => $thread->id,
                    'question' => $validated['poll_question'],
                ]);

                foreach ($filteredOptions as $optText) {
                    \App\Models\ForumPollOption::create([
                        'forum_poll_id' => $poll->id,
                        'option_text' => $optText,
                        'votes_count' => 0,
                    ]);
                }
            }
        }

        return back()->with('success', 'Pesan berhasil dikirim ke squad ' . $group->name);
    }

    /**
     * Submit Vote Polling 3D
     */
    public function votePoll(Request $request, $pollId)
    {
        $user = Auth::user();
        $optionId = $request->input('option_id');

        $poll = \App\Models\ForumPoll::with(['options', 'thread'])->findOrFail($pollId);

        if ($poll->thread && $poll->thread->group_id) {
            $isMember = \App\Models\ForumGroupMember::where('group_id', $poll->thread->group_id)->where('user_id', Auth::id())->exists();
            if (!$isMember) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['error' => 'Anda tidak memiliki akses.'], 403);
                }
                return redirect()->back()->with('error', 'Anda tidak memiliki akses.');
            }
        }

        $option = \App\Models\ForumPollOption::where('forum_poll_id', $poll->id)->findOrFail($optionId);

        // Cek apakah sudah pernah voting
        $existingVote = \App\Models\ForumPollVote::where('forum_poll_id', $poll->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingVote) {
            // Kurangi count opsi lama jika ganti pilihan
            if ($existingVote->forum_poll_option_id !== $option->id) {
                \App\Models\ForumPollOption::where('id', $existingVote->forum_poll_option_id)->decrement('votes_count');
                $existingVote->update(['forum_poll_option_id' => $option->id]);
                $option->increment('votes_count');
            }
        } else {
            \App\Models\ForumPollVote::create([
                'forum_poll_id' => $poll->id,
                'forum_poll_option_id' => $option->id,
                'user_id' => $user->id,
            ]);
            $option->increment('votes_count');
        }

        if (request()->ajax() || request()->wantsJson()) {
            $poll->load('options');
            return response()->json([
                'success' => true,
                'message' => 'Pilihan suara Anda berhasil dicatat!',
                'options' => $poll->options->map(fn($opt) => [
                    'id' => $opt->id,
                    'option_text' => $opt->option_text,
                    'votes_count' => $opt->votes_count,
                ]),
                'total_votes' => $poll->options->sum('votes_count'),
            ]);
        }

        return back()->with('success', 'Pilihan suara Anda berhasil dicatat!');
    }

    public function create()
    {
        $user = Auth::user();
        $userGroups = ForumGroup::whereHas('members', fn($q) => $q->where('user_id', $user->id))->get();
        $categories = ForumThread::CATEGORIES;
        return view('mobile.space.create', compact('userGroups', 'categories'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts('forum_post:' . $user->id, 1)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn('forum_post:' . $user->id);
            return back()->withInput()->with('error', "Anda membuat postingan terlalu cepat. Harap tunggu {$seconds} detik lagi untuk mencegah spam.");
        }

        $validated = $request->validate([
            'title' => 'required|string|min:15|max:255',
            'content' => 'required|string|min:15',
            'category' => 'nullable|string',
            'group_id' => 'nullable|integer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,zip|max:10240',
        ], [
            'title.min' => 'Judul postingan minimal 15 karakter.',
            'content.min' => 'Isi postingan minimal 15 karakter.',
        ]);

        \Illuminate\Support\Facades\RateLimiter::hit('forum_post:' . $user->id, 300); // 5 menit cooldown

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('forum/images', 'public');
        }

        $attachmentPath = null;
        $attachmentName = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentName = $file->getClientOriginalName();
            $attachmentPath = $file->store('forum/attachments', 'public');
        }

        $thread = ForumThread::create([
            'user_id' => $user->id,
            'group_id' => $validated['group_id'] ?? null,
            'title' => $validated['title'],
            'content' => $validated['content'],
            'category' => $validated['category'] ?? 'diskusi',
            'image_path' => $imagePath,
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
            'views_count' => 0,
        ]);

        // Gamification: +15 Poin Reputasi
        \App\Models\ReputationLog::log($user->id, 15, 'forum', "Membuat postingan Pembda Space: {$thread->title}", $thread);

        return redirect()->route('mobile.space.show', $thread->id)
            ->with('success', 'Postingan berhasil dibuat di Pembda Space! (+15 Poin Reputasi)');
    }

    public function show($id)
    {
        $thread = ForumThread::with(['user', 'replies.user'])->findOrFail($id);
        $thread->increment('views_count');

        $isLiked = ForumLike::where('user_id', Auth::id())
            ->where('forum_thread_id', $thread->id)
            ->exists();

        return view('mobile.space.show', compact('thread', 'isLiked'));
    }

    public function reply(Request $request, $id)
    {
        $user = Auth::user();

        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts('forum_reply:' . $user->id, 1)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn('forum_reply:' . $user->id);
            return back()->withInput()->with('error', "Anda membalas terlalu cepat. Harap tunggu {$seconds} detik lagi.");
        }

        $validated = $request->validate([
            'content' => 'required|string|min:10',
        ], [
            'content.min' => 'Komentar balasan minimal 10 karakter.',
        ]);

        \Illuminate\Support\Facades\RateLimiter::hit('forum_reply:' . $user->id, 60); // 1 menit cooldown

        $thread = ForumThread::findOrFail($id);

        $reply = ForumReply::create([
            'forum_thread_id' => $thread->id,
            'user_id' => $user->id,
            'content' => $validated['content'],
        ]);

        // Gamification: +5 Poin Reputasi
        \App\Models\ReputationLog::log($user->id, 5, 'forum', "Mengomentari postingan Space: {$thread->title}", $reply);

        return back()->with('success', 'Komentar Anda berhasil dikirim! (+5 Poin Reputasi)');
    }

    public function like($id)
    {
        $userId = Auth::id();
        $existing = \App\Models\ForumLike::where('user_id', $userId)
            ->where('forum_thread_id', $id)
            ->first();

        $liked = false;
        if ($existing) {
            $existing->delete();
        } else {
            \App\Models\ForumLike::create([
                'user_id' => $userId,
                'forum_thread_id' => $id,
            ]);
            $liked = true;
        }

        $likesCount = \App\Models\ForumLike::where('forum_thread_id', $id)->count();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'liked' => $liked,
                'likes_count' => $likesCount,
            ]);
        }

        return back();
    }

    /**
     * Edit Postingan Thread (Khusus Penulis / Admin)
     */
    public function editThread($id)
    {
        $user = Auth::user();
        $thread = ForumThread::with('group')->findOrFail($id);

        $isOwner = ($thread->user_id === $user->id);
        $isAdmin = in_array($user->role, ['superadmin', 'admin_sekolah']);

        if (!$isOwner && !$isAdmin) {
            return redirect()->route('mobile.space.show', $id)->with('error', 'Anda tidak memiliki hak akses untuk mengedit postingan ini.');
        }

        $categories = ForumThread::CATEGORIES;
        return view('mobile.space.edit', compact('thread', 'categories'));
    }

    /**
     * Update Postingan Thread
     */
    public function updateThread(Request $request, $id)
    {
        $user = Auth::user();
        $thread = ForumThread::findOrFail($id);

        $isOwner = ($thread->user_id === $user->id);
        $isAdmin = in_array($user->role, ['superadmin', 'admin_sekolah']);

        if (!$isOwner && !$isAdmin) {
            return redirect()->route('mobile.space.show', $id)->with('error', 'Anda tidak memiliki hak akses untuk mengubah postingan ini.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|min:5',
            'category' => 'nullable|string',
        ]);

        $thread->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'category' => $validated['category'] ?? $thread->category,
        ]);

        return redirect()->route('mobile.space.show', $thread->id)->with('success', 'Postingan Anda berhasil diperbarui!');
    }

    /**
     * Hapus Postingan Thread (Khusus Penulis / Admin)
     */
    public function destroyThread($id)
    {
        $user = Auth::user();
        $thread = ForumThread::with(['poll', 'replies', 'likes'])->findOrFail($id);

        $isOwner = ($thread->user_id === $user->id);
        $isAdmin = in_array($user->role, ['superadmin', 'admin_sekolah']);

        if (!$isOwner && !$isAdmin) {
            return redirect()->back()->with('error', 'Anda tidak memiliki hak akses untuk menghapus postingan ini.');
        }

        // Hapus polling terkait jika ada
        if ($thread->poll) {
            \App\Models\ForumPollVote::where('forum_poll_id', $thread->poll->id)->delete();
            \App\Models\ForumPollOption::where('forum_poll_id', $thread->poll->id)->delete();
            $thread->poll->delete();
        }

        // Hapus balasan & likes
        $thread->replies()->delete();
        $thread->likes()->delete();
        $groupId = $thread->group_id;

        $thread->delete();

        if ($groupId) {
            return redirect()->route('mobile.space.group.show', $groupId)->with('success', 'Postingan berhasil dihapus!');
        }

        return redirect()->route('mobile.space.index')->with('success', 'Postingan berhasil dihapus!');
    }

    /**
     * Hapus Balasan Komentar (Khusus Penulis / Admin)
     */
    public function destroyReply($id)
    {
        $user = Auth::user();
        $reply = ForumReply::findOrFail($id);

        $isOwner = ($reply->user_id === $user->id);
        $isAdmin = in_array($user->role, ['superadmin', 'admin_sekolah']);

        if (!$isOwner && !$isAdmin) {
            return redirect()->back()->with('error', 'Anda tidak memiliki hak akses untuk menghapus komentar ini.');
        }

        $reply->delete();
        return redirect()->back()->with('success', 'Komentar berhasil dihapus!');
    }

    /**
     * Private Helper: Sinkronisasi Grup Otomatis Berbasis Data Akademik User
     */
    private function syncUserGroups($user)
    {
        if (!class_exists('\App\Models\ForumGroup')) {
            return;
        }

        // 1. Lobi Utama Pembda Space (Grup Default Umum)
        $lobbyGroup = ForumGroup::updateOrCreate(
            ['slug' => 'lobi-utama'],
            [
                'name' => 'Lobi Utama Pembda Space',
                'description' => 'Ruang obrolan umum & kolaborasi seluruh warga Yayasan PEMBDA',
                'icon' => '💬',
                'color' => 'purple',
                'type' => 'lobby',
                'is_official' => true,
            ]
        );

        ForumGroupMember::firstOrCreate([
            'group_id' => $lobbyGroup->id,
            'user_id' => $user->id,
        ], [
            'role' => 'member',
            'joined_at' => now(),
        ]);

        // 2. Grup Khusus Guru & Staf (Jika role guru / employee)
        if ($user->hasRole('guru') || $user->hasRole('admin') || Teacher::where('user_id', $user->id)->exists()) {
            $teacherGroup = ForumGroup::updateOrCreate(
                ['slug' => 'ruang-guru-pembda'],
                [
                    'name' => 'Diskusi Guru & Staf',
                    'description' => 'Komunitas & koordinasi khusus Tenaga Pendidik PEMBDA',
                    'icon' => '👨‍🏫',
                    'color' => 'blue',
                    'type' => 'broadcast',
                    'is_official' => true,
                ]
            );

            ForumGroupMember::firstOrCreate([
                'group_id' => $teacherGroup->id,
                'user_id' => $user->id,
            ], [
                'role' => 'member',
                'joined_at' => now(),
            ]);
        }

        // 3. Grup Rombel / Kelas Saya
        // Jika User adalah Siswa
        $student = Student::where('user_id', $user->id)->first();
        if ($student) {
            $classrooms = Classroom::where('is_active', true)
                ->where(function ($q) use ($student) {
                    $q->whereHas('students', fn($sq) => $sq->where('students.id', $student->id))
                      ->orWhere('id', $student->classroom_id ?? 0);
                })
                ->get();

            foreach ($classrooms as $cls) {
                $clsGroup = ForumGroup::firstOrCreate(
                    ['slug' => 'grup-kelas-' . Str::slug($cls->class_name)],
                    [
                        'name' => '🏫 Grup Rombel ' . $cls->class_name,
                        'classroom_id' => $cls->id,
                        'description' => 'Ruang komunikasi resmi Rombel ' . $cls->class_name,
                        'icon' => '🏫',
                        'color' => 'emerald',
                        'type' => 'classroom',
                        'is_official' => true,
                    ]
                );

                ForumGroupMember::firstOrCreate([
                    'group_id' => $clsGroup->id,
                    'user_id' => $user->id,
                ], [
                    'role' => 'member',
                    'joined_at' => now(),
                ]);
            }
        }

        // Jika User adalah Guru (Wali Kelas / Guru Pengajar)
        $teacher = Teacher::where('user_id', $user->id)->first();
        if ($teacher) {
            $homeroomClasses = Classroom::where('is_active', true)
                ->where('homeroom_teacher_id', $teacher->id)
                ->get();

            foreach ($homeroomClasses as $cls) {
                $clsGroup = ForumGroup::firstOrCreate(
                    ['slug' => 'grup-kelas-' . Str::slug($cls->class_name)],
                    [
                        'name' => '🏫 Grup Rombel ' . $cls->class_name,
                        'classroom_id' => $cls->id,
                        'description' => 'Ruang komunikasi resmi Rombel ' . $cls->class_name,
                        'icon' => '🏫',
                        'color' => 'emerald',
                        'type' => 'classroom',
                        'is_official' => true,
                    ]
                );

                ForumGroupMember::firstOrCreate([
                    'group_id' => $clsGroup->id,
                    'user_id' => $user->id,
                ], [
                    'role' => 'admin',
                    'joined_at' => now(),
                ]);
            }
        }
    }
}
