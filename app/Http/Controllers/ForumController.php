<?php

namespace App\Http\Controllers;

use App\Models\ForumThread;
use App\Models\ForumReply;
use App\Models\ForumLike;
use App\Models\ForumMember;
use App\Models\ReputationLog;
use App\Models\CbtExamResult;
use App\Models\Badge;
use App\Models\ForumReaction;
use App\Models\ForumPoll;
use App\Models\ForumPollOption;
use App\Models\ForumPollVote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ForumController extends Controller
{
    /**
     * Index / Board View
     */
    public function index(Request $request)
    {
        $category = $request->get('category');
        $search = $request->get('search');
        $user = Auth::user();

        $query = ForumThread::with(['user', 'replies', 'likes', 'members', 'reactions', 'poll'])
            ->pinnedFirst();

        // Scope by category
        if ($category && array_key_exists($category, ForumThread::CATEGORIES)) {
            $query->where('category', $category);
        }

        // Scope by search
        if ($search) {
            $query->search($search);
        }

        $threads = $query->paginate(15)->withQueryString();

        // Get category count for channel badges
        $counts = ForumThread::select('category', DB::raw('count(*) as total'))
            ->groupBy('category')
            ->pluck('total', 'category')
            ->toArray();

        // Channel groups for sidebar
        $channelGroups = ForumThread::CHANNEL_GROUPS;

        // Fetch top 5 students for leaderboard
        $topStudents = \App\Models\Reputation::with(['user.student.classroom', 'user.badges'])
            ->whereHas('user', function($q) {
                $q->where('role', 'siswa');
            })
            ->orderBy('total_points', 'desc')
            ->take(5)
            ->get();

        // Fetch active collaborations seeking members
        $activeCollabs = ForumThread::whereIn('category', ['project_idea', 'committee'])
            ->where('status', 'seeking_members')
            ->with(['user', 'likes', 'replies'])
            ->latest()
            ->take(3)
            ->get();

        // Total threads count
        $totalThreads = ForumThread::count();

        // Online users approximation (users active in last 15 min)
        try {
            $onlineCount = \App\Models\User::where('last_login_at', '>=', now()->subMinutes(15))->count();
        } catch (\Exception $e) {
            $onlineCount = 0;
        }

        return view('forum.index', compact(
            'threads', 'counts', 'category', 'search', 
            'topStudents', 'activeCollabs', 'channelGroups',
            'totalThreads', 'onlineCount'
        ));
    }

    /**
     * Create Thread View
     */
    public function create()
    {
        $user = Auth::user();
        $badges = $user->badges()->get();
        
        $cbtResults = collect([]);
        if ($user->isSiswa() && $user->student) {
            $cbtResults = CbtExamResult::where('student_id', $user->student->id)
                ->with('exam')
                ->latest()
                ->take(10)
                ->get();
        }

        return view('forum.create', compact('badges', 'cbtResults'));
    }

    /**
     * Store New Thread
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Info/Announcement category is restricted to teachers/admins
        $allowedCategories = array_keys(ForumThread::CATEGORIES);
        if (!$user->isSuperAdmin() && !$user->isAdminSekolah() && !$user->isGuru()) {
            $allowedCategories = array_diff($allowedCategories, ['info']);
        }

        $rules = [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'required|string|in:' . implode(',', $allowedCategories),
            'image' => 'nullable|image|max:5120', // 5MB limit
            'attachment' => 'nullable|file|max:10240', // 10MB limit
            
            // Collaboration & Charity Specifics
            'charity_target_amount' => 'nullable|numeric|min:0',
            'charity_target_volunteers' => 'nullable|integer|min:0',
            'recruitment_enabled' => 'nullable|boolean',
            'reference_type' => 'nullable|string',
            'reference_id' => 'nullable|integer',
        ];

        $validated = $request->validate($rules);

        DB::beginTransaction();
        try {
            $threadData = [
                'user_id' => $user->id,
                'title' => $validated['title'],
                'content' => $validated['content'],
                'category' => $validated['category'],
                'status' => ($validated['category'] === 'project_idea' || $validated['category'] === 'committee' || $validated['category'] === 'charity')
                    ? 'seeking_members' 
                    : 'seeking_members',
            ];

            // Handle uploads
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('forum', 'public');
                $threadData['image_path'] = $path;
            }

            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $path = $file->store('forum_attachments', 'public');
                $threadData['attachment_path'] = $path;
                $threadData['attachment_name'] = $file->getClientOriginalName();
            }

            // Reference linking (Badges / Grades for visual & portfolio categories)
            $perfCategories = ['performance', 'art_gallery', 'talent', 'portfolio'];
            if (in_array($validated['category'], $perfCategories) && !empty($validated['reference_type']) && !empty($validated['reference_id'])) {
                if ($validated['reference_type'] === 'badge') {
                    $threadData['reference_type'] = Badge::class;
                    $threadData['reference_id'] = $validated['reference_id'];
                } elseif ($validated['reference_type'] === 'grade') {
                    $threadData['reference_type'] = CbtExamResult::class;
                    $threadData['reference_id'] = $validated['reference_id'];
                }
            }

            // Gaming data
            if ($validated['category'] === 'gaming') {
                $threadData['game_name'] = $request->input('game_name');
                $threadData['game_room_code'] = $request->input('game_room_code');
            }

            // Sharing / Bank File category data
            if ($validated['category'] === 'sharing') {
                $threadData['file_category'] = $request->input('file_category');
            }

            // Charity data
            if ($validated['category'] === 'charity') {
                $threadData['charity_target_amount'] = $validated['charity_target_amount'] ?? null;
                $threadData['charity_target_volunteers'] = $validated['charity_target_volunteers'] ?? null;
                $threadData['charity_current_amount'] = 0;
            }

            $thread = ForumThread::create($threadData);

            // Gamification hook: +15 points for creating thread
            ReputationLog::log($user->id, 15, 'forum', "Membuat thread forum: {$thread->title}", $thread);

            DB::commit();

            return redirect()->route('forum.show', $thread)
                ->with('success', 'Topik diskusi berhasil diterbitkan! (+15 Poin Reputasi)');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal memposting: ' . $e->getMessage());
        }
    }

    /**
     * Show Thread Details
     */
    public function show(ForumThread $thread)
    {
        $thread->increment('views_count');
        $thread->load(['user', 'replies.user', 'replies.parent', 'replies.reactions', 'likes', 'members.user', 'reactions', 'poll.options.votes']);

        // Build the performance reference view representation if linked
        $perfCard = null;
        $perfCategories = ['performance', 'art_gallery', 'talent', 'portfolio'];
        if (in_array($thread->category, $perfCategories) && $thread->reference_type) {
            if ($thread->reference_type === Badge::class) {
                $perfCard = Badge::find($thread->reference_id);
            } elseif ($thread->reference_type === CbtExamResult::class) {
                $perfCard = CbtExamResult::with('exam')->find($thread->reference_id);
            }
        }

        // Get reaction counts for the thread
        $threadReactions = $thread->getReactionCounts();

        return view('forum.show', compact('thread', 'perfCard', 'threadReactions'));
    }

    /**
     * Reply to a Thread
     */
    public function reply(Request $request, ForumThread $thread)
    {
        $user = Auth::user();
        if ($thread->is_locked) {
            return back()->with('error', 'Topik diskusi ini dikunci.');
        }

        $validated = $request->validate([
            'content' => 'required_without:voice_note|string|max:5000|nullable',
            'voice_note' => 'nullable|file|mimes:webm,mp3,mp4,m4a,ogg,wav|max:5120',
            'parent_reply_id' => 'nullable|exists:forum_replies,id',
        ]);

        DB::beginTransaction();
        try {
            $voiceNotePath = null;
            if ($request->hasFile('voice_note')) {
                $voiceNotePath = $request->file('voice_note')->store('forum_voice_notes', 'public');
            }

            $reply = ForumReply::create([
                'forum_thread_id' => $thread->id,
                'user_id' => $user->id,
                'content' => $validated['content'] ?? '',
                'voice_note_path' => $voiceNotePath,
                'parent_reply_id' => $validated['parent_reply_id'] ?? null,
            ]);

            // Gamification hook: +5 points for replying
            ReputationLog::log($user->id, 5, 'forum', "Mengomentari diskusi: {$thread->title}", $reply);

            DB::commit();

            return back()->with('success', 'Komentar ditambahkan! (+5 Poin Reputasi)');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memposting komentar: ' . $e->getMessage());
        }
    }

    /**
     * Like / Upvote Thread
     */
    public function like(Request $request, ForumThread $thread)
    {
        $user = Auth::user();
        $author = $thread->user;

        $existingLike = ForumLike::where('user_id', $user->id)
            ->where('forum_thread_id', $thread->id)
            ->first();

        DB::beginTransaction();
        try {
            if ($existingLike) {
                // Reverse points
                ReputationLog::removeLog($user->id, ForumLike::class, $existingLike->id);
                if ($author->id !== $user->id) {
                    ReputationLog::removeLog($author->id, ForumThread::class, $thread->id);
                }

                $existingLike->delete();
                $isLiked = false;
                $message = 'Upvote dibatalkan.';
            } else {
                $like = ForumLike::create([
                    'user_id' => $user->id,
                    'forum_thread_id' => $thread->id,
                ]);

                // Liker gets +2 points
                ReputationLog::log($user->id, 2, 'forum_like', "Menyukai topik: {$thread->title}", $like);

                // Author gets +10 points (if not liking own thread)
                if ($author->id !== $user->id) {
                    ReputationLog::log($author->id, 10, 'forum_upvote', "Mendapat upvote pada topik: {$thread->title}", $thread);
                }

                $isLiked = true;
                $message = 'Topik berhasil diupvote! (+2 Poin Liker / +10 Poin Penulis)';
            }
            DB::commit();

            return response()->json([
                'success' => true,
                'liked' => $isLiked,
                'likes_count' => $thread->likes()->count(),
                'message' => $message
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Accept Best Reply / Answer
     */
    public function acceptReply(ForumReply $reply)
    {
        $user = Auth::user();
        $thread = $reply->thread;

        // Verify authority: only thread author or Admin/Teacher can accept
        if ($user->id !== $thread->user_id && !$user->isSuperAdmin() && !$user->isGuru()) {
            abort(403, 'Unauthorized');
        }

        DB::beginTransaction();
        try {
            // Unaccept any previous accepted reply for this thread
            ForumReply::where('forum_thread_id', $thread->id)
                ->where('is_accepted', true)
                ->update(['is_accepted' => false]);

            $reply->update(['is_accepted' => true]);

            // Recipient gets +15 points for best answer
            ReputationLog::log($reply->user_id, 15, 'forum_best_answer', "Komentar terpilih sebagai Jawaban Terbaik di topik: {$thread->title}", $reply);

            DB::commit();

            return back()->with('success', 'Telah ditetapkan sebagai Jawaban Terbaik! (+15 Poin untuk Penulis Komentar)');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses: ' . $e->getMessage());
        }
    }

    /**
     * Join Project / Committee / Volunteer Charity
     */
    public function join(Request $request, ForumThread $thread)
    {
        $user = Auth::user();

        if ($thread->status === 'completed' || $thread->is_locked) {
            return back()->with('error', 'Aktivitas kolaborasi ini sudah selesai atau ditutup.');
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            // Check if already applied
            $existing = ForumMember::where('forum_thread_id', $thread->id)
                ->where('user_id', $user->id)
                ->first();

            if ($existing) {
                return back()->with('error', 'Anda sudah mengajukan pendaftaran sebelumnya.');
            }

            $member = ForumMember::create([
                'forum_thread_id' => $thread->id,
                'user_id' => $user->id,
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
            ]);

            // Immediate incentive for joining: +10 points to member
            ReputationLog::log($user->id, 10, 'forum_join', "Mendaftar dalam tim/kegiatan: {$thread->title}", $member);

            DB::commit();

            return back()->with('success', 'Permintaan pendaftaran berhasil dikirim! (+10 Poin Reputasi)');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mendaftar: ' . $e->getMessage());
        }
    }

    /**
     * Approve Member Registration
     */
    public function approveMember(ForumMember $member)
    {
        $user = Auth::user();
        $thread = $member->thread;

        // Author or teacher/admin can approve
        if ($user->id !== $thread->user_id && !$user->isSuperAdmin() && !$user->isGuru()) {
            abort(403, 'Unauthorized');
        }

        DB::beginTransaction();
        try {
            $member->update(['status' => 'approved']);

            // Author gets +5 points for approving a team member
            ReputationLog::log($thread->user_id, 5, 'forum_team_approve', "Menyetujui anggota tim baru pada: {$thread->title}", $member);

            DB::commit();

            return back()->with('success', 'Anggota tim berhasil disetujui! (+5 Poin Penulis)');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyetujui: ' . $e->getMessage());
        }
    }

    /**
     * Reject Member Registration
     */
    public function rejectMember(ForumMember $member)
    {
        $user = Auth::user();
        $thread = $member->thread;

        if ($user->id !== $thread->user_id && !$user->isSuperAdmin() && !$user->isGuru()) {
            abort(403, 'Unauthorized');
        }

        DB::beginTransaction();
        try {
            $member->update(['status' => 'rejected']);

            // Remove the +10 points the applicant originally got for joining
            ReputationLog::removeLog($member->user_id, ForumMember::class, $member->id);

            DB::commit();

            return back()->with('success', 'Pendaftaran anggota ditolak.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses penolakan: ' . $e->getMessage());
        }
    }

    /**
     * Update Collaboration Lifecycle Status
     */
    public function updateStatus(Request $request, ForumThread $thread)
    {
        $user = Auth::user();

        if ($user->id !== $thread->user_id && !$user->isSuperAdmin() && !$user->isGuru()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'status' => 'required|string|in:seeking_members,active,completed',
        ]);

        $oldStatus = $thread->status;
        $newStatus = $validated['status'];

        DB::beginTransaction();
        try {
            $thread->update(['status' => $newStatus]);

            // Completion reward: +50 points to creator and approved members
            if ($newStatus === 'completed' && $oldStatus !== 'completed') {
                // Award points to creator
                ReputationLog::log($thread->user_id, 50, 'forum_completed_project', "Menyelesaikan kolaborasi proyek/kegiatan: {$thread->title}", $thread);
                
                // Award points to all approved team members
                $approvedMembers = $thread->approvedMembers()->get();
                foreach ($approvedMembers as $member) {
                    ReputationLog::log($member->user_id, 50, 'forum_completed_project', "Menyelesaikan kolaborasi proyek/kegiatan: {$thread->title}", $thread);
                }
            }

            DB::commit();

            return back()->with('success', 'Status kolaborasi diperbarui!' . ($newStatus === 'completed' ? ' (+50 Poin Bonus Penyelesaian dibagikan!)' : ''));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memperbarui status: ' . $e->getMessage());
        }
    }

    /**
     * Update Donation progress manually (Charity categories)
     */
    public function donate(Request $request, ForumThread $thread)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        DB::beginTransaction();
        try {
            $thread->increment('charity_current_amount', $validated['amount']);

            // Reward donor: +10 points for donating
            ReputationLog::log($user->id, 10, 'forum_charity_donation', "Berdonasi pada aksi sosial: {$thread->title}", $thread);

            DB::commit();

            return back()->with('success', 'Terima kasih atas partisipasi donasi Anda! (+10 Poin Reputasi)');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses donasi: ' . $e->getMessage());
        }
    }

    /**
     * Edit Thread View
     */
    public function edit(ForumThread $thread)
    {
        $user = Auth::user();

        if ($user->id !== $thread->user_id && !$user->isSuperAdmin() && !$user->isAdminSekolah() && !$user->isGuru()) {
            abort(403, 'Unauthorized');
        }

        $badges = $user->badges()->get();
        
        $cbtResults = collect([]);
        if ($user->isSiswa() && $user->student) {
            $cbtResults = CbtExamResult::where('student_id', $user->student->id)
                ->with('exam')
                ->latest()
                ->take(10)
                ->get();
        }

        return view('forum.edit', compact('thread', 'badges', 'cbtResults'));
    }

    /**
     * Update Thread
     */
    public function update(Request $request, ForumThread $thread)
    {
        $user = Auth::user();

        if ($user->id !== $thread->user_id && !$user->isSuperAdmin() && !$user->isAdminSekolah() && !$user->isGuru()) {
            abort(403, 'Unauthorized');
        }

        $allowedCategories = array_keys(ForumThread::CATEGORIES);
        if (!$user->isSuperAdmin() && !$user->isAdminSekolah() && !$user->isGuru()) {
            $allowedCategories = array_diff($allowedCategories, ['info']);
        }

        $rules = [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'required|string|in:' . implode(',', $allowedCategories),
            'image' => 'nullable|image|max:5120', // 5MB limit
            'attachment' => 'nullable|file|max:10240', // 10MB limit
            
            // Collaboration & Charity Specifics
            'charity_target_amount' => 'nullable|numeric|min:0',
            'charity_target_volunteers' => 'nullable|integer|min:0',
            'recruitment_enabled' => 'nullable|boolean',
            'reference_type' => 'nullable|string',
            'reference_id' => 'nullable|integer',
        ];

        $validated = $request->validate($rules);

        DB::beginTransaction();
        try {
            $thread->update([
                'title' => $validated['title'],
                'content' => $validated['content'],
                'category' => $validated['category'],
            ]);

            // Handle uploads
            if ($request->hasFile('image')) {
                // Delete old image
                if ($thread->image_path) {
                    \Storage::disk('public')->delete($thread->image_path);
                }
                $path = $request->file('image')->store('forum', 'public');
                $thread->update(['image_path' => $path]);
            }

            if ($request->hasFile('attachment')) {
                // Delete old attachment
                if ($thread->attachment_path) {
                    \Storage::disk('public')->delete($thread->attachment_path);
                }
                $file = $request->file('attachment');
                $path = $file->store('forum_attachments', 'public');
                $thread->update([
                    'attachment_path' => $path,
                    'attachment_name' => $file->getClientOriginalName()
                ]);
            }

            // Reference linking (Badges / Grades)
            $perfCategories = ['performance', 'art_gallery', 'talent', 'portfolio'];
            if (in_array($validated['category'], $perfCategories) && !empty($validated['reference_type']) && !empty($validated['reference_id'])) {
                if ($validated['reference_type'] === 'badge') {
                    $thread->update([
                        'reference_type' => \App\Models\Badge::class,
                        'reference_id' => $validated['reference_id']
                    ]);
                } elseif ($validated['reference_type'] === 'grade') {
                    $thread->update([
                        'reference_type' => \App\Models\CbtExamResult::class,
                        'reference_id' => $validated['reference_id']
                    ]);
                }
            } else {
                $thread->update([
                    'reference_type' => null,
                    'reference_id' => null
                ]);
            }

            // Gaming data
            if ($validated['category'] === 'gaming') {
                $thread->update([
                    'game_name' => $request->input('game_name'),
                    'game_room_code' => $request->input('game_room_code'),
                ]);
            }

            // Sharing / Bank File category data
            if ($validated['category'] === 'sharing') {
                $thread->update([
                    'file_category' => $request->input('file_category')
                ]);
            }

            // Charity data
            if ($validated['category'] === 'charity') {
                $thread->update([
                    'charity_target_amount' => $validated['charity_target_amount'] ?? null,
                    'charity_target_volunteers' => $validated['charity_target_volunteers'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()->route('forum.show', $thread)
                ->with('success', 'Topik diskusi berhasil diperbarui!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal memperbarui postingan: ' . $e->getMessage());
        }
    }

    /**
     * Delete Thread
     */
    public function destroy(ForumThread $thread)
    {
        $user = Auth::user();

        if ($user->id !== $thread->user_id && !$user->isSuperAdmin() && !$user->isAdminSekolah() && !$user->isGuru()) {
            abort(403, 'Unauthorized');
        }

        DB::beginTransaction();
        try {
            // Revert creator points
            ReputationLog::removeLog($thread->user_id, ForumThread::class, $thread->id);

            // Revert all reply and like logs for this thread
            $replies = $thread->replies()->get();
            foreach ($replies as $reply) {
                ReputationLog::removeLog($reply->user_id, ForumReply::class, $reply->id);
            }

            $likes = $thread->likes()->get();
            foreach ($likes as $like) {
                ReputationLog::removeLog($like->user_id, ForumLike::class, $like->id);
            }

            // Remove files
            if ($thread->image_path) {
                Storage::disk('public')->delete($thread->image_path);
            }
            if ($thread->attachment_path) {
                Storage::disk('public')->delete($thread->attachment_path);
            }

            $thread->delete();

            DB::commit();

            return redirect()->route('forum.index')
                ->with('success', 'Topik diskusi berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus topik: ' . $e->getMessage());
        }
    }

    /**
     * Toggle emoji reaction on a thread (AJAX)
     */
    public function react(Request $request, ForumThread $thread)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'emoji' => 'required|string|in:🔥,❤️,😂,🤔,💡,👏',
        ]);

        $alias = ForumReaction::getAlias($validated['emoji']);

        $existing = ForumReaction::where('user_id', $user->id)
            ->where('forum_thread_id', $thread->id)
            ->whereNull('forum_reply_id')
            ->where('emoji', $alias)
            ->first();

        if ($existing) {
            $existing->delete();
            $reacted = false;
        } else {
            try {
                ForumReaction::create([
                    'user_id' => $user->id,
                    'forum_thread_id' => $thread->id,
                    'forum_reply_id' => null,
                    'emoji' => $alias,
                ]);
                $reacted = true;
            } catch (\Illuminate\Database\QueryException $e) {
                // If it's a unique constraint violation, it means it was created concurrently.
                // We can safely delete it to toggle it off.
                if ($e->getCode() == 23000) {
                    ForumReaction::where('user_id', $user->id)
                        ->where('forum_thread_id', $thread->id)
                        ->whereNull('forum_reply_id')
                        ->where('emoji', $alias)
                        ->delete();
                    $reacted = false;
                } else {
                    throw $e;
                }
            }
        }

        return response()->json([
            'success' => true,
            'reacted' => $reacted,
            'counts' => $thread->getReactionCounts(),
        ]);
    }

    /**
     * Toggle emoji reaction on a reply (AJAX)
     */
    public function reactReply(Request $request, ForumReply $reply)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'emoji' => 'required|string|in:🔥,❤️,😂,🤔,💡,👏',
        ]);

        $alias = ForumReaction::getAlias($validated['emoji']);

        $existing = ForumReaction::where('user_id', $user->id)
            ->where('forum_reply_id', $reply->id)
            ->whereNull('forum_thread_id')
            ->where('emoji', $alias)
            ->first();

        if ($existing) {
            $existing->delete();
            $reacted = false;
        } else {
            try {
                ForumReaction::create([
                    'user_id' => $user->id,
                    'forum_thread_id' => null,
                    'forum_reply_id' => $reply->id,
                    'emoji' => $alias,
                ]);
                $reacted = true;
            } catch (\Illuminate\Database\QueryException $e) {
                if ($e->getCode() == 23000) {
                    ForumReaction::where('user_id', $user->id)
                        ->where('forum_reply_id', $reply->id)
                        ->whereNull('forum_thread_id')
                        ->where('emoji', $alias)
                        ->delete();
                    $reacted = false;
                } else {
                    throw $e;
                }
            }
        }

        return response()->json([
            'success' => true,
            'reacted' => $reacted,
            'counts' => $reply->getReactionCounts(),
        ]);
    }

    /**
     * Create a poll for a thread
     */
    public function createPoll(Request $request, ForumThread $thread)
    {
        $user = Auth::user();

        // Only thread author or admin/teacher can create poll
        if ($user->id !== $thread->user_id && !$user->isSuperAdmin() && !$user->isGuru()) {
            abort(403, 'Unauthorized');
        }

        // Only one poll per thread
        if ($thread->poll) {
            return back()->with('error', 'Thread ini sudah memiliki polling.');
        }

        $validated = $request->validate([
            'question' => 'required|string|max:500',
            'options' => 'required|array|min:2|max:6',
            'options.*' => 'required|string|max:255',
            'is_multiple_choice' => 'nullable|boolean',
            'closes_at' => 'nullable|date|after:now',
        ]);

        DB::beginTransaction();
        try {
            $poll = ForumPoll::create([
                'forum_thread_id' => $thread->id,
                'question' => $validated['question'],
                'is_multiple_choice' => $validated['is_multiple_choice'] ?? false,
                'closes_at' => $validated['closes_at'] ?? null,
            ]);

            foreach ($validated['options'] as $optionText) {
                ForumPollOption::create([
                    'forum_poll_id' => $poll->id,
                    'option_text' => $optionText,
                ]);
            }

            DB::commit();
            return back()->with('success', 'Polling berhasil dibuat!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal membuat polling: ' . $e->getMessage());
        }
    }

    /**
     * Vote on a poll option (AJAX)
     */
    public function votePoll(Request $request, ForumPollOption $option)
    {
        $user = Auth::user();
        $poll = $option->poll;

        if (!$poll->isOpen()) {
            return response()->json(['success' => false, 'message' => 'Polling sudah ditutup.'], 422);
        }

        // Check if already voted
        $existingVote = ForumPollVote::where('forum_poll_id', $poll->id)
            ->where('user_id', $user->id)
            ->first();

        DB::beginTransaction();
        try {
            if ($existingVote) {
                // If voting for same option, remove vote
                if ($existingVote->forum_poll_option_id === $option->id) {
                    $existingVote->delete();
                    $option->decrement('votes_count');
                    DB::commit();
                    
                    return response()->json([
                        'success' => true,
                        'voted' => false,
                        'options' => $poll->fresh()->options->map(fn($o) => [
                            'id' => $o->id,
                            'votes_count' => $o->votes_count,
                            'percentage' => $poll->totalVotes() > 0 ? round(($o->votes_count / $poll->totalVotes()) * 100) : 0,
                        ]),
                    ]);
                }
                
                // Switch vote to different option
                $oldOption = $existingVote->option;
                $oldOption->decrement('votes_count');
                $existingVote->update(['forum_poll_option_id' => $option->id]);
                $option->increment('votes_count');
            } else {
                ForumPollVote::create([
                    'forum_poll_id' => $poll->id,
                    'forum_poll_option_id' => $option->id,
                    'user_id' => $user->id,
                ]);
                $option->increment('votes_count');
            }

            DB::commit();

            $poll->refresh();
            $totalVotes = $poll->totalVotes();

            return response()->json([
                'success' => true,
                'voted' => true,
                'voted_option_id' => $option->id,
                'options' => $poll->options->map(fn($o) => [
                    'id' => $o->id,
                    'votes_count' => $o->votes_count,
                    'percentage' => $totalVotes > 0 ? round(($o->votes_count / $totalVotes) * 100) : 0,
                ]),
                'total_votes' => $totalVotes,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // --- Pembda Place (Live Collaborative Canvas) Methods ---

    public function getPlaceCanvas()
    {
        $pixels = \App\Models\ForumPlacePixel::select('x', 'y', 'color', 'user_id', 'updated_at')
                    ->with('user:id,name')
                    ->get()
                    ->map(function ($px) {
                        return [
                            'x' => $px->x,
                            'y' => $px->y,
                            'color' => $px->color,
                            'user' => $px->user ? $px->user->name : 'Unknown',
                            'time' => $px->updated_at->diffForHumans(),
                        ];
                    });

        return response()->json(['success' => true, 'pixels' => $pixels, 'timestamp' => time()]);
    }

    public function drawPlacePixel(Request $request)
    {
        $request->validate([
            'x' => 'required|integer|min:0|max:99',
            'y' => 'required|integer|min:0|max:99',
            'color' => 'required|string|size:7',
        ]);

        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $cacheKey = 'place_cooldown:' . $user->id;
        $expiryTime = \Illuminate\Support\Facades\Cache::get($cacheKey);
        if ($expiryTime && $expiryTime > time()) {
            $ttl = $expiryTime - time();
            return response()->json(['success' => false, 'message' => 'Cooldown aktif', 'ttl' => $ttl], 429);
        }

        // Save pixel
        $pixel = \App\Models\ForumPlacePixel::updateOrCreate(
            ['x' => $request->x, 'y' => $request->y],
            ['color' => $request->color, 'user_id' => $user->id, 'updated_at' => now()]
        );

        // Set cooldown: 300 seconds (5 minutes)
        \Illuminate\Support\Facades\Cache::put($cacheKey, time() + 300, 300);

        return response()->json(['success' => true, 'pixel' => [
            'x' => $pixel->x,
            'y' => $pixel->y,
            'color' => $pixel->color,
            'user' => $user->name,
            'time' => 'Baru saja'
        ]]);
    }

    public function getPlaceUpdates(Request $request)
    {
        $timestamp = $request->get('since');
        if (!$timestamp) {
            return response()->json(['success' => false, 'pixels' => []]);
        }

        // since is in seconds
        $date = \Carbon\Carbon::createFromTimestamp($timestamp);

        $pixels = \App\Models\ForumPlacePixel::select('x', 'y', 'color', 'user_id', 'updated_at')
                    ->where('updated_at', '>', $date)
                    ->with('user:id,name')
                    ->get()
                    ->map(function ($px) {
                        return [
                            'x' => $px->x,
                            'y' => $px->y,
                            'color' => $px->color,
                            'user' => $px->user ? $px->user->name : 'Unknown',
                            'time' => $px->updated_at->diffForHumans(),
                        ];
                    });

        return response()->json(['success' => true, 'pixels' => $pixels, 'timestamp' => time()]);
    }

    // ==========================================
    // PEMBDA COLABS (PUZZLE)
    // ==========================================
    public function getPuzzleState()
    {
        $puzzle = \App\Models\Puzzle::where('is_active', true)->latest()->first();
        if (!$puzzle) {
            // Auto-create default active puzzle if none exists
            $puzzle = \App\Models\Puzzle::create([
                'title' => 'Esports Championship (Minggu 1)',
                'image_path' => 'puzzles/pembda_puzzle_1.png',
                'grid_x' => 10,
                'grid_y' => 5,
                'is_active' => true,
            ]);

            $totalPieces = $puzzle->grid_x * $puzzle->grid_y;
            $piecesData = [];
            for ($i = 0; $i < $totalPieces; $i++) {
                $piecesData[] = [
                    'puzzle_id' => $puzzle->id,
                    'piece_index' => $i,
                    'is_placed' => false,
                    'placed_by_user_id' => null,
                    'placed_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            \App\Models\PuzzlePiece::insert($piecesData);

            // Give 10 random initial bonus pieces placed by System
            $bonusIds = \App\Models\PuzzlePiece::where('puzzle_id', $puzzle->id)
                ->inRandomOrder()
                ->limit(10)
                ->pluck('id');

            \App\Models\PuzzlePiece::whereIn('id', $bonusIds)->update([
                'is_placed' => true,
                'placed_at' => now(),
            ]);
        }

        $pieces = \App\Models\PuzzlePiece::where('puzzle_id', $puzzle->id)
                    ->with('user:id,name')
                    ->get()
                    ->map(function ($p) {
                        return [
                            'id' => $p->id,
                            'index' => $p->piece_index,
                            'is_placed' => $p->is_placed,
                            'placed_by' => $p->user ? $p->user->name : null,
                            'placed_at' => $p->placed_at ? $p->placed_at->diffForHumans() : null,
                        ];
                    });

        // Check if current user has placed a piece today
        $user = Auth::user();
        $hasPlacedToday = false;
        if ($user) {
            $hasPlacedToday = \App\Models\PuzzlePiece::where('puzzle_id', $puzzle->id)
                ->where('placed_by_user_id', $user->id)
                ->whereDate('placed_at', \Carbon\Carbon::today())
                ->exists();
        }

        return response()->json([
            'success' => true,
            'puzzle' => [
                'id' => $puzzle->id,
                'title' => $puzzle->title,
                'image_url' => asset('storage/' . $puzzle->image_path),
                'grid_x' => $puzzle->grid_x,
                'grid_y' => $puzzle->grid_y,
                'progress' => $puzzle->progress,
            ],
            'pieces' => $pieces,
            'has_placed_today' => $hasPlacedToday,
        ]);
    }

    public function placePuzzlePiece(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Anda harus login']);
        }

        $validated = $request->validate([
            'puzzle_id' => 'required|exists:puzzles,id',
            'piece_index' => 'required|integer',
            'target_index' => 'required|integer',
        ]);

        // Check if placed on correct index
        if ($validated['piece_index'] !== $validated['target_index']) {
            return response()->json(['success' => false, 'message' => 'Posisi salah! Coba letakkan di tempat yang benar. (Jatah harian Anda masih aman)']);
        }

        // Check if user already placed today
        $hasPlacedToday = \App\Models\PuzzlePiece::where('puzzle_id', $validated['puzzle_id'])
            ->where('placed_by_user_id', $user->id)
            ->whereDate('placed_at', \Carbon\Carbon::today())
            ->exists();

        if ($hasPlacedToday) {
            return response()->json(['success' => false, 'message' => 'Anda sudah meletakkan kepingan hari ini. Kembali lagi besok!']);
        }

        $piece = \App\Models\PuzzlePiece::where('puzzle_id', $validated['puzzle_id'])
            ->where('piece_index', $validated['piece_index'])
            ->first();

        if (!$piece) {
            return response()->json(['success' => false, 'message' => 'Kepingan tidak ditemukan']);
        }

        if ($piece->is_placed) {
            return response()->json(['success' => false, 'message' => 'Kepingan ini sudah diletakkan oleh orang lain']);
        }

        $piece->update([
            'is_placed' => true,
            'placed_by_user_id' => $user->id,
            'placed_at' => now(),
        ]);

        // Gamification
        \App\Models\ReputationLog::log($user->id, 10, 'forum', 'Meletakkan kepingan Pembda Colabs');

        return response()->json(['success' => true, 'message' => 'Kepingan berhasil diletakkan! (+10 Poin)']);
    }

    /**
     * Reset papan puzzle ke awal (hanya 10 keping acak dari Sistem).
     * Hanya bisa diakses oleh admin.
     */
    public function resetPuzzle(Request $request)
    {
        $user = Auth::user();
        if (!$user || (!$user->isAdmin() && !$user->isSuperAdmin() && $user->role !== 'admin')) {
            return response()->json(['success' => false, 'message' => 'Hanya admin yang bisa mereset puzzle']);
        }

        $puzzle = \App\Models\Puzzle::where('is_active', true)->latest()->first();
        if (!$puzzle) {
            return response()->json(['success' => false, 'message' => 'Tidak ada puzzle aktif']);
        }

        // Reset semua keping ke belum terpasang
        \App\Models\PuzzlePiece::where('puzzle_id', $puzzle->id)->update([
            'is_placed' => false,
            'placed_by_user_id' => null,
            'placed_at' => null,
        ]);

        // Pasang 10 keping acak sebagai bonus awal dari Sistem
        $bonusIds = \App\Models\PuzzlePiece::where('puzzle_id', $puzzle->id)
            ->inRandomOrder()
            ->limit(10)
            ->pluck('id');

        \App\Models\PuzzlePiece::whereIn('id', $bonusIds)->update([
            'is_placed' => true,
            'placed_at' => now(),
            // placed_by_user_id tetap null = Sistem (Bonus)
        ]);

        return response()->json([
            'success' => true, 
            'message' => 'Puzzle berhasil direset! 10 keping acak telah dipasang oleh Sistem.'
        ]);
    }
}
