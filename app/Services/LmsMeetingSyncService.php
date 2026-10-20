<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class LmsMeetingSyncService
{
    const TTL_SECONDS = 7200; // 2 Jam (Sesuai durasi sesi kelas)

    /**
     * Daftar pilihan suasana hati (Mood) siswa
     */
    public static function getAvailableMoods(): array
    {
        return [
            'semangat'    => ['key' => 'semangat',    'label' => 'Semangat',    'icon' => '🔥', 'color' => '#ea580c', 'bg' => '#ffedd5'],
            'ngantuk'     => ['key' => 'ngantuk',     'label' => 'Ngantuk',     'icon' => '😴', 'color' => '#4f46e5', 'bg' => '#e0e7ff'],
            'bingung'     => ['key' => 'bingung',     'label' => 'Bingung',     'icon' => '😵‍💫', 'color' => '#ca8a04', 'bg' => '#fef9c3'],
            'malas'       => ['key' => 'malas',       'label' => 'Malas',       'icon' => '🥱', 'color' => '#64748b', 'bg' => '#f1f5f9'],
            'sedih'       => ['key' => 'sedih',       'label' => 'Sedih',       'icon' => '😢', 'color' => '#2563eb', 'bg' => '#dbeafe'],
            'belum_sadar' => ['key' => 'belum_sadar', 'label' => 'Belum Sadar', 'icon' => '🥴', 'color' => '#9333ea', 'bg' => '#f3e8ff'],
            'masa_bodoh'  => ['key' => 'masa_bodoh',  'label' => 'Masa Bodoh',  'icon' => '🙄', 'color' => '#475569', 'bg' => '#f8fafc'],
            'sakit'       => ['key' => 'sakit',       'label' => 'Sakit',       'icon' => '🤒', 'color' => '#dc2626', 'bg' => '#fee2e2'],
            'lemas'       => ['key' => 'lemas',       'label' => 'Lemas',       'icon' => '🫠', 'color' => '#0d9488', 'bg' => '#ccfbf1'],
            'gembira'     => ['key' => 'gembira',     'label' => 'Gembira',     'icon' => '😄', 'color' => '#16a34a', 'bg' => '#dcfce7'],
            'fokus'       => ['key' => 'fokus',       'label' => 'Fokus',       'icon' => '🧐', 'color' => '#0284c7', 'bg' => '#e0f2fe'],
        ];
    }

    /**
     * Cache keys helper
     */
    protected static function getSignalsKey(int $courseId): string
    {
        return 'lms_meeting_signals_' . $courseId;
    }

    protected static function getChatsKey(int $courseId): string
    {
        return 'lms_meeting_chats_' . $courseId;
    }

    protected static function getPollKey(int $courseId): string
    {
        return 'lms_meeting_poll_' . $courseId;
    }

    protected static function getQaKey(int $courseId): string
    {
        return 'lms_meeting_qa_' . $courseId;
    }

    protected static function getQuizKey(int $courseId): string
    {
        return 'lms_meeting_quiz_' . $courseId;
    }

    /**
     * Heartbeat / Sync status pengguna (Guru atau Siswa)
     */
    public static function syncUser(int $courseId, int $userId, string $name, string $role, ?int $studentId = null, array $extra = []): array
    {
        $signalsKey = self::getSignalsKey($courseId);
        $signals = Cache::get($signalsKey, []);
        $now = time();

        $existing = $signals[$userId] ?? [
            'user_id'         => $userId,
            'name'            => $name,
            'role'            => $role,
            'student_id'      => $studentId,
            'raise_hand'      => false,
            'raise_hand_time' => null,
            'mood'            => 'semangat',
            'created_at'      => $now,
        ];

        // Update fields
        $existing['name'] = $name;
        $existing['role'] = $role;
        $existing['last_seen'] = $now;

        if (isset($extra['raise_hand'])) {
            $existing['raise_hand'] = (bool)$extra['raise_hand'];
            if ($existing['raise_hand'] && empty($existing['raise_hand_time'])) {
                $existing['raise_hand_time'] = $now;
            } elseif (!$existing['raise_hand']) {
                $existing['raise_hand_time'] = null;
            }
        }

        if (!empty($extra['mood'])) {
            $moodKey = strtolower(trim($extra['mood']));
            $available = self::getAvailableMoods();
            if (isset($available[$moodKey])) {
                $existing['mood'] = $moodKey;
            }
        }

        $signals[$userId] = $existing;

        // Bersihkan user yang sudah offline (> 90 detik tanpa heartbeat)
        foreach ($signals as $uid => $sig) {
            if (($now - ($sig['last_seen'] ?? 0)) > 90) {
                unset($signals[$uid]);
            }
        }

        Cache::put($signalsKey, $signals, self::TTL_SECONDS);

        return $signals;
    }

    /**
     * Ubah suasana hati (Mood)
     */
    public static function setMood(int $courseId, int $userId, string $moodKey): bool
    {
        $signalsKey = self::getSignalsKey($courseId);
        $signals = Cache::get($signalsKey, []);

        if (isset($signals[$userId])) {
            $moodKey = strtolower(trim($moodKey));
            $available = self::getAvailableMoods();
            if (isset($available[$moodKey])) {
                $signals[$userId]['mood'] = $moodKey;
                $signals[$userId]['last_seen'] = time();
                Cache::put($signalsKey, $signals, self::TTL_SECONDS);
                return true;
            }
        }

        return false;
    }

    /**
     * Toggle status angkat tangan
     */
    public static function setRaiseHand(int $courseId, int $userId, bool $state): bool
    {
        $signalsKey = self::getSignalsKey($courseId);
        $signals = Cache::get($signalsKey, []);

        if (isset($signals[$userId])) {
            $signals[$userId]['raise_hand'] = $state;
            $signals[$userId]['raise_hand_time'] = $state ? time() : null;
            $signals[$userId]['last_seen'] = time();
            Cache::put($signalsKey, $signals, self::TTL_SECONDS);
            return true;
        }

        return false;
    }

    /**
     * Guru menurunkan tangan siswa
     */
    public static function dismissHand(int $courseId, int $targetUserId): bool
    {
        $signalsKey = self::getSignalsKey($courseId);
        $signals = Cache::get($signalsKey, []);

        if (isset($signals[$targetUserId])) {
            $signals[$targetUserId]['raise_hand'] = false;
            $signals[$targetUserId]['raise_hand_time'] = null;
            Cache::put($signalsKey, $signals, self::TTL_SECONDS);
            return true;
        }

        return false;
    }

    /**
     * Kirim Pesan Chat (Publik ke Semua atau Pribadi Direct Message)
     */
    public static function sendChat(
        int $courseId,
        int $senderId,
        string $senderName,
        string $senderRole,
        $recipientId = 'all',
        string $recipientName = 'Semua',
        string $text = ''
    ): array {
        $text = trim($text);
        if ($text === '') {
            return [];
        }

        $chatsKey = self::getChatsKey($courseId);
        $chats = Cache::get($chatsKey, []);

        $isPrivate = ($recipientId !== 'all' && (int)$recipientId > 0);
        $message = [
            'id'             => uniqid('chat_'),
            'sender_id'      => $senderId,
            'sender_name'    => $senderName,
            'sender_role'    => $senderRole,
            'recipient_id'   => $isPrivate ? (int)$recipientId : 'all',
            'recipient_name' => $isPrivate ? $recipientName : 'Semua Orang',
            'is_private'     => $isPrivate,
            'text'           => htmlspecialchars($text, ENT_QUOTES, 'UTF-8'),
            'timestamp'      => time(),
            'time_formatted' => date('H:i'),
        ];

        $chats[] = $message;

        // Pertahankan maksimal 150 pesan terakhir dalam sesi kelas
        if (count($chats) > 150) {
            $chats = array_slice($chats, -150);
        }

        Cache::put($chatsKey, $chats, self::TTL_SECONDS);

        return $message;
    }

    /**
     * Ambil state lengkap yang terfilter untuk user tertentu
     */
    public static function getState(int $courseId, int $forUserId): array
    {
        $signalsKey = self::getSignalsKey($courseId);
        $chatsKey   = self::getChatsKey($courseId);

        $signals = Cache::get($signalsKey, []);
        $allChats = Cache::get($chatsKey, []);
        $availableMoods = self::getAvailableMoods();

        // 1. Filter chat agar siswa tidak melihat pesan pribadi orang lain
        $visibleChats = array_values(array_filter($allChats, function ($chat) use ($forUserId) {
            if (!$chat['is_private'] || $chat['recipient_id'] === 'all') {
                return true;
            }
            // Terlihat hanya jika user adalah pengirim atau penerima
            return ((int)$chat['sender_id'] === $forUserId || (int)$chat['recipient_id'] === $forUserId);
        }));

        // 2. Format peserta & ringkasan mood
        $attendees = [];
        $raisedHands = [];
        $moodSummary = [];

        foreach ($signals as $uid => $sig) {
            $moodKey = $sig['mood'] ?? 'semangat';
            $moodMeta = $availableMoods[$moodKey] ?? $availableMoods['semangat'];

            $attendeeData = [
                'user_id'    => $sig['user_id'],
                'name'       => $sig['name'],
                'role'       => $sig['role'],
                'raise_hand' => (bool)($sig['raise_hand'] ?? false),
                'mood'       => $moodMeta['key'],
                'mood_label' => $moodMeta['label'],
                'mood_icon'  => $moodMeta['icon'],
                'mood_color' => $moodMeta['color'],
                'mood_bg'    => $moodMeta['bg'],
            ];

            $attendees[] = $attendeeData;

            if ($attendeeData['raise_hand']) {
                $raisedHands[] = [
                    'user_id' => $sig['user_id'],
                    'name'    => $sig['name'],
                    'role'    => $sig['role'],
                    'time'    => $sig['raise_hand_time'] ?? 0,
                ];
            }

            if ($sig['role'] === 'siswa') {
                $moodSummary[$moodMeta['label']] = ($moodSummary[$moodMeta['label']] ?? 0) + 1;
            }
        }

        // Urutkan siswa yang angkat tangan berdasarkan siapa yang duluan
        usort($raisedHands, fn($a, $b) => ($a['time'] ?? 0) <=> ($b['time'] ?? 0));

        $sessionKey = 'lms_meeting_session_' . $courseId;
        $isActive = Cache::has($sessionKey);

        return [
            'is_active'       => $isActive,
            'attendees'       => $attendees,
            'raised_hands'    => $raisedHands,
            'chats'           => $visibleChats,
            'poll'            => self::getPoll($courseId, $forUserId),
            'qa_list'         => self::getQuestions($courseId, $forUserId),
            'quiz'            => self::getQuiz($courseId, $forUserId),
            'mood_summary'    => $moodSummary,
            'available_moods' => $availableMoods,
            'server_time'     => time(),
        ];
    }

    // ─────────────────────────────────────────────────────────
    // FITUR POLLING KILAT (QUICK POLL)
    // ─────────────────────────────────────────────────────────

    public static function createPoll(int $courseId, string $question, array $options): array
    {
        $options = array_values(array_filter(array_map('trim', $options)));
        if (empty($options)) {
            $options = ['Paham', 'Masih Bingung'];
        }

        $poll = [
            'id'          => uniqid('poll_'),
            'question'    => htmlspecialchars(trim($question), ENT_QUOTES, 'UTF-8'),
            'options'     => array_map(fn($o) => htmlspecialchars($o, ENT_QUOTES, 'UTF-8'), $options),
            'created_at'  => time(),
            'is_active'   => true,
            'votes'       => [], // userId => optionIndex
            'total_votes' => 0,
            'counts'      => array_fill(0, count($options), 0),
        ];

        Cache::put(self::getPollKey($courseId), $poll, self::TTL_SECONDS);
        return $poll;
    }

    public static function votePoll(int $courseId, int $userId, int $optionIndex): ?array
    {
        $pollKey = self::getPollKey($courseId);
        $poll = Cache::get($pollKey);
        if (!$poll || empty($poll['is_active'])) {
            return null;
        }

        if (!isset($poll['options'][$optionIndex])) {
            return null;
        }

        $poll['votes'][$userId] = (int)$optionIndex;
        $poll['total_votes'] = count($poll['votes']);

        // Recount
        $counts = array_fill(0, count($poll['options']), 0);
        foreach ($poll['votes'] as $uid => $idx) {
            if (isset($counts[$idx])) {
                $counts[$idx]++;
            }
        }
        $poll['counts'] = $counts;

        Cache::put($pollKey, $poll, self::TTL_SECONDS);
        return $poll;
    }

    public static function endPoll(int $courseId): ?array
    {
        $pollKey = self::getPollKey($courseId);
        $poll = Cache::get($pollKey);
        if ($poll) {
            $poll['is_active'] = false;
            $poll['ended_at'] = time();
            Cache::put($pollKey, $poll, self::TTL_SECONDS);
        }
        return $poll;
    }

    public static function clearPoll(int $courseId): void
    {
        Cache::forget(self::getPollKey($courseId));
    }

    public static function getPoll(int $courseId, ?int $forUserId = null): ?array
    {
        $poll = Cache::get(self::getPollKey($courseId));
        if (!$poll) {
            return null;
        }

        $userVoted = null;
        $hasVoted = false;
        if ($forUserId && isset($poll['votes'][$forUserId])) {
            $userVoted = (int)$poll['votes'][$forUserId];
            $hasVoted = true;
        }

        $percentages = [];
        $total = max(1, $poll['total_votes']);
        foreach ($poll['counts'] as $c) {
            $percentages[] = $poll['total_votes'] > 0 ? round(($c / $total) * 100) : 0;
        }

        return [
            'id'                => $poll['id'],
            'question'          => $poll['question'],
            'options'           => $poll['options'],
            'created_at'        => $poll['created_at'],
            'is_active'         => (bool)($poll['is_active'] ?? false),
            'total_votes'       => (int)($poll['total_votes'] ?? 0),
            'counts'            => $poll['counts'] ?? array_fill(0, count($poll['options']), 0),
            'percentages'       => $percentages,
            'has_voted'         => $hasVoted,
            'user_voted_option' => $userVoted,
        ];
    }

    // ─────────────────────────────────────────────────────────
    // FITUR TANYA JAWAB TERSTRUKTUR (Q&A QUEUE & UPVOTE)
    // ─────────────────────────────────────────────────────────

    public static function askQuestion(int $courseId, int $userId, string $userName, string $userRole, string $text): ?array
    {
        $text = trim($text);
        if ($text === '') {
            return null;
        }

        $qaKey = self::getQaKey($courseId);
        $questions = Cache::get($qaKey, []);

        $item = [
            'id'             => uniqid('qa_'),
            'user_id'        => $userId,
            'user_name'      => $userName,
            'user_role'      => $userRole,
            'question'       => htmlspecialchars($text, ENT_QUOTES, 'UTF-8'),
            'created_at'     => time(),
            'time_formatted' => date('H:i'),
            'upvotes'        => [$userId], // Pembuat otomatis upvote
            'upvote_count'   => 1,
            'status'         => 'pending', // 'pending' | 'answering' | 'answered'
        ];

        $questions[] = $item;

        if (count($questions) > 100) {
            $questions = array_slice($questions, -100);
        }

        Cache::put($qaKey, $questions, self::TTL_SECONDS);
        return $item;
    }

    public static function upvoteQuestion(int $courseId, string $questionId, int $userId): array
    {
        $qaKey = self::getQaKey($courseId);
        $questions = Cache::get($qaKey, []);

        foreach ($questions as &$q) {
            if ($q['id'] === $questionId) {
                $idx = array_search($userId, $q['upvotes']);
                if ($idx !== false) {
                    unset($q['upvotes'][$idx]);
                    $q['upvotes'] = array_values($q['upvotes']);
                } else {
                    $q['upvotes'][] = $userId;
                }
                $q['upvote_count'] = count($q['upvotes']);
                break;
            }
        }

        Cache::put($qaKey, $questions, self::TTL_SECONDS);
        return $questions;
    }

    public static function updateQuestionStatus(int $courseId, string $questionId, string $status): array
    {
        $qaKey = self::getQaKey($courseId);
        $questions = Cache::get($qaKey, []);

        if ($status === 'delete') {
            $questions = array_values(array_filter($questions, fn($q) => $q['id'] !== $questionId));
        } else {
            foreach ($questions as &$q) {
                if ($q['id'] === $questionId) {
                    $q['status'] = in_array($status, ['pending', 'answering', 'answered']) ? $status : 'pending';
                    break;
                }
            }
        }

        Cache::put($qaKey, $questions, self::TTL_SECONDS);
        return $questions;
    }

    public static function getQuestions(int $courseId, ?int $forUserId = null): array
    {
        $qaKey = self::getQaKey($courseId);
        $questions = Cache::get($qaKey, []);

        // Sort: 'answering' di atas, lalu upvote terbanyak, lalu terbaru
        usort($questions, function ($a, $b) {
            $isAnswA = ($a['status'] ?? '') === 'answering';
            $isAnswB = ($b['status'] ?? '') === 'answering';
            if ($isAnswA && !$isAnswB) return -1;
            if (!$isAnswA && $isAnswB) return 1;

            $votesA = (int)($a['upvote_count'] ?? 0);
            $votesB = (int)($b['upvote_count'] ?? 0);
            if ($votesA !== $votesB) {
                return $votesB <=> $votesA;
            }

            return ($b['created_at'] ?? 0) <=> ($a['created_at'] ?? 0);
        });

        return array_map(function ($q) use ($forUserId) {
            $q['has_upvoted'] = $forUserId ? in_array($forUserId, $q['upvotes'] ?? []) : false;
            return $q;
        }, $questions);
    }

    // ─────────────────────────────────────────────────────────
    // FITUR FASE 4: KUIS LIVE KAHOOT-STYLE (IN-VIDEO QUIZ)
    // ─────────────────────────────────────────────────────────

    public static function startQuiz(int $courseId, string $title, array $questions): array
    {
        $cleanQuestions = [];
        foreach ($questions as $q) {
            $text = trim($q['question'] ?? '');
            $opts = array_values(array_filter(array_map('trim', (array)($q['options'] ?? []))));
            $correct = (int)($q['correct_answer'] ?? 0);
            $timeLimit = (int)($q['time_limit'] ?? 20);

            if ($text !== '' && count($opts) >= 2) {
                $cleanQuestions[] = [
                    'question'       => htmlspecialchars($text, ENT_QUOTES, 'UTF-8'),
                    'options'        => array_map(fn($o) => htmlspecialchars($o, ENT_QUOTES, 'UTF-8'), $opts),
                    'correct_answer' => $correct,
                    'time_limit'     => $timeLimit > 0 ? $timeLimit : 20,
                    'explanation'    => htmlspecialchars(trim($q['explanation'] ?? ''), ENT_QUOTES, 'UTF-8'),
                ];
            }
        }

        if (empty($cleanQuestions)) {
            $cleanQuestions = [
                [
                    'question'       => 'Contoh Soal: Manakah rumus Hukum Ohm yang benar?',
                    'options'        => ['V = I × R', 'P = V × I', 'I = V × R', 'R = V × I'],
                    'correct_answer' => 0,
                    'time_limit'     => 20,
                    'explanation'    => 'Hukum Ohm menyatakan bahwa beda potensial (V) sebanding dengan kuat arus (I) dikalikan hambatan (R).',
                ]
            ];
        }

        $quiz = [
            'id'                     => uniqid('quiz_live_'),
            'title'                  => htmlspecialchars(trim($title ?: 'Kuis Interaktif KBM'), ENT_QUOTES, 'UTF-8'),
            'status'                 => 'waiting',
            'current_question_index' => 0,
            'questions'              => $cleanQuestions,
            'question_started_at'    => null,
            'players'                => [],
            'answers_count'          => 0,
            'created_at'             => time(),
        ];

        Cache::put(self::getQuizKey($courseId), $quiz, self::TTL_SECONDS);
        return $quiz;
    }

    public static function startQuestion(int $courseId, int $index = 0): ?array
    {
        $quizKey = self::getQuizKey($courseId);
        $quiz = Cache::get($quizKey);
        if (!$quiz) return null;

        $quiz['status'] = 'question';
        $quiz['current_question_index'] = $index;
        $quiz['question_started_at'] = time();
        $quiz['answers_count'] = 0;

        // Reset last_answer status for players on new question
        foreach ($quiz['players'] as &$pl) {
            unset($pl['last_answer']);
            unset($pl['last_answer_correct']);
            unset($pl['last_answer_points']);
        }

        Cache::put($quizKey, $quiz, self::TTL_SECONDS);
        return $quiz;
    }

    public static function submitQuizAnswer(int $courseId, int $userId, string $userName, int $answerIndex): ?array
    {
        $quizKey = self::getQuizKey($courseId);
        $quiz = Cache::get($quizKey);
        if (!$quiz || ($quiz['status'] ?? '') !== 'question') {
            return null;
        }

        $idx = $quiz['current_question_index'] ?? 0;
        $currentQ = $quiz['questions'][$idx] ?? null;
        if (!$currentQ) return null;

        // Init player if not exists
        if (!isset($quiz['players'][$userId])) {
            $quiz['players'][$userId] = [
                'user_id'       => $userId,
                'name'          => $userName,
                'score'         => 0,
                'correct_count' => 0,
                'streak'        => 0,
                'answers'       => [],
            ];
        }

        $player = &$quiz['players'][$userId];

        // Cegah submit ganda untuk soal yang sama
        if (isset($player['answers'][$idx])) {
            return $quiz;
        }

        $isCorrect = ((int)$answerIndex === (int)$currentQ['correct_answer']);
        $elapsed = max(0, time() - ($quiz['question_started_at'] ?? time()));
        $timeLimit = (int)($currentQ['time_limit'] ?? 20);

        $points = 0;
        if ($isCorrect) {
            $ratio = max(0, ($timeLimit - $elapsed) / max(1, $timeLimit));
            $points = (int) round(500 + ($ratio * 500)); // Rentang 500 s/d 1000 poin
            $player['score'] += $points;
            $player['correct_count']++;
            $player['streak']++;
        } else {
            $player['streak'] = 0;
        }

        $player['answers'][$idx] = (int)$answerIndex;
        $player['last_answer'] = (int)$answerIndex;
        $player['last_answer_correct'] = $isCorrect;
        $player['last_answer_points'] = $points;

        $quiz['answers_count'] = ($quiz['answers_count'] ?? 0) + 1;

        Cache::put($quizKey, $quiz, self::TTL_SECONDS);
        return $quiz;
    }

    public static function showQuizScoreboard(int $courseId): ?array
    {
        $quizKey = self::getQuizKey($courseId);
        $quiz = Cache::get($quizKey);
        if (!$quiz) return null;

        $quiz['status'] = 'scoreboard';
        Cache::put($quizKey, $quiz, self::TTL_SECONDS);
        return $quiz;
    }

    public static function endQuiz(int $courseId): ?array
    {
        $quizKey = self::getQuizKey($courseId);
        $quiz = Cache::get($quizKey);
        if (!$quiz) return null;

        $quiz['status'] = 'finished';
        $quiz['ended_at'] = time();

        // Sort players by score DESC
        $players = array_values($quiz['players'] ?? []);
        usort($players, fn($a, $b) => ($b['score'] ?? 0) <=> ($a['score'] ?? 0));

        // Award bonus reputation points to Top 3
        $bonusRanks = [
            0 => ['points' => 15, 'label' => 'Juara 1'],
            1 => ['points' => 10, 'label' => 'Juara 2'],
            2 => ['points' => 5,  'label' => 'Juara 3'],
        ];

        foreach ($bonusRanks as $rankIdx => $bonus) {
            if (isset($players[$rankIdx]) && ($players[$rankIdx]['score'] ?? 0) > 0) {
                $p = $players[$rankIdx];
                $studentUser = \App\Models\User::find($p['user_id']);
                if ($studentUser) {
                    \App\Models\ReputationLog::log(
                        $p['user_id'],
                        $bonus['points'],
                        'lms_quiz_winner',
                        "{$bonus['label']} Kuis Live PembdaTEMU: {$quiz['title']} (Skor: {$p['score']})"
                    );
                }
            }
        }

        Cache::put($quizKey, $quiz, self::TTL_SECONDS);
        return $quiz;
    }

    public static function clearQuiz(int $courseId): void
    {
        Cache::forget(self::getQuizKey($courseId));
    }

    public static function getQuiz(int $courseId, ?int $forUserId = null): ?array
    {
        $quizKey = self::getQuizKey($courseId);
        $quiz = Cache::get($quizKey);
        if (!$quiz) {
            return null;
        }

        $currentIndex = (int)($quiz['current_question_index'] ?? 0);
        $totalQuestions = count($quiz['questions'] ?? []);
        $currentQuestion = $quiz['questions'][$currentIndex] ?? null;

        $safeCurrentQ = null;
        if ($currentQuestion) {
            $safeCurrentQ = [
                'question'   => $currentQuestion['question'],
                'options'    => $currentQuestion['options'],
                'time_limit' => $currentQuestion['time_limit'] ?? 20,
            ];
            // Kunci jawaban hanya dibagikan ke siswa saat scoreboard atau finished
            if (in_array($quiz['status'], ['scoreboard', 'finished'])) {
                $safeCurrentQ['correct_answer'] = $currentQuestion['correct_answer'];
                $safeCurrentQ['explanation']    = $currentQuestion['explanation'] ?? '';
            }
        }

        $players = array_values($quiz['players'] ?? []);
        usort($players, fn($a, $b) => ($b['score'] ?? 0) <=> ($a['score'] ?? 0));

        $topPlayers = array_slice($players, 0, 5);
        $podium = array_slice($players, 0, 3);

        $myPlayer = null;
        if ($forUserId && isset($quiz['players'][$forUserId])) {
            $myPlayer = $quiz['players'][$forUserId];
            $rank = 1;
            foreach ($players as $i => $pl) {
                if ($pl['user_id'] == $forUserId) {
                    $rank = $i + 1;
                    break;
                }
            }
            $myPlayer['rank'] = $rank;
        }

        $distribution = [0 => 0, 1 => 0, 2 => 0, 3 => 0];
        foreach ($quiz['players'] ?? [] as $pl) {
            if (isset($pl['answers'][$currentIndex])) {
                $ansIdx = (int)$pl['answers'][$currentIndex];
                if (isset($distribution[$ansIdx])) {
                    $distribution[$ansIdx]++;
                }
            }
        }

        return [
            'id'                     => $quiz['id'],
            'title'                  => $quiz['title'],
            'status'                 => $quiz['status'],
            'current_question_index' => $currentIndex,
            'total_questions'        => $totalQuestions,
            'current_question'       => $safeCurrentQ,
            'question_started_at'    => $quiz['question_started_at'] ?? 0,
            'time_elapsed'           => max(0, time() - ($quiz['question_started_at'] ?? time())),
            'total_players'          => count($players),
            'answers_count'          => $quiz['answers_count'] ?? 0,
            'distribution'           => $distribution,
            'top_players'            => $topPlayers,
            'podium'                 => $podium,
            'my_player'              => $myPlayer,
        ];
    }

    /**
     * Bersihkan seluruh cache saat sesi meeting resmi diakhiri
     */
    public static function endSession(int $courseId): void
    {
        Cache::forget(self::getSignalsKey($courseId));
        Cache::forget(self::getChatsKey($courseId));
        Cache::forget(self::getPollKey($courseId));
        Cache::forget(self::getQaKey($courseId));
        Cache::forget(self::getQuizKey($courseId));
    }
}
