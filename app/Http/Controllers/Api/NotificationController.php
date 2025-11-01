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
}


