<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LostItem;
use App\Models\FoundItem;
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

        // Use eager loading to prevent N+1 queries
        $postedItems = collect()
            ->merge(LostItem::where('user_id', $userId)->with(['category', 'user'])->get()->map(fn($i) => ['type'=>'lost','model'=>$i]))
            ->merge(FoundItem::where('user_id', $userId)->with(['category', 'user'])->get()->map(fn($i) => ['type'=>'found','model'=>$i]));
        
        if ($postedItems->isEmpty()) {
            return response()->json([], 200);
        }
        
        $postedLostIds = $postedItems->filter(fn($x)=>$x['type']==='lost')->map(fn($x)=>$x['model']->id)->all();
        $postedFoundIds = $postedItems->filter(fn($x)=>$x['type']==='found')->map(fn($x)=>$x['model']->id)->all();

        $lostItems = LostItem::whereNotIn('id', $postedLostIds)
            ->where('status', 'open')
            ->latest('created_at')
            ->limit((int) env('AI_CANDIDATE_LIMIT', 200))
            ->get()
            ->all();
        
        $foundItems = FoundItem::whereNotIn('id', $postedFoundIds)
            ->where('status', 'unclaimed')
            ->latest('created_at')
            ->limit((int) env('AI_CANDIDATE_LIMIT', 200))
            ->get()
            ->all();
        
        foreach ($postedItems as $p) {
            $postedItem = $p['model'];
            $postedItem->matchedItem = [
                'highest_best' => null,
                'lower_best' => null,
            ];

            try {
                if ($p['type'] === 'lost' && count($foundItems) > 0) {
                    $postedItem->matchedItem = $aiService->matchBestLostAndFound($postedItem, $foundItems, FoundItem::class);
                } elseif ($p['type'] === 'found' && count($lostItems) > 0) {
                    $postedItem->matchedItem = $aiService->matchBestLostAndFound($postedItem, $lostItems, LostItem::class);
                }
            } catch (\Throwable $e) {
                \Log::warning('AI_MATCH_PROFILE_FAILED', [
                    'itemId' => $postedItem->id,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        return response()->json($postedItems, 200);
    }
}
