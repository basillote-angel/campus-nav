<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LostItem;
use App\Models\FoundItem;
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

            $lost = LostItem::where('user_id', $userId)->get();
            $found = FoundItem::where('user_id', $userId)->get();
            $items = $lost->map(function($i){ return ['type' => 'lost'] + $i->toArray(); })
                ->merge($found->map(function($i){ return ['type' => 'found'] + $i->toArray(); }))
                ->sortByDesc('created_at')
                ->values();

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


