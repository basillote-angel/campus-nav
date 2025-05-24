<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Services\AIService;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function index() {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json($user, 200);
    }

    // This method retrieves items posted by the authenticated user
    // and includes comments related to those items.
    // It also call the AI service to get the best match for lost or found items.
    public function postedItems(AIService $aiService) {
        $userId = Auth::id();

        $postedItems = Item::where(function ($query) use ($userId) {
                $query->where('owner_id', $userId)
                    ->orWhere('finder_id', $userId);
            })
            ->with('comments')
            ->get();
        
        if ($postedItems->isEmpty()) {
            return response()->json([], 200);
        }
        
        $postedItemsIds = $postedItems->pluck('id');

        $lostItems = Item::where('type', 'lost')
            ->whereNotIn('id', $postedItemsIds) // Exclude user posted items
            ->where('status', 'unclaimed') // Exclude claimed items
            ->get()
            ->all();
        
        $foundItems = Item::where('type', 'found')
            ->whereNotIn('id', $postedItemsIds) // Exclude user posted items
            ->where('status', 'unclaimed') // Exclude claimed items
            ->get()
            ->all();
        
        foreach ($postedItems as $postedItem) {
            $matchedItem = [
                'highest_best' => null,
                'lower_best' => null,
            ];

            if ($postedItem->type == 'lost' && count($foundItems) > 0) {
                $matchedItem = $aiService->matchBestLostAndFound($postedItem, $foundItems);

            } elseif ($postedItem->type == 'found' && count($lostItems) > 0) {
                $matchedItem = $aiService->matchBestLostAndFound($postedItem, $lostItems);
            }
            
            $postedItem->matchedItem = $matchedItem;
        }

        return response()->json($postedItems, 200);
    }
}
