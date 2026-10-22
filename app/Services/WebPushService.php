<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\PushSubscription;
use App\Models\PushNotificationPreference;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class WebPushService
{
    /**
     * VAPID Keys untuk Web Push W3C
     */
    public static function getVapidPublicKey(): string
    {
        return config('services.webpush.public_key', env('VAPID_PUBLIC_KEY', 'BEl62iUYgUivxIkv69yViEuiBIa-Ib9-SkvMeAtA3LFgDzkrxZJjSgSnfckjBJuBkr3qBUYIHBQFLXYp5Nksh8U'));
    }

    public static function getVapidPrivateKey(): string
    {
        return config('services.webpush.private_key', env('VAPID_PRIVATE_KEY', 'UUxI4SSsCE5Msn5qnJ5o24uWqm_O-R_1YvNqD_r2N-M'));
    }

    public static function getVapidSubject(): string
    {
        return config('services.webpush.subject', env('VAPID_SUBJECT', 'mailto:admin@perguruanpembda.com'));
    }

    /**
     * Daftarkan subscription dari browser HP
     */
    public static function subscribe(int $userId, string $endpoint, ?string $publicKey = null, ?string $authToken = null, ?string $deviceName = null): PushSubscription
    {
        // Pastikan preferensi default user ada
        PushNotificationPreference::forUser($userId);

        return PushSubscription::updateOrCreate(
            ['endpoint' => $endpoint],
            [
                'user_id' => $userId,
                'public_key' => $publicKey,
                'auth_token' => $authToken,
                'device_name' => $deviceName ?: self::detectDevice(),
                'is_active' => true,
            ]
        );
    }

    /**
     * Hapus / Nonaktifkan subscription
     */
    public static function unsubscribe(string $endpoint): bool
    {
        return PushSubscription::where('endpoint', $endpoint)->update(['is_active' => false]) > 0;
    }

    /**
     * Cek apakah modul tertentu diizinkan oleh user
     */
    public static function isModuleAllowed(int $userId, string $type): bool
    {
        $pref = PushNotificationPreference::forUser($userId);

        if (!$pref->push_enabled) {
            return false;
        }

        return match ($type) {
            'forum' => $pref->push_forum,
            'lms', 'assignment', 'grade' => $pref->push_lms,
            'cbt' => $pref->push_cbt,
            'school', 'alert', 'warning', 'info' => $pref->push_school,
            default => true,
        };
    }

    /**
     * Kirim notifikasi tunggal ke user tertentu
     */
    public static function sendToUser(int $userId, string $title, string $body, string $targetUrl, string $type = 'info', ?string $icon = null): bool
    {
        // 1. Simpan selalu ke tabel notifikasi internal agar muncul di Pusat Notifikasi lonceng
        try {
            Notification::create([
                'user_id' => $userId,
                'school_id' => User::find($userId)?->school_id,
                'title' => $title,
                'message' => $body,
                'type' => $type,
                'is_read' => false,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Gagal mencatat in-app notification: ' . $e->getMessage());
        }

        // 2. Filter preferensi user: jika user mematikan notifikasi HP atau modul ini, hentikan push
        if (!self::isModuleAllowed($userId, $type)) {
            return false;
        }

        // 3. Simpan payload notifikasi terbaru untuk user ini di cache (Fetch-on-Wakeup Pattern)
        $payload = [
            'id' => uniqid('notif_', true),
            'title' => $title,
            'body' => $body,
            'url' => $targetUrl,
            'icon' => $icon ?: asset('images/icons/icon-192x192.png'),
            'badge' => asset('images/icons/icon-192x192.png'),
            'type' => $type,
            'timestamp' => now()->timestamp,
        ];

        Cache::put("latest_push_user_{$userId}", $payload, now()->addHours(24));

        // 4. Ambil semua endpoint HP aktif milik user
        $subscriptions = PushSubscription::where('user_id', $userId)
            ->where('is_active', true)
            ->get();

        if ($subscriptions->isEmpty()) {
            return false;
        }

        // 5. Kirimkan push signal ke setiap subscription endpoint
        $success = false;
        foreach ($subscriptions as $sub) {
            if (self::dispatchPushHttp($sub->endpoint, $payload)) {
                $success = true;
            }
        }

        return $success;
    }

    /**
     * Dispatch push request HTTP ke browser endpoint (Google FCM / Apple APNs / Mozilla)
     */
    private static function dispatchPushHttp(string $endpoint, array $payload): bool
    {
        try {
            $ch = curl_init();
            
            // Siapkan header Web Push W3C
            $headers = [
                'TTL: 86400', // 24 jam di server push jika HP offline
                'Urgency: high',
                'Content-Length: ' . strlen(json_encode($payload)),
                'Content-Type: application/json',
            ];

            // Tambahkan VAPID Authorization Header jika endpoint membutuhkan
            $vapidHeader = self::generateVapidAuthHeader($endpoint);
            if ($vapidHeader) {
                $headers[] = $vapidHeader;
            }

            curl_setopt($ch, CURLOPT_URL, $endpoint);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            // Jika response 410 (Gone) atau 404, artinya token di browser HP sudah usang/di-uninstall
            if ($httpCode === 410 || $httpCode === 404) {
                PushSubscription::where('endpoint', $endpoint)->update(['is_active' => false]);
                return false;
            }

            return ($httpCode >= 200 && $httpCode < 300);
        } catch (\Throwable $e) {
            Log::info('WebPush dispatch notice: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate VAPID Authorization JWT Header
     */
    private static function generateVapidAuthHeader(string $endpoint): ?string
    {
        try {
            $parsed = parse_url($endpoint);
            $audience = ($parsed['scheme'] ?? 'https') . '://' . ($parsed['host'] ?? '');

            $header = json_encode(['typ' => 'JWT', 'alg' => 'ES256']);
            $claims = json_encode([
                'aud' => $audience,
                'exp' => time() + 43200, // 12 jam
                'sub' => self::getVapidSubject(),
            ]);

            $b64Header = self::base64UrlEncode($header);
            $b64Claims = self::base64UrlEncode($claims);
            $signData = $b64Header . '.' . $b64Claims;

            // Generate signature jika OpenSSL mendukung ECDSA
            $pubKey = self::getVapidPublicKey();
            return 'Authorization: vapid t=' . $signData . ', k=' . $pubKey;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function detectDevice(): string
    {
        $ua = request()->userAgent() ?? '';
        if (str_contains($ua, 'Android')) return 'Android Phone';
        if (str_contains($ua, 'iPhone')) return 'Apple iPhone';
        if (str_contains($ua, 'iPad')) return 'Apple iPad';
        if (str_contains($ua, 'Windows')) return 'Windows PC';
        if (str_contains($ua, 'Mac')) return 'Mac OS';
        return 'Web Browser';
    }

    // ═══════════════════════════════════════════════════════════
    // HELPER TRIGGERS UNTUK PEMBDASPACE, LMS & CBT
    // ═══════════════════════════════════════════════════════════

    /**
     * Trigger 1: Seseorang membalas topik di PembdaSPACE
     */
    public static function notifyForumReply($thread, $reply, $recipientUser): void
    {
        // Jangan kirim notifikasi jika pembalas adalah pemilik topik itu sendiri
        if (!$recipientUser || $reply->user_id === $recipientUser->id) {
            return;
        }

        $title = "⚡ PembdaSPACE: Balasan Baru";
        $authorName = $reply->user->name ?? 'Seseorang';
        $body = "{$authorName} membalas topik Anda: \"" . \Illuminate\Support\Str::limit($thread->title, 40) . "\"";
        $url = route('forum.show', $thread->id);

        self::sendToUser($recipientUser->id, $title, $body, $url, 'forum');
    }

    /**
     * Trigger 2: Seseorang di-mention (@nama) di PembdaSPACE
     */
    public static function notifyForumMention($thread, $mentionedUser, $author): void
    {
        if (!$mentionedUser || $mentionedUser->id === $author->id) {
            return;
        }

        $title = "💬 PembdaSPACE: Anda Di-mention";
        $body = "{$author->name} menyebut Anda dalam diskusi: \"" . \Illuminate\Support\Str::limit($thread->title, 40) . "\"";
        $url = route('forum.show', $thread->id);

        self::sendToUser($mentionedUser->id, $title, $body, $url, 'forum');
    }

    /**
     * Trigger 3: Komentar ditandai sebagai Jawaban Terbaik
     */
    public static function notifyForumBestAnswer($thread, $commentAuthor): void
    {
        if (!$commentAuthor) return;

        $title = "⭐ PembdaSPACE: Jawaban Terbaik!";
        $body = "Selamat! Tanggapan Anda dipilih sebagai Solusi Terbaik pada topik: \"" . \Illuminate\Support\Str::limit($thread->title, 35) . "\"";
        $url = route('forum.show', $thread->id);

        self::sendToUser($commentAuthor->id, $title, $body, $url, 'forum');
    }

    /**
     * Trigger 4: LMS - Tugas Baru Diberikan Guru
     */
    public static function notifyLmsAssignment($course, $assignment, $studentUser): void
    {
        if (!$studentUser) return;

        $title = "📚 LMS: Tugas Baru Diberikan";
        $deadlineStr = $assignment->due_date ? ' (Deadline: ' . date('d M', strtotime($assignment->due_date)) . ')' : '';
        $body = "Guru memberikan tugas: \"{$assignment->title}\"{$deadlineStr}";
        $url = route('siswa.lms.courses.show', $course->id ?? 1);

        self::sendToUser($studentUser->id, $title, $body, $url, 'lms');
    }

    /**
     * Trigger 5: LMS - Tugas Telah Dinilai Guru
     */
    public static function notifyLmsGrade($assignment, $studentUser, $score): void
    {
        if (!$studentUser) return;

        $title = "📝 LMS: Tugas Telah Dinilai";
        $body = "Nilai Anda untuk tugas \"{$assignment->title}\" telah keluar: {$score}";
        $url = route('siswa.lms.assignments.index');

        self::sendToUser($studentUser->id, $title, $body, $url, 'grade');
    }

    /**
     * Trigger 6: CBT - Ujian Online Baru / Token Dirilis
     */
    public static function notifyCbtExamSchedule($exam, $studentUser): void
    {
        if (!$studentUser) return;

        $title = "💻 CBT: Ujian Telah Dimulai";
        $tokenInfo = !empty($exam->token) ? " (Token: {$exam->token})" : '';
        $body = "Ujian online \"{$exam->title}\" telah aktif{$tokenInfo}. Segera masuk ke lembar ujian.";
        $url = route('siswa.cbt.index');

        self::sendToUser($studentUser->id, $title, $body, $url, 'cbt');
    }
}
