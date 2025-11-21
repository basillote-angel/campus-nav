<?php

namespace App\Http\Controllers;

use App\Enums\FoundItemStatus;
use App\Http\Resources\NotificationResource;
use App\Models\AppNotification;
use App\Models\FoundItem;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Get real-time notification updates (for polling)
     * Returns unread count, recent notifications, and pending claims count
     * Uses session-based authentication for web dashboard
     */
    public function getUpdates(Request $request)
    {
        // Check if user is authenticated via session
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $user = auth()->user();
        
        $unreadCount = AppNotification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();

        // Get recent unread notifications (last 10)
        $recentNotifications = AppNotification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get pending claims count (for Claims badge)
        $pendingClaimsCount = 0;
        if ($user->role === 'admin') {
            $pendingClaimsCount = FoundItem::where('status', FoundItemStatus::CLAIM_PENDING->value)->count();
        }

        return response()->json([
            'unread_count' => $unreadCount,
            'pending_claims_count' => $pendingClaimsCount,
            'recent_notifications' => NotificationResource::collection($recentNotifications),
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}

