<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Services\AIService;
use Illuminate\Http\Request;

class MatchQueueController extends Controller
{
    public function index(Request $request, AIService $aiService)
    {
        $days = (int) $request->query('days', 14);
        $minScore = (float) $request->query('minScore', 0.6);

        $recentFound = Item::where('type', 'found')
            ->where('status', 'unclaimed')
            ->where('created_at', '>=', now()->subDays($days))
            ->latest('created_at')
            ->limit(200)
            ->get();

        $lostCandidates = Item::where('type', 'lost')
            ->where('status', 'unclaimed')
            ->get();

        $suggestions = [];
        foreach ($recentFound as $found) {
            if ($lostCandidates->isEmpty()) {
                continue;
            }
            $matches = $aiService->matchLostAndFound($found, $lostCandidates->all());
            foreach ($matches as $m) {
                $s = (float) ($m['score'] ?? 0);
                if ($s >= $minScore && isset($m['item'])) {
                    $suggestions[] = [
                        'found' => $found,
                        'lost' => $m['item'],
                        'score' => $s,
                    ];
                }
            }
        }

        usort($suggestions, fn($a, $b) => $b['score'] <=> $a['score']);

        return view('admin.matches.index', [
            'suggestions' => $suggestions,
            'days' => $days,
            'minScore' => $minScore,
        ]);
    }
}


