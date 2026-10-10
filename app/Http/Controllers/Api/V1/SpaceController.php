<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ForumThread;
use App\Models\ForumReply;
use App\Models\ForumLike;
use App\Models\ForumReaction;
use App\Models\ForumGroup;
use App\Models\ForumGroupMember;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\RateLimiter;

class SpaceController extends Controller
{
    /**
     * Get user's groups
     */
    public function groups(Request $request)
    {
        $user = $request->user();
        $groupFilter = $request->input('filter', 'semua');
        $search = $request->input('search');

        $this->syncUserGroups($user);

        $groupsQuery = ForumGroup::whereHas('members', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->with(['latestThread.user']);

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
                    $grp->calculated_member_count = $pivotCount + 1;
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

        return response()->json($groups);
    }

    /**
     * Show a specific group and its threads
     */
    public function showGroup(Request $request, $groupId)
    {
        $user = $request->user();
        $group = ForumGroup::findOrFail($groupId);

        $membership = ForumGroupMember::where('group_id', $group->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$membership) {
            return response()->json(['message' => 'Unauthorized access to group'], 403);
        }

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
                $group->calculated_member_count = $pivotCount + 1;
            } else {
                $group->calculated_member_count = $group->members()->count();
            }
        } else {
            $group->calculated_member_count = $group->members()->count();
        }

        $membership->update(['last_read_at' => now()]);

        $threads = ForumThread::where('group_id', $group->id)
            ->with([
                'user:id,name,username,role,photo',
                'poll.options',
            ])
            ->withCount('replies', 'likes')
            ->orderBy('is_pinned', 'desc')
            ->latest()
            ->paginate(15);

