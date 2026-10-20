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

    /**
     * Bersihkan seluruh cache saat sesi meeting resmi diakhiri
     */
    public static function endSession(int $courseId): void
    {
        Cache::forget(self::getSignalsKey($courseId));
        Cache::forget(self::getChatsKey($courseId));
        Cache::forget(self::getPollKey($courseId));
        Cache::forget(self::getQaKey($courseId));
    }
}
