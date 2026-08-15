<?php

namespace App\Http\Controllers;

use App\Models\AlumniForum;
use App\Models\AlumniForumReply;
use Illuminate\Http\Request;

class AlumniForumController extends Controller
{
    private function resolveSchoolId(): int
    {
        $user = auth()->user();
        $schoolId = $user->school_id 
            ?? $user->alumniDirectory?->school_id 
            ?? $user->student?->school_id 
            ?? $user->alumniProfile?->school_id;

        if (!$schoolId) {
            $schoolId = \App\Models\School::where('type', '!=', 'yayasan')->first()?->id ?? 1;
        }

        return $schoolId;
    }

    public function index(Request $request)
    {
        $schoolId = $this->resolveSchoolId();

        $category = $request->get('category');
        $search = $request->get('search');
        $scope = $request->get('scope', 'my_ika');

        $query = AlumniForum::with(['user', 'replies', 'school']);

        if ($scope === 'my_ika' && $schoolId) {
            $query->where('school_id', $schoolId);
        }

        if ($category) {
            $query->where('category', $category);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $threads = $query->latest()->paginate(15)->withQueryString();
        $categories = AlumniForum::CATEGORIES;
        $userSchool = \App\Models\School::find($schoolId);

        return view('alumni.forum.index', compact('threads', 'category', 'search', 'scope', 'categories', 'userSchool'));
    }

    public function create()
    {
        $categories = AlumniForum::CATEGORIES;
        return view('alumni.forum.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts('alumni_forum_post:' . $user->id, 1)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn('alumni_forum_post:' . $user->id);
            return back()->withInput()->with('error', "Anda membuat topik terlalu cepat. Harap tunggu {$seconds} detik lagi untuk mencegah spam.");
        }

        $request->validate([
            'title' => 'required|string|min:15|max:255',
            'category' => 'required|string',
            'content' => 'required|string|min:15',
            'image' => 'nullable|image|max:5120',
        ], [
            'title.min' => 'Judul topik minimal 15 karakter.',
            'content.min' => 'Isi topik minimal 15 karakter.',
        ]);

        \Illuminate\Support\Facades\RateLimiter::hit('alumni_forum_post:' . $user->id, 300); // 5 menit cooldown

        $schoolId = $this->resolveSchoolId();

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('alumni_forums', 'public');
        }

        AlumniForum::create([
            'user_id' => $user->id,
            'school_id' => $schoolId,
            'category' => $request->category,
            'title' => $request->title,
            'content' => $request->content,
            'image_path' => $imagePath,
        ]);

        return redirect()->route('alumni.forum.index')->with('success', 'Topik diskusi berhasil diterbitkan!');
    }

    public function show(AlumniForum $forum)
    {
        $forum->increment('views_count');
        $forum->load(['user', 'replies.user']);

        return view('alumni.forum.show', compact('forum'));
    }

    public function reply(Request $request, AlumniForum $forum)
    {
        $user = auth()->user();

        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts('alumni_forum_reply:' . $user->id, 1)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn('alumni_forum_reply:' . $user->id);
            return back()->withInput()->with('error', "Anda membalas terlalu cepat. Harap tunggu {$seconds} detik lagi.");
        }

        $request->validate([
            'content' => 'required|string|min:10',
        ], [
            'content.min' => 'Tanggapan minimal 10 karakter.',
        ]);

        \Illuminate\Support\Facades\RateLimiter::hit('alumni_forum_reply:' . $user->id, 60);

        AlumniForumReply::create([
            'alumni_forum_id' => $forum->id,
            'user_id' => $user->id,
            'content' => $request->content,
        ]);

        return back()->with('success', 'Tanggapan berhasil dikirim!');
    }
}
