<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\AppNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $query = AppNotification::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc');

        $page = $query->paginate(20);

        $unreadCount = AppNotification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'data' => NotificationResource::collection($page->items()),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
            ],
            'unread_count' => $unreadCount,
        ]);
    }

    public function markRead(Request $request, string $id)
    {
        $notification = AppNotification::where('user_id', $request->user()->id)
            ->whereKey($id)
            ->firstOrFail();

        $notification->read_at = now();
        $notification->save();

        return response()->noContent();
    }

    /**
     * Get real-time notification updates (for polling)
     * Returns unread count and recent notifications
     */
    public function getUpdates(Request $request)
    {
        $user = $request->user();
        
        $unreadCount = AppNotification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();

        // Get recent unread notifications (last 10)
        $recentNotifications = AppNotification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'unread_count' => $unreadCount,
            'recent_notifications' => NotificationResource::collection($recentNotifications),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllRead(Request $request)
    {
        AppNotification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['message' => 'All notifications marked as read']);
    }
}


