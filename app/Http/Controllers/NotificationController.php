<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Notification center - list all notifications
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $type = $request->get('type');

        $query = Notification::forUser($user->id);

        if ($type) {
            $query->byType($type);
        }

        $notifications = $query->orderByDesc('created_at')
            ->paginate(20);

        $unreadCount = Notification::forUser($user->id)->unread()->count();

        return view('notifications.index', compact('notifications', 'unreadCount', 'type'));
    }

    /**
     * Mark single notification as read
     */
    public function markRead(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            abort(403);
        }

        $notification->markAsRead();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back();
    }

    /**
     * Mark all notifications as read
     */
    public function markAllRead()
    {
        Notification::forUser(Auth::id())
            ->unread()
            ->update(['is_read' => true, 'read_at' => now()]);

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Semua notifikasi telah ditandai dibaca.');
    }

    /**
     * Get unread count (JSON for AJAX polling)
     */
    public function unreadCount()
    {
        $count = Notification::forUser(Auth::id())->unread()->count();

        return response()->json([
            'success' => true,
            'count' => $count,
        ]);
    }

    /**
     * Get latest notifications (JSON for dropdown)
     */
    public function latest()
    {
        $notifications = Notification::forUser(Auth::id())
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $unreadCount = Notification::forUser(Auth::id())->unread()->count();

        return response()->json([
            'success' => true,
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }
}