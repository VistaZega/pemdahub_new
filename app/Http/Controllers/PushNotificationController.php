<?php

namespace App\Http\Controllers;

use App\Models\PushNotificationPreference;
use App\Models\PushSubscription;
use App\Services\WebPushService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class PushNotificationController extends Controller
{
    /**
     * Daftarkan subscription push dari browser HP
     */
    public function subscribe(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'endpoint' => 'required|string',
            'public_key' => 'nullable|string',
            'auth_token' => 'nullable|string',
            'device_name' => 'nullable|string',
        ]);

        $subscription = WebPushService::subscribe(
            $user->id,
            $request->endpoint,
            $request->public_key,
            $request->auth_token,
            $request->device_name
        );

        return response()->json([
            'success' => true,
            'message' => 'Perangkat HP berhasil didaftarkan untuk notifikasi.',
            'subscription_id' => $subscription->id,
        ]);
    }

    /**
     * Berhenti berlangganan push di perangkat ini
     */
    public function unsubscribe(Request $request): JsonResponse
    {
        $endpoint = $request->input('endpoint');
        if ($endpoint) {
            WebPushService::unsubscribe($endpoint);
        }

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi untuk perangkat ini telah dinonaktifkan.',
        ]);
    }

    /**
     * Status notifikasi HP & preferensi user saat ini
     */
    public function status(): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['authenticated' => false]);
        }

        $pref = PushNotificationPreference::forUser($user->id);
        $deviceCount = PushSubscription::where('user_id', $user->id)->active()->count();

        return response()->json([
            'authenticated' => true,
            'push_enabled' => (bool) $pref->push_enabled,
            'push_lms' => (bool) $pref->push_lms,
            'push_cbt' => (bool) $pref->push_cbt,
            'push_forum' => (bool) $pref->push_forum,
            'push_school' => (bool) $pref->push_school,
            'active_devices' => $deviceCount,
            'vapid_public_key' => WebPushService::getVapidPublicKey(),
        ]);
    }

    /**
     * Toggle sakelar notifikasi (Master atau per modul)
     */
    public function toggle(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false], 401);
        }

        $field = $request->input('field', 'push_enabled');
        $allowedFields = ['push_enabled', 'push_lms', 'push_cbt', 'push_forum', 'push_school'];

        if (!in_array($field, $allowedFields)) {
            return response()->json(['success' => false, 'message' => 'Field tidak valid'], 422);
        }

        $pref = PushNotificationPreference::forUser($user->id);
        $newValue = $request->has('value') ? (bool) $request->input('value') : !$pref->$field;
        $pref->$field = $newValue;
        $pref->save();

        return response()->json([
            'success' => true,
            'field' => $field,
            'value' => $newValue,
            'message' => 'Pengaturan notifikasi berhasil diperbarui.',
        ]);
    }

    /**
     * Ambil VAPID Public Key untuk browser web push registration
     */
    public function getVapidKey(): JsonResponse
    {
        return response()->json([
            'public_key' => WebPushService::getVapidPublicKey(),
        ]);
    }

    /**
     * Endpoint untuk Service Worker: ambil notifikasi terbaru saat event push diterima
     */
    public function latestPush(): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['has_notification' => false]);
        }

        $payload = Cache::get("latest_push_user_{$user->id}");

        if ($payload) {
            return response()->json([
                'has_notification' => true,
                'data' => $payload,
            ]);
        }

        // Fallback: ambil notifikasi unread terbaru dari database
        $latest = \App\Models\Notification::forUser($user->id)->unread()->latest()->first();

        if ($latest) {
            return response()->json([
                'has_notification' => true,
                'data' => [
                    'title' => $latest->title,
                    'body' => $latest->message,
                    'url' => route('notifications.index'),
                    'icon' => asset('images/icons/icon-192x192.png'),
                ],
            ]);
        }

        return response()->json(['has_notification' => false]);
    }
}