        return response()->json([
            'group' => $group,
            'membership' => $membership,
            'threads' => $threads
        ]);
    }

    /**
     * Store a new thread in a group
     */
    public function storeGroupThread(Request $request, $groupId)
    {
        $user = $request->user();
        $group = ForumGroup::findOrFail($groupId);

        $membership = ForumGroupMember::where('group_id', $group->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$membership) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($group->only_admin_can_post && $membership->role !== 'admin') {
            return response()->json(['message' => 'Only admins can post in this group'], 403);
        }

        $isPrivileged = ($user->isSuperAdmin() || $user->isAdminSekolah() || $user->isGuru());

        if (!$isPrivileged && RateLimiter::tooManyAttempts('space_group_post:' . $user->id, 2)) {
            $seconds = RateLimiter::availableIn('space_group_post:' . $user->id);
            return response()->json(['message' => "Too many requests. Please wait {$seconds} seconds."], 429);
        }

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'content' => 'required|string|min:3',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,zip|max:10240',
            'voice_note' => 'nullable|file|mimes:mp3,wav,m4a,ogg,webm|max:10240',
            'poll_question' => 'nullable|string|max:255',
            'poll_options' => 'nullable|array',
            'poll_options.*' => 'nullable|string|max:255',
        ]);

        if (!$isPrivileged) {
            RateLimiter::hit('space_group_post:' . $user->id, 30);
        }

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

        return response()->json([
            'message' => 'Post created successfully',
            'thread' => $thread->load('user', 'poll.options')
        ]);
    }

    /**
     * Get public threads
     */
    public function threads(Request $request)
    {
        $category = $request->input('category');
        $search = $request->input('search');

        $threadsQuery = ForumThread::whereNull('group_id')
            ->with(['user:id,name,username,role,photo'])
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

        return response()->json($threads);
    }

    /**
     * Create a public thread
     */
    public function storeThread(Request $request)
    {
        $user = $request->user();

        $content = $request->input('content');
        $title = $request->input('title');
        $category = $request->input('category') ?? 'diskusi';

        if (empty($content)) {
            return response()->json(['message' => 'Konten postingan tidak boleh kosong.'], 422);
        }

        if (empty($title)) {
            $title = Str::limit(strip_tags($content), 60);
        }

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
            'group_id' => $request->input('group_id') ?: null,
            'title' => $title,
            'content' => $content,
            'category' => $category,
            'image_path' => $imagePath,
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
            'views_count' => 0,
        ]);

        try {
            \App\Models\ReputationLog::log($user->id, 15, 'forum', "Membuat postingan Pembda Space: {$thread->title}", 'App\Models\ForumThread', $thread->id);
        } catch (\Throwable $e) {
            // Ignore reputation log error
        }

        return response()->json([
            'success' => true,
            'message' => 'Postingan berhasil diterbitkan!',
            'thread' => $thread->load('user')
        ]);
    }


    public function showThread(Request $request, $id)
    {
        $thread = ForumThread::with([
            'user:id,name,username,role,photo',
            'replies' => function($q) {
                $q->with('user:id,name,username,role,photo')->latest();
            },
            'poll.options'
        ])->withCount('likes', 'replies')->findOrFail($id);

        $thread->increment('views_count');

        $isLiked = ForumLike::where('user_id', $request->user()->id)
            ->where('forum_thread_id', $thread->id)
            ->exists();

        return response()->json([
            'thread' => $thread,
            'is_liked' => $isLiked
        ]);
    }

    public function updateThread(Request $request, $id)
    {
        $user = $request->user();
        $thread = ForumThread::findOrFail($id);

        if ($thread->user_id !== $user->id && !in_array($user->role, ['superadmin', 'admin_sekolah'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
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

        return response()->json(['message' => 'Post updated successfully', 'thread' => $thread]);
    }

    public function destroyThread(Request $request, $id)
    {
        $user = $request->user();
        $thread = ForumThread::findOrFail($id);

        if ($thread->user_id !== $user->id && !in_array($user->role, ['superadmin', 'admin_sekolah'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $thread->delete();
        return response()->json(['message' => 'Post deleted successfully']);
    }

    public function storeReply(Request $request, $threadId)
    {
        $user = $request->user();
        $thread = ForumThread::findOrFail($threadId);

        if (RateLimiter::tooManyAttempts('forum_reply:' . $user->id, 1)) {
            $seconds = RateLimiter::availableIn('forum_reply:' . $user->id);
            return response()->json(['message' => "Too many requests. Please wait {$seconds} seconds."], 429);
        }

        $validated = $request->validate([
            'content' => 'required|string|min:10',
            'voice_note' => 'nullable|file|mimes:mp3,wav,m4a,ogg,webm|max:10240',
        ]);

        RateLimiter::hit('forum_reply:' . $user->id, 60);

        $voiceNotePath = null;
        if ($request->hasFile('voice_note')) {
            $voiceNotePath = $request->file('voice_note')->store('forum/voicenotes', 'public');
        }

        $reply = ForumReply::create([
            'forum_thread_id' => $thread->id,
            'user_id' => $user->id,
            'content' => $validated['content'],
            'voice_note_path' => $voiceNotePath,
        ]);

        \App\Models\ReputationLog::log($user->id, 5, 'forum', "Mengomentari postingan Space: {$thread->title}", 'App\Models\ForumReply', $reply->id);

        return response()->json([
            'message' => 'Reply created successfully',
            'reply' => $reply->load('user:id,name,username,role,photo')
        ]);
    }

    public function destroyReply(Request $request, $replyId)
    {
        $user = $request->user();
        $reply = ForumReply::findOrFail($replyId);

        if ($reply->user_id !== $user->id && !in_array($user->role, ['superadmin', 'admin_sekolah'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $reply->delete();
        return response()->json(['message' => 'Reply deleted successfully']);
    }

    public function toggleLike(Request $request, $threadId)
    {
        $user = $request->user();
        $thread = ForumThread::findOrFail($threadId);

        $existing = ForumLike::where('user_id', $user->id)
            ->where('forum_thread_id', $thread->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $liked = false;
        } else {
            ForumLike::create([
                'user_id' => $user->id,
                'forum_thread_id' => $thread->id,
            ]);
            $liked = true;
        }

        $likesCount = ForumLike::where('forum_thread_id', $thread->id)->count();

        return response()->json([
            'message' => 'Like toggled',
            'liked' => $liked,
            'likes_count' => $likesCount
        ]);
    }

    public function toggleReaction(Request $request, $threadId)
    {
        $user = $request->user();
        $thread = ForumThread::findOrFail($threadId);
        
        $request->validate([
            'emoji' => 'required|string|max:10'
        ]);

        $emoji = $request->emoji;

        $existing = ForumReaction::where('user_id', $user->id)
            ->where('forum_thread_id', $thread->id)
            ->where('emoji', $emoji)
            ->first();

        if ($existing) {
            $existing->delete();
            $reacted = false;
        } else {
            ForumReaction::create([
                'user_id' => $user->id,
                'forum_thread_id' => $thread->id,
                'emoji' => $emoji
            ]);
            $reacted = true;
        }

        return response()->json([
            'message' => 'Reaction toggled',
            'reacted' => $reacted
        ]);
    }

    public function toggleReplyReaction(Request $request, $replyId)
    {
        $user = $request->user();
        $reply = ForumReply::findOrFail($replyId);
        
        $request->validate([
            'emoji' => 'required|string|max:10'
        ]);

        $emoji = $request->emoji;

        $existing = ForumReaction::where('user_id', $user->id)
            ->where('forum_reply_id', $reply->id)
            ->where('emoji', $emoji)
            ->first();

        if ($existing) {
            $existing->delete();
            $reacted = false;
        } else {
            ForumReaction::create([
                'user_id' => $user->id,
                'forum_reply_id' => $reply->id,
                'emoji' => $emoji
            ]);
            $reacted = true;
        }

        return response()->json([
            'message' => 'Reply reaction toggled',
            'reacted' => $reacted
        ]);
    }

    public function votePoll(Request $request, $pollId)
    {
        $user = $request->user();
        $request->validate([
            'option_id' => 'required|exists:forum_poll_options,id'
        ]);

        $poll = \App\Models\ForumPoll::with('options')->findOrFail($pollId);
        $optionId = $request->option_id;

        $existingVote = \App\Models\ForumPollVote::where('forum_poll_id', $poll->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingVote) {
            if ($existingVote->forum_poll_option_id != $optionId) {
                \App\Models\ForumPollOption::where('id', $existingVote->forum_poll_option_id)->decrement('votes_count');
                $existingVote->update(['forum_poll_option_id' => $optionId]);
                \App\Models\ForumPollOption::where('id', $optionId)->increment('votes_count');
            }
        } else {
            \App\Models\ForumPollVote::create([
                'forum_poll_id' => $poll->id,
                'forum_poll_option_id' => $optionId,
                'user_id' => $user->id,
            ]);
            \App\Models\ForumPollOption::where('id', $optionId)->increment('votes_count');
        }

        $poll->load('options');

        return response()->json([
            'message' => 'Vote recorded successfully',
            'poll' => $poll
        ]);
    }

    private function syncUserGroups($user)
    {
        if (!class_exists('\App\Models\ForumGroup')) {
            return;
        }

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
