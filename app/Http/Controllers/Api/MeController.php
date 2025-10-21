<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;

class MeController extends Controller
{
    public function items()
    {
        try {
            $userId = Auth::id();

            if (!$userId) {
                return response()->json([], 200);
            }

            $items = Item::where(function ($q) use ($userId) {
                    $q->where('owner_id', $userId)
                        ->orWhere('finder_id', $userId);
                })
                ->with(['owner', 'finder'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json($items, 200);
        } catch (\Throwable $e) {
            \Log::error('ME_ITEMS_ERROR', [
                'message' => $e->getMessage(),
                'userId' => Auth::id(),
            ]);
            return response()->json([], 200);
        }
    }
}


