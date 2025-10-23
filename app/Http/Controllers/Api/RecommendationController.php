<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LostItem;
use App\Models\FoundItem;
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
                ->where('status', 'open')
                ->get();

            if ($lostItems->isEmpty()) {
                return response()->json([], 200);
            }

            $candidateFound = FoundItem::where('status', 'unclaimed')->get();

            if ($candidateFound->isEmpty()) {
                return response()->json([], 200);
            }

            $aggregated = [];
            foreach ($lostItems as $lost) {
                $matches = $aiService->matchLostAndFound($lost, $candidateFound->all());
                foreach ($matches as $m) {
                    if (!isset($m['item'])) { continue; }
                    $itm = $m['item'];
                    $score = $m['score'] ?? 0;
                    if (!$itm || !isset($itm->id)) { continue; }
                    $id = $itm->id;
                    if (!isset($aggregated[$id]) || $aggregated[$id]['score'] < $score) {
                        $aggregated[$id] = [
                            'item' => FoundItem::find($id),
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


