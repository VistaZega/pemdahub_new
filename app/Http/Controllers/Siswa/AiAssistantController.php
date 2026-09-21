<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\AiStudentAssistantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AiAssistantController extends Controller
{
    protected $aiService;

    public function __construct(AiStudentAssistantService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Display the main Pembda AI interface.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $student = $user->student;

        if (!$student) {
            return redirect()->route('siswa.dashboard')->with('error', 'Profil siswa tidak ditemukan.');
        }

        // Fetch student's conversations
        $conversations = DB::table('ai_conversations')
            ->where('student_id', $student->id)
            ->orderBy('updated_at', 'desc')
            ->get();

        $activeConversationId = $request->query('conversation_id');
        $activeConversation = null;
        $messages = [];

        if ($activeConversationId) {
            $activeConversation = DB::table('ai_conversations')
                ->where('id', $activeConversationId)
                ->where('student_id', $student->id)
                ->first();
        }

        if (!$activeConversation && $conversations->isNotEmpty()) {
            $activeConversation = $conversations->first();
        }

        if ($activeConversation) {
            $messages = DB::table('ai_messages')
                ->where('conversation_id', $activeConversation->id)
                ->orderBy('id', 'asc')
                ->get();
        }

        $usageInfo = $this->aiService->getUsageInfo($student->id);

        return view('siswa.ai.index', compact('student', 'conversations', 'activeConversation', 'messages', 'usageInfo'));
    }

    /**
     * Start a new AI conversation session.
     */
    public function newConversation(Request $request)
    {
        $user = Auth::user();
        $student = $user->student;

        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Siswa tidak valid'], 403);
        }

        $validated = $request->validate([
            'mode' => 'required|in:tutor,bk_consultation,lms_assistant',
            'title' => 'nullable|string|max:100'
        ]);

        $modeTitles = [
            'tutor' => 'Tutor Akademik AI',
            'bk_consultation' => 'Konsultasi BK & Karir',
            'lms_assistant' => 'Asisten Belajar LMS'
        ];

        $title = $validated['title'] ?? ($modeTitles[$validated['mode']] ?? 'Percakapan AI');

        $conversationId = DB::table('ai_conversations')->insertGetId([
            'student_id' => $student->id,
            'mode' => $validated['mode'],
            'title' => $title,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'conversation' => [
                    'id' => $conversationId,
                    'mode' => $validated['mode'],
                    'title' => $title
                ],
                'redirect_url' => route('siswa.ai.index', ['conversation_id' => $conversationId])
            ]);
        }

        return redirect()->route('siswa.ai.index', ['conversation_id' => $conversationId]);
    }

    /**
     * Send message to Pembda AI via AJAX/Fetch.
     */
    public function sendMessage(Request $request)
    {
        $user = Auth::user();
        $student = $user->student;

        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Siswa tidak ditemukan.'], 403);
        }

        $validated = $request->validate([
            'conversation_id' => 'nullable|integer',
            'mode' => 'required|in:tutor,bk_consultation,lms_assistant',
            'message' => 'required|string|max:3000',
        ]);

        $conversationId = $validated['conversation_id'] ?? null;
        $conversation = null;

        if ($conversationId) {
            $conversation = DB::table('ai_conversations')
                ->where('id', $conversationId)
                ->where('student_id', $student->id)
                ->first();
        }

        // Create conversation if none exists
        if (!$conversation) {
            $title = Str::limit(trim($validated['message']), 35, '...');
            $conversationId = DB::table('ai_conversations')->insertGetId([
                'student_id' => $student->id,
                'mode' => $validated['mode'],
                'title' => $title ?: 'Percakapan Baru',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $conversation = DB::table('ai_conversations')->where('id', $conversationId)->first();
        }

        // Fetch recent message history for context
        $rawHistory = DB::table('ai_messages')
            ->where('conversation_id', $conversation->id)
            ->orderBy('id', 'asc')
            ->take(10)
            ->get();

        $historyArray = [];
        foreach ($rawHistory as $h) {
            $historyArray[] = [
                'sender' => $h->sender,
                'message' => $h->message
            ];
        }

        // Save student's prompt
        $studentMsgId = DB::table('ai_messages')->insertGetId([
            'conversation_id' => $conversation->id,
            'sender' => 'student',
            'message' => $validated['message'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Auto update conversation title if default title
        if ($rawHistory->isEmpty()) {
            $newTitle = Str::limit(trim($validated['message']), 35, '...');
            DB::table('ai_conversations')
                ->where('id', $conversation->id)
                ->update([
                    'title' => $newTitle,
                    'updated_at' => now()
                ]);
        }

        // Generate AI response
        $aiResponse = $this->aiService->generateResponse(
            $student,
            $validated['message'],
            $conversation->mode,
            $historyArray
        );

        if (!$aiResponse['success']) {
            return response()->json([
                'success' => false,
                'message' => $aiResponse['message'],
                'is_quota_error' => $aiResponse['is_quota_error'] ?? false
            ], 422);
        }

        // Save AI response
        $aiMsgId = DB::table('ai_messages')->insertGetId([
            'conversation_id' => $conversation->id,
            'sender' => 'ai',
            'message' => $aiResponse['message'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Touch conversation updated_at
        DB::table('ai_conversations')
            ->where('id', $conversation->id)
            ->update(['updated_at' => now()]);

        $usageInfo = $this->aiService->getUsageInfo($student->id);

        return response()->json([
            'success' => true,
            'conversation_id' => $conversation->id,
            'ai_message' => [
                'id' => $aiMsgId,
                'message' => $aiResponse['message'],
                'created_at' => now()->format('H:i')
            ],
            'usage' => $usageInfo
        ]);
    }

    /**
     * Delete an AI conversation.
     */
    public function destroyConversation($id)
    {
        $user = Auth::user();
        $student = $user->student;

        if (!$student) {
            return redirect()->route('siswa.dashboard');
        }

        DB::table('ai_conversations')
            ->where('id', $id)
            ->where('student_id', $student->id)
            ->delete();

        return redirect()->route('siswa.ai.index')->with('success', 'Sesi percakapan berhasil dihapus.');
    }
}
