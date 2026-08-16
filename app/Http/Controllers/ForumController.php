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

        $query = ForumThread::with(['user', 'replies', 'likes', 'members', 'reactions', 'poll.options', 'poll.votes'])
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

        // Fetch Pembda Space Groups for PC Sidebar
        $userGroups = collect();
        if ($user && class_exists('\App\Models\ForumGroup')) {
            // Standardize existing DB record names if needed
            \App\Models\ForumGroup::where('slug', 'lobi-utama')->update(['name' => 'Lobi Utama Pembda Space', 'icon' => '💬']);
            \App\Models\ForumGroup::where('slug', 'ruang-guru-pembda')->update(['name' => 'Diskusi Guru & Staf', 'icon' => '👨‍🏫']);

            $userGroups = \App\Models\ForumGroup::whereHas('members', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->with(['latestThread'])->get();
        }

        // Fetch Highlight Threads for Forum Header Banner
        $latestHighlight = ForumThread::with(['user'])->latest()->first();
        $trendingHighlight = ForumThread::with(['user'])->withCount(['replies', 'likes'])
            ->orderByDesc('replies_count')
            ->first();

        return view('forum.index', compact(
            'threads', 'counts', 'category', 'search', 
            'topStudents', 'activeCollabs', 'channelGroups',
            'totalThreads', 'onlineCount', 'latestHighlight', 'trendingHighlight', 'userGroups'
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
            'title' => 'required|string|min:15|max:255',
            'content' => 'required|string|min:15',
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

        $messages = [
            'title.min' => 'Judul topik terlalu singkat. Masukkan minimal 15 karakter.',
            'content.min' => 'Isi topik terlalu singkat. Masukkan minimal 15 karakter.',
        ];

        $validated = $request->validate($rules, $messages);

        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts('forum_post:' . $user->id, 1)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn('forum_post:' . $user->id);
            return back()->withInput()->with('error', "Anda membuat topik terlalu cepat. Harap tunggu {$seconds} detik lagi untuk mencegah spam.");
        }

        DB::beginTransaction();
        try {
            \Illuminate\Support\Facades\RateLimiter::hit('forum_post:' . $user->id, 300); // 5 menit cooldown
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

        $rules = [
            'content' => 'required_without:voice_note|string|min:10|max:5000|nullable',
            'voice_note' => 'nullable|file|mimes:webm,mp3,mp4,m4a,ogg,wav|max:5120',
            'parent_reply_id' => 'nullable|exists:forum_replies,id',
        ];
        $messages = [
            'content.min' => 'Balasan terlalu singkat. Masukkan minimal 10 karakter untuk mencegah spam (atau gunakan Voice Note).',
        ];

        $validated = $request->validate($rules, $messages);

        // Hanya limit balasan berupa teks biasa (jika voice note tidak apa-apa lebih cepat)
        if (!$request->hasFile('voice_note') && \Illuminate\Support\Facades\RateLimiter::tooManyAttempts('forum_reply:' . $user->id, 1)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn('forum_reply:' . $user->id);
            return back()->withInput()->with('error', "Anda membalas terlalu cepat. Harap tunggu {$seconds} detik lagi.");
        }

        DB::beginTransaction();
        try {
            $voiceNotePath = null;
            if ($request->hasFile('voice_note')) {
                $voiceNotePath = $request->file('voice_note')->store('forum_voice_notes', 'public');
            } else {
                \Illuminate\Support\Facades\RateLimiter::hit('forum_reply:' . $user->id, 60); // 1 menit cooldown
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

            // Notification: Send to thread author or parent reply author
            $targetUserId = null;
            $notifTitle = '';
            
            if ($reply->parent_reply_id) {
                $parentReply = ForumReply::find($reply->parent_reply_id);
                if ($parentReply && $parentReply->user_id !== $user->id) {
                    $targetUserId = $parentReply->user_id;
                    $notifTitle = "{$user->name} membalas komentar Anda";
                }
            } elseif ($thread->user_id !== $user->id) {
                $targetUserId = $thread->user_id;
                $notifTitle = "{$user->name} mengomentari postingan Anda";
            }

            if ($targetUserId) {
                \App\Models\Notification::create([
                    'user_id' => $targetUserId,
                    'title' => $notifTitle,
                    'message' => "Ada balasan baru di topik: {$thread->title}",
                    'type' => 'forum',
                    'related_model' => ForumThread::class,
                    'related_id' => $thread->id,
                ]);
            }

            DB::commit();

            return back()->with('success', 'Komentar ditambahkan! (+5 Poin Reputasi)');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memposting komentar: ' . $e->getMessage());
        }
    }

    /**
     * Update a reply / comment
     */
    public function updateReply(Request $request, ForumReply $reply)
    {
        $user = Auth::user();
        if ($reply->user_id !== $user->id && !$user->isSuperAdmin()) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengedit komentar ini.');
        }

        $validated = $request->validate([
            'content' => 'required|string|min:2|max:3000',
        ]);

        $reply->update([
            'content' => $validated['content'],
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Komentar berhasil diperbarui!',
                'content' => $reply->content,
            ]);
        }

        return back()->with('success', 'Komentar berhasil diperbarui!');
    }

    /**
     * Delete a reply / comment
     */
    public function destroyReply(ForumReply $reply)
    {
        $user = Auth::user();
        $thread = $reply->thread;
        if ($reply->user_id !== $user->id && !$user->isSuperAdmin() && $thread->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk menghapus komentar ini.');
        }

        DB::beginTransaction();
        try {
            // Delete voice note if exists
            if ($reply->voice_note_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($reply->voice_note_path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($reply->voice_note_path);
            }

            // Remove reputation points
            ReputationLog::removeLog($reply->user_id, ForumReply::class, $reply->id);

            // Re-assign child replies to parent of this reply (or top-level)
            ForumReply::where('parent_reply_id', $reply->id)->update(['parent_reply_id' => $reply->parent_reply_id]);

            // Delete reactions
            \App\Models\ForumReaction::where('forum_reply_id', $reply->id)->delete();

            $reply->delete();

            DB::commit();

            return back()->with('success', 'Komentar berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus komentar: ' . $e->getMessage());
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
                    
                    \App\Models\Notification::create([
                        'user_id' => $author->id,
                        'title' => "{$user->name} menyukai postingan Anda",
                        'message' => "Postingan Anda mendapat upvote: {$thread->title}",
                        'type' => 'forum',
                        'related_model' => ForumThread::class,
                        'related_id' => $thread->id,
                    ]);
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
        if ($user->id !== $thread->user_id && !$user->isSuperAdmin()) {
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
        if ($user->id !== $thread->user_id && !$user->isSuperAdmin()) {
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

        if ($user->id !== $thread->user_id && !$user->isSuperAdmin()) {
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

        if ($user->id !== $thread->user_id && !$user->isSuperAdmin()) {
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

        if ($user->id !== $thread->user_id && !$user->isSuperAdmin()) {
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

        if ($user->id !== $thread->user_id && !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized');
        }

        $allowedCategories = array_keys(ForumThread::CATEGORIES);
        if (!$user->isSuperAdmin() && !$user->isAdminSekolah() && !$user->isGuru()) {
            $allowedCategories = array_diff($allowedCategories, ['info']);
        }

        $rules = [
            'title' => 'required|string|min:10|max:255',
            'content' => 'required|string|min:10',
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

        $messages = [
            'title.min' => 'Judul topik terlalu singkat. Masukkan minimal 10 karakter.',
            'content.min' => 'Isi topik terlalu singkat. Masukkan minimal 10 karakter.',
        ];

        $validated = $request->validate($rules, $messages);

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

        if ($user->id !== $thread->user_id && !$user->isSuperAdmin()) {
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
                
                if ($thread->user_id !== $user->id) {
                    \App\Models\Notification::create([
                        'user_id' => $thread->user_id,
                        'title' => "{$user->name} memberikan reaksi pada postingan Anda",
                        'message' => "Postingan Anda mendapat reaksi {$validated['emoji']}: {$thread->title}",
                        'type' => 'forum',
                        'related_model' => ForumThread::class,
                        'related_id' => $thread->id,
                    ]);
                }
                
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

                if ($reply->user_id !== $user->id) {
                    \App\Models\Notification::create([
                        'user_id' => $reply->user_id,
                        'title' => "{$user->name} memberikan reaksi pada komentar Anda",
                        'message' => "Komentar Anda mendapat reaksi {$validated['emoji']}",
                        'type' => 'forum',
                        'related_model' => ForumThread::class,
                        'related_id' => $reply->forum_thread_id,
                    ]);
                }

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
        if ($user->id !== $thread->user_id && !$user->isSuperAdmin()) {
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
    // PEMBDA COLABS (PUZZLE) - REBUILT CLEANLY
    // ==========================================
    public function getPuzzleState()
    {
        // 1. Dapatkan puzzle aktif, jika belum ada buat puzzle default baru
        $puzzle = \App\Models\Puzzle::where('is_active', true)->latest()->first();
        if (!$puzzle) {
            $puzzle = \App\Models\Puzzle::create([
                'title' => 'Esports Championship (Minggu 1)',
                'image_path' => 'puzzles/pembda_puzzle_1.png',
                'grid_x' => 10,
                'grid_y' => 5,
                'is_active' => true,
            ]);
        }

        // Nonaktifkan puzzle lain agar hanya 1 puzzle aktif di DB
        \App\Models\Puzzle::where('id', '!=', $puzzle->id)->where('is_active', true)->update(['is_active' => false]);

        $totalPieces = $puzzle->grid_x * $puzzle->grid_y;

        // 2. Cek apakah kepingan sudah di-generate di DB (harus tepat 50 keping), jika tidak buat baru
        $piecesCount = \App\Models\PuzzlePiece::where('puzzle_id', $puzzle->id)->count();
        if ($piecesCount !== $totalPieces) {
            \App\Models\PuzzlePiece::where('puzzle_id', $puzzle->id)->delete();
            $newPieces = [];
            for ($i = 0; $i < $totalPieces; $i++) {
                $newPieces[] = [
                    'puzzle_id' => $puzzle->id,
                    'piece_index' => $i,
                    'is_placed' => false,
                    'placed_by_user_id' => null,
                    'placed_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            \App\Models\PuzzlePiece::insert($newPieces);

            // Beri 10 keping bonus awal dari sistem
            $bonusIds = \App\Models\PuzzlePiece::where('puzzle_id', $puzzle->id)
                ->inRandomOrder()
                ->limit(10)
                ->pluck('id');

            \App\Models\PuzzlePiece::whereIn('id', $bonusIds)->update([
                'is_placed' => true,
                'placed_at' => now(),
            ]);
        }

        // 3. Ambil semua kepingan dari DB
        $allPieces = \App\Models\PuzzlePiece::where('puzzle_id', $puzzle->id)
            ->with('user:id,name')
            ->orderBy('piece_index', 'asc')
            ->get();

        // 4. Hitung dan susun array board & inventory langsung dari PHP Backend
        $board = array_fill(0, $totalPieces, null);
        $inventory = [];
        $placedCount = 0;

        foreach ($allPieces as $piece) {
            $isPlaced = (bool) ($piece->is_placed == 1 || $piece->is_placed === true || $piece->is_placed === '1');
            if ($isPlaced && $piece->piece_index >= 0 && $piece->piece_index < $totalPieces) {
                $board[$piece->piece_index] = [
                    'id' => (int) $piece->id,
                    'index' => (int) $piece->piece_index,
                    'placed_by' => $piece->user ? $piece->user->name : 'Sistem (Bonus)',
                    'placed_at' => $piece->placed_at ? $piece->placed_at->diffForHumans() : null,
                ];
                $placedCount++;
            } else {
                $inventory[] = (int) $piece->piece_index;
            }
        }

        // Cek apakah user saat ini sudah menaruh kepingan hari ini
        $user = Auth::user();
        $hasPlacedToday = false;
        if ($user) {
            $hasPlacedToday = \App\Models\PuzzlePiece::where('puzzle_id', $puzzle->id)
                ->where('placed_by_user_id', $user->id)
                ->whereDate('placed_at', \Carbon\Carbon::today())
                ->exists();
        }

        $percentage = $totalPieces > 0 ? round(($placedCount / $totalPieces) * 100) : 0;

        return response()->json([
            'success' => true,
            'puzzle' => [
                'id' => (int) $puzzle->id,
                'title' => (string) $puzzle->title,
                'image_url' => asset('storage/' . $puzzle->image_path),
                'grid_x' => (int) $puzzle->grid_x,
                'grid_y' => (int) $puzzle->grid_y,
                'total_pieces' => $totalPieces,
                'placed_pieces' => $placedCount,
                'percentage' => $percentage,
            ],
            'board' => $board,
            'inventory' => $inventory,
            'has_placed_today' => (bool) $hasPlacedToday,
        ])->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
          ->header('Pragma', 'no-cache')
          ->header('Expires', 'Sat, 01 Jan 1990 00:00:00 GMT');
    }

    public function placePuzzlePiece(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Anda harus login untuk bermain']);
        }

        $validated = $request->validate([
            'puzzle_id' => 'required|integer',
            'piece_index' => 'required|integer',
            'target_index' => 'required|integer',
        ]);

        $puzzle = \App\Models\Puzzle::find($validated['puzzle_id']);
        if (!$puzzle || !$puzzle->is_active) {
            return response()->json(['success' => false, 'message' => 'Puzzle tidak aktif']);
        }

        // Cek apakah posisi meletakkan sudah benar
        if ($validated['piece_index'] !== $validated['target_index']) {
            return response()->json([
                'success' => false,
                'message' => 'Posisi kurang tepat! Coba letakkan kepingan ini di kotak yang sesuai (Jatah harian Anda masih aman).'
            ]);
        }

        // Cek apakah user sudah meletakkan kepingan hari ini
        $hasPlacedToday = \App\Models\PuzzlePiece::where('puzzle_id', $puzzle->id)
            ->where('placed_by_user_id', $user->id)
            ->whereDate('placed_at', \Carbon\Carbon::today())
            ->exists();

        if ($hasPlacedToday) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah meletakkan kepingan hari ini! Kembali lagi besok.'
            ]);
        }

        $piece = \App\Models\PuzzlePiece::where('puzzle_id', $puzzle->id)
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

        // Beri poin reputasi (+10)
        try {
            \App\Models\ReputationLog::log($user->id, 10, 'forum', 'Meletakkan kepingan Pembda Colabs');
        } catch (\Exception $e) {}

        return response()->json([
            'success' => true,
            'message' => 'Mantap! Kepingan berhasil diletakkan (+10 Poin Reputasi)!'
        ]);
    }

    public function resetPuzzle(Request $request)
    {
        $user = Auth::user();
        if (!$user || (!$user->isSuperAdmin() && !$user->isAdminSekolah() && !in_array($user->role, ['superadmin', 'admin', 'admin_sekolah', 'admin_yayasan']))) {
            return response()->json(['success' => false, 'message' => 'Hanya admin yang bisa mereset puzzle']);
        }

        $puzzle = \App\Models\Puzzle::latest()->first();
        if (!$puzzle) {
            $puzzle = \App\Models\Puzzle::create([
                'title' => 'Esports Championship (Minggu 1)',
                'image_path' => 'puzzles/pembda_puzzle_1.png',
                'grid_x' => 10,
                'grid_y' => 5,
                'is_active' => true,
            ]);
        }

        // Matikan puzzle lain & aktifkan puzzle ini
        \App\Models\Puzzle::where('id', '!=', $puzzle->id)->update(['is_active' => false]);
        $puzzle->update(['is_active' => true]);

        // Bersihkan dan set ulang kepingan menjadi 50 keping bersih
        \App\Models\PuzzlePiece::where('puzzle_id', $puzzle->id)->delete();

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

        // Pasang 10 keping acak sebagai bonus awal dari Sistem (20% selesai)
        $bonusIds = \App\Models\PuzzlePiece::where('puzzle_id', $puzzle->id)
            ->inRandomOrder()
            ->limit(10)
            ->pluck('id');

        \App\Models\PuzzlePiece::whereIn('id', $bonusIds)->update([
            'is_placed' => true,
            'placed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Puzzle berhasil direset! 10 keping acak awal (20%) telah dipasang oleh Sistem.'
        ]);
    }
}
