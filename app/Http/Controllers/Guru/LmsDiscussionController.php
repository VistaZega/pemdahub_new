<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lms\StoreLmsDiscussionRequest;
use App\Models\LmsAnnouncement;
use App\Models\LmsCourse;
use App\Models\LmsDiscussion;
use App\Models\LmsDiscussionReply;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LmsDiscussionController extends Controller
{
    private function getTeacher(): ?Teacher
    {
        return Teacher::where('user_id', Auth::id())->first();
    }

    private function authorizeAccess(LmsCourse $course, Teacher $teacher): bool
    {
        return $course->teacher_id === $teacher->id;
    }

    // ================================================================
    // DISCUSSIONS
    // ================================================================

    /**
     * Show discussion forum for a course
     */
    public function index(LmsCourse $course)
    {
        $teacher = $this->getTeacher();
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }
        $teacher->load('school');

        $discussions = $course->discussions()
            ->with(['author', 'latestReply.author'])
            ->withCount('replies')
            ->orderByDesc('is_pinned')
            ->orderByDesc('last_reply_at')
            ->orderByDesc('created_at')
            ->paginate(15)->withQueryString();

        return view('guru.lms.discussions.index', compact('teacher', 'course', 'discussions'));
    }

    /**
     * Create new discussion
     */
    public function store(StoreLmsDiscussionRequest $request, LmsCourse $course)
    {
        $teacher = $this->getTeacher();
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }

        $course->discussions()->create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'content' => $request->content,
            'type' => $request->type,
            'is_pinned' => $request->boolean('is_pinned'),
        ]);

        return redirect()->route('guru.lms.discussions.index', $course->id)
            ->with('success', 'Diskusi berhasil dibuat.');
    }

    /**
     * Show discussion detail with replies
     */
    public function show(LmsCourse $course, LmsDiscussion $discussion)
    {
        $teacher = $this->getTeacher();
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }
        $teacher->load('school');

        $discussion->load([
            'author',
            'topLevelReplies' => fn($q) => $q->with(['author', 'children.author'])->orderBy('created_at'),
        ]);

        return view('guru.lms.discussions.show', compact('teacher', 'course', 'discussion'));
    }

    /**
     * Edit discussion topic
     */
    public function edit(LmsCourse $course, LmsDiscussion $discussion)
    {
        $teacher = $this->getTeacher();
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }
        $teacher->load('school');

        return view('guru.lms.discussions.edit', compact('teacher', 'course', 'discussion'));
    }

    /**
     * Update discussion topic
     */
    public function update(Request $request, LmsCourse $course, LmsDiscussion $discussion)
    {
        $teacher = $this->getTeacher();
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:300',
            'content' => 'required|string',
            'type' => 'required|in:discussion,question,announcement',
        ]);

        $discussion->update([
            'title' => $request->title,
            'content' => $request->content,
            'type' => $request->type,
            'is_pinned' => $request->boolean('is_pinned'),
        ]);

        return redirect()->route('guru.lms.discussions.show', [$course->id, $discussion->id])
            ->with('success', 'Topik diskusi berhasil diperbarui.');
    }

    /**
     * Reply to discussion
     */
    public function reply(Request $request, LmsCourse $course, LmsDiscussion $discussion)
    {
        $teacher = $this->getTeacher();
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }

        if ($discussion->is_locked) {
            return redirect()->back()->with('error', 'Diskusi ini sudah dikunci.');
        }

        $request->validate([
            'content' => 'required|string',
            'parent_id' => 'nullable|exists:lms_discussion_replies,id',
        ]);

        $discussion->replies()->create([
            'user_id' => Auth::id(),
            'parent_id' => $request->parent_id,
            'content' => $request->content,
        ]);

        $discussion->incrementRepliesCount();

        return redirect()->route('guru.lms.discussions.show', [$course->id, $discussion->id])
            ->with('success', 'Balasan berhasil ditambahkan.');
    }

    /**
     * Toggle pin discussion
     */
    public function togglePin(LmsCourse $course, LmsDiscussion $discussion)
    {
        $teacher = $this->getTeacher();
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }

        $discussion->update(['is_pinned' => !$discussion->is_pinned]);

        return redirect()->back()->with('success', $discussion->is_pinned ? 'Diskusi telah disematkan.' : 'Diskusi tidak lagi disematkan.');
    }

    /**
     * Toggle lock discussion
     */
    public function toggleLock(LmsCourse $course, LmsDiscussion $discussion)
    {
        $teacher = $this->getTeacher();
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }

        $discussion->update(['is_locked' => !$discussion->is_locked]);

        return redirect()->back()->with('success', $discussion->is_locked ? 'Diskusi telah dikunci.' : 'Diskusi telah dibuka kembali.');
    }

    /**
     * Mark reply as best answer
     */
    public function markBestAnswer(LmsCourse $course, LmsDiscussion $discussion, LmsDiscussionReply $reply)
    {
        $teacher = $this->getTeacher();
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }

        $reply->markAsBestAnswer();

        return redirect()->back()->with('success', 'Jawaban terbaik telah ditandai.');
    }

    /**
     * Delete discussion (soft delete)
     */
    public function destroy(LmsCourse $course, LmsDiscussion $discussion)
    {
        $teacher = $this->getTeacher();
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }

        $courseId = $course->id;
        $discussion->delete();

        return redirect()->route('guru.lms.discussions.index', $courseId)
            ->with('success', 'Diskusi berhasil dihapus.');
    }

    // ================================================================
    // ANNOUNCEMENTS
    // ================================================================

    /**
     * Store announcement
     */
    public function storeAnnouncement(Request $request, LmsCourse $course)
    {
        $teacher = $this->getTeacher();
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:200',
            'content' => 'required|string',
        ]);

        $course->announcements()->create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'content' => $request->content,
            'is_pinned' => $request->boolean('is_pinned'),
            'is_published' => true,
            'published_at' => now(),
        ]);

        return redirect()->route('guru.lms.show', ['course' => $course->id, 'tab' => 'announcements'])
            ->with('success', 'Pengumuman berhasil dibuat.');
    }

    /**
     * Delete announcement (soft delete)
     */
    public function destroyAnnouncement(LmsAnnouncement $announcement)
    {
        $teacher = $this->getTeacher();
        $course = $announcement->course;
        if (!$teacher || !$this->authorizeAccess($course, $teacher)) {
            abort(403);
        }

        $courseId = $course->id;
        $announcement->delete();

        return redirect()->route('guru.lms.show', ['course' => $courseId, 'tab' => 'announcements'])
            ->with('success', 'Pengumuman berhasil dihapus.');
    }
}
