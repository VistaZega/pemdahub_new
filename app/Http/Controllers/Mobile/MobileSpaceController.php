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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class MobileSpaceController extends Controller
{
    /**
     * Tampilan Utama Pembda Space Groups (WA Groups Style)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $groupFilter = $request->input('filter', 'semua');
        $search = $request->input('search');

        // Otomatis sinkronkan grup pengguna berbasis peran & Rombel
        $this->syncUserGroups($user);

        // Ambil grup-grup di mana pengguna terdaftar sebagai anggota
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

        $groups = $groupsQuery->get()->sortByDesc(function ($g) {
            return $g->latestThread?->created_at ?? $g->created_at;
        })->values();

        // Feed postingan umum jika diinginkan
        $recentThreads = ForumThread::with(['user', 'replies'])
            ->withCount('replies', 'likes')
            ->latest()
            ->take(10)
            ->get();

        return view('mobile.space.index', compact('groups', 'groupFilter', 'search', 'recentThreads'));
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
            // Otomatis gabungkan jika grup resmi
            $membership = ForumGroupMember::create([
                'group_id' => $group->id,
                'user_id' => $user->id,
                'role' => 'member',
                'joined_at' => now(),
            ]);
        }

        // Update last_read_at
        $membership->update(['last_read_at' => now()]);

        // Ambil postingan / pesan di dalam grup ini
        $threads = ForumThread::where('group_id', $group->id)
            ->with(['user', 'replies.user'])
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

        // Cek batasan posting admin
        if ($group->only_admin_can_post) {
            $membership = ForumGroupMember::where('group_id', $group->id)
                ->where('user_id', $user->id)
                ->first();

            if (!$membership || $membership->role !== 'admin') {
                return back()->with('error', 'Hanya Admin / Guru yang dapat mengirim pesan di grup pengumuman ini.');
            }
        }

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'content' => 'required|string',
        ]);

        $title = $validated['title'] ?? Str::limit(strip_tags($validated['content']), 50);

        $thread = ForumThread::create([
            'group_id' => $group->id,
            'user_id' => $user->id,
            'title' => $title,
            'content' => $validated['content'],
            'category' => $group->type === 'classroom' ? 'diskusi' : 'info',
            'views_count' => 0,
        ]);

        return back()->with('success', 'Pesan berhasil dikirim ke grup ' . $group->name);
    }

    public function create()
    {
        $user = Auth::user();
        $userGroups = ForumGroup::whereHas('members', fn($q) => $q->where('user_id', $user->id))->get();
        return view('mobile.space.create', compact('userGroups'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'nullable|string',
            'group_id' => 'nullable|integer',
        ]);

        $thread = ForumThread::create([
            'user_id' => Auth::id(),
            'group_id' => $validated['group_id'] ?? null,
            'title' => $validated['title'],
            'content' => $validated['content'],
            'category' => $validated['category'] ?? 'diskusi',
            'views_count' => 0,
        ]);

        return redirect()->route('mobile.space.show', $thread->id)
            ->with('success', 'Postingan berhasil dibuat di Pembda Space!');
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
        $request->validate([
            'content' => 'required|string',
        ]);

        $thread = ForumThread::findOrFail($id);

        ForumReply::create([
            'forum_thread_id' => $thread->id,
            'user_id' => Auth::id(),
            'content' => $request->input('content'),
        ]);

        return back()->with('success', 'Komentar Anda berhasil dikirim.');
    }

    public function like($id)
    {
        $userId = Auth::id();
        $existing = ForumLike::where('user_id', $userId)
            ->where('forum_thread_id', $id)
            ->first();

        if ($existing) {
            $existing->delete();
        } else {
            ForumLike::create([
                'user_id' => $userId,
                'forum_thread_id' => $id,
            ]);
        }

        return back();
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
        $lobbyGroup = ForumGroup::firstOrCreate(
            ['slug' => 'lobi-utama'],
            [
                'name' => '💬 Lobi Utama Pembda Space',
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
            $teacherGroup = ForumGroup::firstOrCreate(
                ['slug' => 'ruang-guru-pembda'],
                [
                    'name' => '👨‍🏫 Ruang Diskusi Guru & Staf',
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
        $activeAY = \App\Models\AcademicYear::where('is_active', true)->first();

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
            // Kelas Perwalian
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
                    'role' => 'admin', // Wali Kelas otomatis Admin Grup
                    'joined_at' => now(),
                ]);
            }
        }
    }
}
