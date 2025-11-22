<?php

namespace App\Http\Controllers\Api;

use App\Enums\FoundItemStatus;
use App\Enums\LostItemStatus;
use App\Http\Controllers\Controller;
use App\Models\FoundItem;
use App\Models\LostItem;
use App\Services\AIService;
use Illuminate\Support\Facades\Auth;

class RecommendationController extends Controller
{
    public function index(AIService $aiService)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([], 200);
            }

            $role = $user->role ?? 'student';
            if (!in_array($role, ['student'])) {
                return response()->json([], 200);
            }

            $lostItems = LostItem::where('user_id', $user->id)
                ->where('status', LostItemStatus::LOST_REPORTED->value)
                ->latest('created_at')
                ->limit((int) env('AI_LOST_REF_LIMIT', 10))
                ->get();

            if ($lostItems->isEmpty()) {
                return response()->json([], 200);
            }

            $candidateFound = FoundItem::query()
                ->select(['id', 'title', 'description', 'category_id', 'location', 'image_path', 'date_found', 'updated_at'])
                ->where('status', FoundItemStatus::FOUND_UNCLAIMED->value)
                ->latest('created_at')
                ->limit((int) env('AI_CANDIDATE_LIMIT', 200))
                ->get();

            if ($candidateFound->isEmpty()) {
                return response()->json([], 200);
            }

            $candidateMap = $candidateFound->keyBy('id');

            $aggregated = [];
            foreach ($lostItems as $lost) {
                $matches = $aiService->matchLostAndFound($lost, $candidateFound->all(), \App\Models\FoundItem::class);
                foreach ($matches as $m) {
                    if (!isset($m['item'])) { continue; }
                    $itm = $m['item'];
                    $score = $m['score'] ?? 0;
                    if (!$itm || !isset($itm->id)) { continue; }
                    $id = $itm->id;
                    if (!isset($aggregated[$id]) || $aggregated[$id]['score'] < $score) {
                        $aggregated[$id] = [
                            'item' => $candidateMap->get($id),
                            'score' => $score,
                        ];
                    }
                }
            }

            $results = array_values($aggregated);
            usort($results, function ($a, $b) { return ($b['score'] <=> $a['score']); });

            $topK = (int) config('services.navistfind_ai.top_k', 10);
            $results = array_slice($results, 0, $topK);

            return response()->json($results, 200);
        } catch (\Throwable $e) {
            \Log::error('RECOMMENDATIONS_ERROR', [
                'message' => $e->getMessage(),
                'userId' => Auth::id(),
            ]);
            return response()->json([], 200);
        }
    }
}


