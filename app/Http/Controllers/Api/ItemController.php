<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Services\AIService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Item::query();

            // Filters: type (lost|found), category, date range (lost_found_date)
            if ($request->has('type') && in_array($request->query('type'), ['lost', 'found'])) {
                $query->where('type', $request->query('type'));
            }

            if ($request->has('category') && !empty($request->query('category'))) {
                $query->where('category', $request->query('category'));
            }

            if ($request->has('dateFrom') && !empty($request->query('dateFrom'))) {
                $query->whereDate('lost_found_date', '>=', $request->query('dateFrom'));
            }

            if ($request->has('dateTo') && !empty($request->query('dateTo'))) {
                $query->whereDate('lost_found_date', '<=', $request->query('dateTo'));
            }

            // Keyword search across name and description
            $keyword = trim($request->query('query', ''));
            $sort = $request->query('sort', 'newest'); // newest | relevance

            if ($keyword !== '') {
                $kw = "%{$keyword}%";
                $query->where(function ($q) use ($kw) {
                    $q->where('name', 'like', $kw)
                      ->orWhere('description', 'like', $kw);
                });

                if ($sort === 'relevance') {
                    // Simple heuristic relevance sort
                    $query->orderByRaw(
                        "(CASE WHEN name LIKE ? THEN 2 ELSE 0 END) + (CASE WHEN description LIKE ? THEN 1 ELSE 0 END) DESC",
                        [$kw, $kw]
                    );
                }
            }

            // Default sort by newest unless relevance is explicitly requested with a keyword
            if ($sort !== 'relevance' || $keyword === '') {
                $query->orderBy('created_at', 'desc');
            }

            return response()->json($query->with(['owner', 'finder'])->get(), 200);
        } catch (\Throwable $e) {
            \Log::error('ITEM_INDEX_ERROR', [
                'message' => $e->getMessage(),
                'userId' => Auth::id(),
            ]);
            return response()->json([], 200);
        }
    }

    public function store(Request $request, AIService $aiService)
    {
        try {
            $request->validate([
                'name' => 'required|string',
                'category' => 'required|string|in:electronics,documents,accessories,idOrCards,clothing,bagOrPouches,personalItems,schoolSupplies,others',
                'description' => 'required|string',
                'type' => 'required|in:lost,found',
                'location' => 'nullable|string',
                'lost_found_date' => 'nullable|date',
            ]);

            $userId = Auth::id();
            $user = Auth::user();
            $role = $user->role ?? 'student';
            $isAdminOrStaff = in_array($role, ['admin', 'staff']);

            // Enforce role-based creation rules:
            // - Students can only create LOST items
            // - Admin/Staff can create LOST or FOUND
            if (!$isAdminOrStaff && $request->input('type') === 'found') {
                return response()->json([
                    'message' => 'Only admin or staff can create found items.'
                ], 403);
            }

            if ($request->type == 'lost') {
                // Lost item: the authenticated user is the owner
                $request->merge(['owner_id' => $userId]);
            } elseif ($request->type == 'found') {
                // Found item: the authenticated user is the finder
                $request->merge(['finder_id' => $userId]);
            }

            $formattedDate = $request->lost_found_date 
                ? Carbon::parse($request->lost_found_date)->format('Y-m-d H:i:s')
                : null;

            $item = Item::create(array_merge($request->all(), [
                'user_id' => Auth::id(),
                'lost_found_date' => $formattedDate
            ]));
            $responsePayload = $item;
            if ((bool) $request->boolean('include_matches', false)) {
                $typeFilter = $item->type === 'lost' ? 'found' : 'lost';
                $candidates = Item::where('type', $typeFilter)
                    ->where('status', 'unclaimed')
                    ->where('id', '!=', $item->id)
                    ->latest('created_at')
                    ->limit(200)
                    ->get();
                $matches = [];
                if ($candidates->isNotEmpty()) {
                    $matches = $aiService->matchLostAndFound($item, $candidates->all());
                }
                $responsePayload = [ 'item' => $item, 'matches' => $matches ];
                \Log::info('AI_MATCHES_CREATED', [
                    'itemId' => $item->id,
                    'type' => $item->type,
                    'matchesCount' => count($matches),
                ]);
            }
            return response()->json($responsePayload, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create item', 'message' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
           $item = Item::with(['owner', 'finder'])->find($id);

            if (!$item) {
                return response()->json(['message' => 'Item not found'], 404);
            }
    
            return response()->json($item, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch items', 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id, AIService $aiService)
    {
        try {
            $item = Item::find($id);

            if (!$item) {
                return response()->json(['message' => 'Item not found'], 404);
            }

            // Enforce role-based rules on updates to type as well
            $user = Auth::user();
            $role = $user->role ?? 'student';
            $isAdminOrStaff = in_array($role, ['admin', 'staff']);

            if (!$isAdminOrStaff && $request->has('type') && $request->input('type') === 'found') {
                return response()->json([
                    'message' => 'Only admin or staff can set type to found.'
                ], 403);
            }

            $item->update($request->only(['name', 'category', 'description', 'type', 'location', 'lost_found_date']));

            $responsePayload = $item;
            if ((bool) $request->boolean('include_matches', false)) {
                $typeFilter = $item->type === 'lost' ? 'found' : 'lost';
                $candidates = Item::where('type', $typeFilter)
                    ->where('status', 'unclaimed')
                    ->where('id', '!=', $item->id)
                    ->latest('created_at')
                    ->limit(200)
                    ->get();
                $matches = [];
                if ($candidates->isNotEmpty()) {
                    $matches = $aiService->matchLostAndFound($item, $candidates->all());
                }
                $responsePayload = [ 'item' => $item, 'matches' => $matches ];
                \Log::info('AI_MATCHES_UPDATED', [
                    'itemId' => $item->id,
                    'type' => $item->type,
                    'matchesCount' => count($matches),
                ]);
            }

            return response()->json($responsePayload, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update item', 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $item = Item::find($id);

            if (!$item) {
                return response()->json(['message' => 'Item not found'], 404);
            }

            $userId = Auth::id();
            $isOwner = ($item->owner_id && $item->owner_id === $userId) || ($item->finder_id && $item->finder_id === $userId);
            if (!$isOwner) {
                return response()->json(['message' => 'Forbidden'], 403);
            }

            $item->delete();
            return response()->json(['deleted' => true], 200);

        } catch (\Throwable $e) {
            \Log::error('ITEM_DELETE_ERROR', [
                'message' => $e->getMessage(),
                'userId' => Auth::id(),
                'itemId' => $id,
            ]);
            return response()->json(['message' => 'Server error'], 500);
        }
    } 

     public function matchesItems(AIService $aiService, $id)
    {
        try {
            $item = Item::find($id);

            if (!$item) {
                return response()->json(['message' => 'Item not found'], 404);
            }

            $typeFilter = $item['type'] == 'lost' ? 'found' : 'lost';

            $candidateItems = Item::where('type', $typeFilter)
                ->where('id', '!=', $item['id'])
                ->where('status', 'unclaimed') // Exclude claimed items
                ->get();
            
            $matches = [];
            if (!$candidateItems->isEmpty()) {
                $matches = $aiService->matchLostAndFound($item, $candidateItems->all());
            }

            return response()->json($matches, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch items', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Recommend items for the authenticated user based on their active lost posts.
     * Returns found items ranked by AI match score.
     */
    public function recommended(AIService $aiService)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            // Only students receive recommendations for now
            $role = $user->role ?? 'student';
            if (!in_array($role, ['student'])) {
                return response()->json([], 200);
            }

            // Reference set: user's active lost items
            $lostItems = Item::where('type', 'lost')
                ->where('owner_id', $user->id)
                ->where('status', 'unclaimed')
                ->get();

            if ($lostItems->isEmpty()) {
                return response()->json([], 200);
            }

            // Candidates: all unclaimed found items
            $candidateFound = Item::where('type', 'found')
                ->where('status', 'unclaimed')
                ->get();

            if ($candidateFound->isEmpty()) {
                return response()->json([], 200);
            }

            // Aggregate matches across all lost items; keep highest score per found item
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
                            'item' => Item::with(['owner', 'finder'])->find($id),
                            'score' => $score,
                        ];
                    }
                }
            }

            // Sort by score desc and limit
            $results = array_values($aggregated);
            usort($results, function ($a, $b) {
                return ($b['score'] <=> $a['score']);
            });

            $topK = intval(env('AI_RECOMMEND_TOP_K', 10));
            $results = array_slice($results, 0, $topK);

            return response()->json($results, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to get recommendations', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Student submits a claim for a found item.
     */
    public function claim(Request $request, $id)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            $request->validate([
                'message' => 'required|string|max:2000',
                'contactName' => 'nullable|string|max:255',
                'contactInfo' => 'nullable|string|max:255',
            ]);

            $item = Item::find($id);
            if (!$item) return response()->json(['message' => 'Item not found'], 404);

            if ($item->type !== 'found') {
                return response()->json(['message' => 'Only found items can be claimed'], 422);
            }
            if ($item->status !== 'unclaimed') {
                return response()->json(['message' => 'Item is not available to claim'], 422);
            }

            $item->claimed_by = $user->id;
            $item->claim_message = $request->input('message');
            $item->claimed_at = now();
            $item->status = 'pending_approval';
            $item->save();

            return response()->json($item, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to submit claim', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Admin/Staff approves the pending claim.
     */
    public function approveClaim(Request $request, $id)
    {
        try {
            $user = Auth::user();
            $role = $user->role ?? 'student';
            if (!in_array($role, ['admin', 'staff'])) {
                return response()->json(['message' => 'Forbidden'], 403);
            }

            $item = Item::find($id);
            if (!$item) return response()->json(['message' => 'Item not found'], 404);

            if ($item->status !== 'pending_approval') {
                return response()->json(['message' => 'No pending claim to approve'], 422);
            }

            $item->approved_by = $user->id;
            $item->approved_at = now();
            $item->status = 'claimed';
            $item->save();

            return response()->json($item, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to approve claim', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Admin/Staff rejects the pending claim with a reason.
     */
    public function rejectClaim(Request $request, $id)
    {
        try {
            $user = Auth::user();
            $role = $user->role ?? 'student';
            if (!in_array($role, ['admin', 'staff'])) {
                return response()->json(['message' => 'Forbidden'], 403);
            }

            $request->validate([
                'reason' => 'required|string|max:1000'
            ]);

            $item = Item::find($id);
            if (!$item) return response()->json(['message' => 'Item not found'], 404);

            if ($item->status !== 'pending_approval') {
                return response()->json(['message' => 'No pending claim to reject'], 422);
            }

            $item->rejected_by = $user->id;
            $item->rejected_at = now();
            $item->rejection_reason = $request->input('reason');

            // Reset to unclaimed state
            $item->status = 'unclaimed';
            $item->claimed_by = null;
            $item->claim_message = null;
            $item->claimed_at = null;
            $item->save();

            return response()->json($item, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to reject claim', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Record simple AI feedback (logs for now; table later).
     */
    public function aiFeedback(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) return response()->json(['message' => 'Unauthorized'], 401);

            $request->validate([
                'itemId' => 'required|integer',
                'matchedItemId' => 'required|integer',
                'action' => 'required|string|in:positive,negative,dismissed',
                'source' => 'nullable|string|in:home,recommended,detail,matches',
            ]);

            \Log::info('AI_FEEDBACK', [
                'userId' => $user->id,
                'itemId' => $request->itemId,
                'matchedItemId' => $request->matchedItemId,
                'action' => $request->action,
                'source' => $request->source,
                'timestamp' => now()->toISOString(),
            ]);

            return response()->json(['ok' => true], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to record feedback', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Batch AI matching for a list of item IDs (admin/staff only)
     */
    public function batchMatch(Request $request, AIService $aiService)
    {
        try {
            $user = Auth::user();
            $role = $user->role ?? 'student';
            if (!in_array($role, ['admin', 'staff'])) {
                return response()->json(['message' => 'Forbidden'], 403);
            }

            $request->validate([
                'itemIds' => 'required|array|min:1',
                'itemIds.*' => 'integer',
                'topK' => 'nullable|integer|min:1|max:50',
                'threshold' => 'nullable|numeric|min:0|max:1',
            ]);

            $topK = (int)($request->input('topK', config('services.navistfind_ai.top_k', 10)));
            $threshold = (float)($request->input('threshold', config('services.navistfind_ai.threshold', 0.6)));

            $items = Item::whereIn('id', $request->itemIds)->get();
            if ($items->isEmpty()) {
                return response()->json([], 200);
            }

            $results = [];
            foreach ($items as $ref) {
                $typeFilter = $ref->type === 'lost' ? 'found' : 'lost';
                $candidates = Item::where('type', $typeFilter)
                    ->where('status', 'unclaimed')
                    ->where('id', '!=', $ref->id)
                    ->latest('created_at')
                    ->limit(200)
                    ->get();

                $matches = [];
                if ($candidates->isNotEmpty()) {
                    $raw = $aiService->matchLostAndFound($ref, $candidates->all());
                    $filtered = array_values(array_filter($raw, function ($m) use ($threshold) {
                        return isset($m['score']) && $m['score'] >= $threshold && isset($m['item']);
                    }));
                    usort($filtered, function ($a, $b) { return ($b['score'] <=> $a['score']); });
                    $matches = array_slice($filtered, 0, $topK);
                }

                $results[] = [
                    'referenceId' => $ref->id,
                    'matches' => $matches,
                ];
            }

            return response()->json($results, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to batch match', 'message' => $e->getMessage()], 500);
        }
    }
}
