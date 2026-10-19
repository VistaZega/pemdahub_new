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

        return [
            'attendees'       => $attendees,
            'raised_hands'    => $raisedHands,
            'chats'           => $visibleChats,
            'mood_summary'    => $moodSummary,
            'available_moods' => $availableMoods,
            'server_time'     => time(),
        ];
    }
}
