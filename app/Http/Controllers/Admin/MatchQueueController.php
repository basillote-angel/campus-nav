<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FoundItem;
use App\Models\LostItem;
use App\Services\AIService;
use App\Models\ItemMatch;
use App\Jobs\ComputeItemMatches;
use Illuminate\Http\Request;

class MatchQueueController extends Controller
{
    public function index(Request $request, AIService $aiService)
    {
        $days = (int) $request->query('days', 14);
        $minScore = (float) $request->query('minScore', 0.6);

        // Read from matches table instead of recomputing
        $matches = ItemMatch::query()
            ->where('created_at', '>=', now()->subDays($days))
            ->where('similarity_score', '>=', $minScore)
            ->with(['lostItem', 'foundItem'])
            ->orderByDesc('similarity_score')
            ->limit(500)
            ->get();

        return view('admin.matches.index', [
            'suggestions' => $matches,
            'days' => $days,
            'minScore' => $minScore,
        ]);
    }

    public function refresh(Request $request)
    {
        $days = (int) $request->query('days', 14);

        // Enqueue jobs for recent found items
        $recentFound = FoundItem::where('status', 'unclaimed')
            ->where('created_at', '>=', now()->subDays($days))
            ->latest('created_at')
            ->limit(200)
            ->get(['id']);

        foreach ($recentFound as $found) {
            ComputeItemMatches::dispatch('found', $found->id);
        }

        // Also enqueue for recent lost items
        $recentLost = LostItem::where('status', 'open')
            ->where('created_at', '>=', now()->subDays($days))
            ->latest('created_at')
            ->limit(200)
            ->get(['id']);

        foreach ($recentLost as $lost) {
            ComputeItemMatches::dispatch('lost', $lost->id);
        }

        return back()->with('success', 'Match refresh queued. Please check back shortly.');
    }
}


