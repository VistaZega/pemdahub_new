<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\AiStudentAssistantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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

        $prefillMode = in_array($request->query('mode'), ['tutor', 'bk_consultation', 'lms_assistant'], true)
            ? $request->query('mode')
            : null;
        $prefillPrompt = $request->query('prompt');

        return view('siswa.ai.index', compact(
            'student',
            'conversations',
            'activeConversation',
            'messages',
            'usageInfo',
            'prefillMode',
            'prefillPrompt'
        ));
    }

    /**
     * Return personalized AI Daily Study Briefing for student dashboard widget.
     */
    public function dailyBriefing(Request $request)
    {
        $student = Auth::user()?->student;
        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Siswa tidak ditemukan.'], 403);
        }

        $result = $this->aiService->getDailyBriefing($student, $request->boolean('refresh'));
        return response()->json($result);
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
     * Send message to Pembda AI via AJAX/Fetch (non-streaming fallback).
     */
    public function sendMessage(Request $request)
    {
        $user = Auth::user();
        $student = $user->student;

        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Siswa tidak ditemukan.'], 403);
        }

        $prep = $this->prepareChatSubmission($request, $student);

        $aiResponse = $this->aiService->generateResponse(
            $student,
            $prep['message'],
            $prep['conversation']->mode,
            $prep['history'],
            $prep['image_data_uri']
        );

        if (!$aiResponse['success']) {
            return response()->json([
                'success' => false,
                'message' => $aiResponse['message'],
                'is_quota_error' => $aiResponse['is_quota_error'] ?? false
            ], 422);
        }

        $aiMsgId = DB::table('ai_messages')->insertGetId([
            'conversation_id' => $prep['conversation']->id,
            'sender' => 'ai',
            'message' => $aiResponse['message'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ai_conversations')
            ->where('id', $prep['conversation']->id)
            ->update(['updated_at' => now()]);

        $usageInfo = $this->aiService->getUsageInfo($student->id);

        return response()->json([
            'success' => true,
            'conversation_id' => $prep['conversation']->id,
            'image_url' => $prep['image_url'],
            'ai_message' => [
                'id' => $aiMsgId,
                'message' => $aiResponse['message'],
                'created_at' => now()->format('H:i')
            ],
            'usage' => $usageInfo
        ]);
    }

    /**
     * Stream AI response chunk-by-chunk via Server-Sent Events (SSE).
     */
    public function streamMessage(Request $request)
    {
        $user = Auth::user();
        $student = $user->student;

        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Siswa tidak ditemukan.'], 403);
        }

        $prep = $this->prepareChatSubmission($request, $student);
        $conversation = $prep['conversation'];

        return response()->stream(function () use ($student, $prep, $conversation) {
            if (ob_get_level() > 0) {
                @ob_end_flush();
            }

            $emit = function (string $event, array $payload) {
                echo "event: {$event}\n";
                echo 'data: ' . json_encode($payload, JSON_UNESCAPED_UNICODE) . "\n\n";
                if (ob_get_level() > 0) {
                    @ob_flush();
                }
                flush();
            };

            $emit('meta', [
                'conversation_id'    => $conversation->id,
                'conversation_title' => $conversation->title,
                'image_url'          => $prep['image_url'],
            ]);

            $aiResponse = $this->aiService->streamResponse(
                $student,
                $prep['message'],
                $conversation->mode,
                $prep['history'],
                $prep['image_data_uri'],
                function (string $delta) use ($emit) {
                    $emit('chunk', ['delta' => $delta]);
                }
            );

            if (!$aiResponse['success']) {
                $emit('error', [
                    'message'        => $aiResponse['message'],
                    'is_quota_error' => $aiResponse['is_quota_error'] ?? false,
                ]);
                return;
            }

            $aiMsgId = DB::table('ai_messages')->insertGetId([
                'conversation_id' => $conversation->id,
                'sender'          => 'ai',
                'message'         => $aiResponse['message'],
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            DB::table('ai_conversations')
                ->where('id', $conversation->id)
                ->update(['updated_at' => now()]);

            $emit('done', [
                'conversation_id' => $conversation->id,
                'ai_message_id'   => $aiMsgId,
                'full_message'    => $aiResponse['message'],
                'usage'           => $this->aiService->getUsageInfo($student->id),
            ]);
        }, 200, [
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache, no-store, must-revalidate',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * Validate request, handle optional question image upload, load latest 10 history messages, and store student message.
     */
    protected function prepareChatSubmission(Request $request, Student $student): array
    {
        $validated = $request->validate([
            'conversation_id' => 'nullable|integer',
            'mode'            => 'required|in:tutor,bk_consultation,lms_assistant',
            'message'         => 'nullable|string|max:3000',
            'image'           => 'nullable|image|mimes:jpeg,jpg,png,webp|max:4096',
        ]);

        $messageText = trim((string) ($validated['message'] ?? ''));
        if ($messageText === '' && !$request->hasFile('image')) {
            abort(422, 'Pesan atau foto soal tidak boleh kosong.');
        }
        if ($messageText === '' && $request->hasFile('image')) {
            $messageText = 'Tolong bantu jelaskan dan selesaikan soal pada gambar ini langkah demi langkah.';
        }

        $imageUrl = null;
        $imageDataUri = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $mime = $file->getMimeType() ?: 'image/jpeg';
            $rawBytes = file_get_contents($file->getRealPath());
            if ($rawBytes !== false) {
                $imageDataUri = 'data:' . $mime . ';base64,' . base64_encode($rawBytes);
            }
            $storedPath = $file->store('ai-uploads/' . $student->id, 'public');
            $imageUrl = Storage::url($storedPath);
        }

        $conversationId = $validated['conversation_id'] ?? null;
        $conversation = null;

        if ($conversationId) {
            $conversation = DB::table('ai_conversations')
                ->where('id', $conversationId)
                ->where('student_id', $student->id)
                ->first();
        }

        if (!$conversation) {
            $title = Str::limit($messageText, 35, '...');
            $conversationId = DB::table('ai_conversations')->insertGetId([
                'student_id' => $student->id,
                'mode'       => $validated['mode'],
                'title'      => $title ?: 'Percakapan Baru',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $conversation = DB::table('ai_conversations')->where('id', $conversationId)->first();
        }

        // Ambil 10 pesan TERBARU (perbaikan bug: sebelumnya mengambil 10 pesan pertama)
        $rawHistory = DB::table('ai_messages')
            ->where('conversation_id', $conversation->id)
            ->orderBy('id', 'desc')
            ->take(10)
            ->get()
            ->reverse()
            ->values();

        $historyArray = [];
        foreach ($rawHistory as $h) {
            $historyArray[] = [
                'sender'  => $h->sender,
                'message' => $h->message,
            ];
        }

        $metadata = $imageUrl ? json_encode(['image_url' => $imageUrl]) : null;
        DB::table('ai_messages')->insertGetId([
            'conversation_id' => $conversation->id,
            'sender'          => 'student',
            'message'         => $messageText,
            'metadata'        => $metadata,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        if ($rawHistory->isEmpty()) {
            $newTitle = Str::limit($messageText, 35, '...');
            DB::table('ai_conversations')
                ->where('id', $conversation->id)
                ->update([
                    'title'      => $newTitle,
                    'updated_at' => now(),
                ]);
            $conversation->title = $newTitle;
        }

        return [
            'conversation'   => $conversation,
            'message'        => $messageText,
            'history'        => $historyArray,
            'image_url'      => $imageUrl,
            'image_data_uri' => $imageDataUri,
        ];
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
