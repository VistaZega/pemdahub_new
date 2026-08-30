<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class FcmService
{
    public function sendToUser(int $userId, string $title, string $body, array $data = [])
    {
        $serverKey = config('services.firebase.server_key');
        
        if (empty($serverKey)) {
            Log::warning('FCM Server Key is not set. Cannot send notification.');
            return false;
        }

        $tokens = DB::table('device_tokens')
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->pluck('device_token')
            ->toArray();

        if (empty($tokens)) {
            return false;
        }

        $successCount = 0;

        foreach ($tokens as $token) {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $serverKey,
                'Content-Type' => 'application/json',
            ])->post('https://fcm.googleapis.com/fcm/send', [
                'to' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => $data,
            ]);

            if ($response->successful()) {
                $successCount++;
            } else {
                Log::error('FCM Send Error: ' . $response->body());
            }
        }

        return $successCount > 0;
    }
}
