<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\ForumThread;
use App\Models\ForumReply;
use App\Models\ForumLike;
use App\Models\ForumMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MobileSpaceController extends Controller
{
    public function index(Request $request)
    {
        $channel = $request->input('channel');
        $category = $request->input('category');
        $search = $request->input('search');

        $query = ForumThread::with(['user', 'category', 'replies'])
            ->withCount('replies', 'likes');

        if ($channel) {
            $query->where('channel_group', $channel);
        }

        if ($category) {
            $query->where('category_id', $category);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $threads = $query->orderBy('is_pinned', 'desc')
            ->latest()
            ->paginate(15);

        return view('mobile.space.index', compact('threads', 'channel', 'category', 'search'));
    }

    public function create()
    {
        return view('mobile.space.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category_slug' => 'nullable|string',
            'channel_group' => 'nullable|string',
        ]);

        $thread = ForumThread::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'content' => $validated['content'],
            'channel_group' => $validated['channel_group'] ?? 'diskusi',
            'category_slug' => $validated['category_slug'] ?? 'diskusi',
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
            ->where('thread_id', $thread->id)
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
            'thread_id' => $thread->id,
            'user_id' => Auth::id(),
            'content' => $request->input('content'),
        ]);

        return back()->with('success', 'Komentar Anda berhasil dikirim.');
    }

    public function like($id)
    {
        $userId = Auth::id();
        $like = ForumLike::where('user_id', $userId)->where('thread_id', $id)->first();

        if ($like) {
            $like->delete();
            $liked = false;
        } else {
            ForumLike::create([
                'user_id' => $userId,
                'thread_id' => $id,
            ]);
            $liked = true;
        }

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'liked' => $liked]);
        }

        return back();
    }
}
