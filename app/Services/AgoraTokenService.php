<?php

namespace App\Services;

require_once __DIR__ . '/Agora/RtcTokenBuilder2.php';

use RtcTokenBuilder2;
use Illuminate\Support\Facades\Log;

class AgoraTokenService
{
    const ROLE_PUBLISHER  = RtcTokenBuilder2::ROLE_PUBLISHER;  // 1 - Guru (bisa publish audio/video)
    const ROLE_SUBSCRIBER = RtcTokenBuilder2::ROLE_SUBSCRIBER; // 2 - Siswa (mendengarkan/menonton & interaksi)

    /**
     * Generate RTC Token untuk room video conference
     *
     * @param string $channelName Nama channel/room (misal: pembda-lms-12)
     * @param int $uid User ID (integer)
     * @param int $role ROLE_PUBLISHER atau ROLE_SUBSCRIBER
     * @param int $expireSeconds Durasi aktif token (default 2 jam / 7200 detik)
     * @return string
     */
    public static function buildToken(string $channelName, int $uid, int $role = self::ROLE_PUBLISHER, int $expireSeconds = 7200): string
    {
        $appId = config('services.agora.app_id');
        $appCertificate = config('services.agora.app_certificate');

        if (empty($appId)) {
            Log::warning('Agora App ID belum dikonfigurasi di .env (AGORA_APP_ID)');
            return '';
        }

        // Jika certificate kosong, project di console Agora mungkin diset mode "App ID only"
        if (empty($appCertificate)) {
            return '';
        }

        try {
            return RtcTokenBuilder2::buildTokenWithUid(
                $appId,
                $appCertificate,
                $channelName,
                $uid,
                $role,
                $expireSeconds,
                $expireSeconds
            );
        } catch (\Throwable $e) {
            Log::error('Gagal generate Agora RTC token: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Standarisasi nama channel Agora untuk course/mata pelajaran LMS
     */
    public static function generateChannelName(int $courseId): string
    {
        return 'pembda-lms-' . $courseId;
    }
}
