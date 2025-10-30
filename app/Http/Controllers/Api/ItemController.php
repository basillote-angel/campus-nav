<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LostItem;
use App\Models\FoundItem;
use App\Services\AIService;
use App\Jobs\ComputeItemMatches;
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
            $perPage = min(max((int) $request->query('perPage', 20), 1), 100);
            // Merge lost and found views via simple union approach based on type filter
            $type = $request->query('type');
            if ($type === 'found') {
                $query = FoundItem::query();
            } else if ($type === 'lost') {
                $query = LostItem::query();
            } else {
                // default list latest across both
                $lost = LostItem::query();
                $found = FoundItem::query();
                $lost->select(['id','user_id','category_id','title','description','image_path','location','date_lost as date','status','created_at','updated_at'])
                    ->addSelect(\DB::raw("'lost' as type"));
                $found->select(['id','user_id','category_id','title','description','image_path','location','date_found as date','status','created_at','updated_at'])
                    ->addSelect(\DB::raw("'found' as type"));
                $merged = \DB::query()
                    ->fromSub($lost->unionAll($found), 'items')
                    ->orderBy('created_at', 'desc');
                return response()->json($merged->paginate($perPage), 200);
            }

            // Filters: type (lost|found), category, date range (lost_found_date)
            // category filter

            if ($request->has('category') && !empty($request->query('category'))) {
                $query->where('category_id', $request->query('category'));
            }

            if ($request->has('dateFrom') && !empty($request->query('dateFrom'))) {
                $dateCol = ($type === 'found') ? 'date_found' : 'date_lost';
                $query->whereDate($dateCol, '>=', $request->query('dateFrom'));
            }

            if ($request->has('dateTo') && !empty($request->query('dateTo'))) {
                $dateCol = ($type === 'found') ? 'date_found' : 'date_lost';
                $query->whereDate($dateCol, '<=', $request->query('dateTo'));
            }

            // Keyword search across name and description
            $keyword = trim($request->query('query', ''));
            $sort = $request->query('sort', 'newest'); // newest | relevance

            if ($keyword !== '') {
                $kw = "%{$keyword}%";
                $query->where(function ($q) use ($kw) {
                    $q->where('title', 'like', $kw)
                      ->orWhere('description', 'like', $kw);
                });

                if ($sort === 'relevance') {
                    // Simple heuristic relevance sort
                    $query->orderByRaw(
                        "(CASE WHEN title LIKE ? THEN 2 ELSE 0 END) + (CASE WHEN description LIKE ? THEN 1 ELSE 0 END) DESC",
                        [$kw, $kw]
                    );
                }
            }

            // Default sort by newest unless relevance is explicitly requested with a keyword
            if ($sort !== 'relevance' || $keyword === '') {
                $query->orderBy('created_at', 'desc');
            }

            return response()->json($query->paginate($perPage), 200);
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
                'title' => 'required|string',
                'category_id' => 'required|integer|exists:categories,id',
                'description' => 'required|string',
                'type' => 'required|in:lost,found',
                'location' => 'nullable|string',
                'date' => 'nullable|date',
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

            $item = null;
            if ($request->type === 'lost') {
                $item = LostItem::create([
                    'user_id' => $userId,
                    'category_id' => $request->category_id,
                    'title' => $request->title,
                    'description' => $request->description,
                    'image_path' => $request->image_path,
                    'location' => $request->location,
                    'date_lost' => $request->date ? Carbon::parse($request->date)->format('Y-m-d') : null,
                    'status' => 'open',
                ]);
            } else {
                $item = FoundItem::create([
                    'user_id' => $userId,
                    'category_id' => $request->category_id,
                    'title' => $request->title,
                    'description' => $request->description,
                    'image_path' => $request->image_path,
                    'location' => $request->location,
                    'date_found' => $request->date ? Carbon::parse($request->date)->format('Y-m-d') : null,
                    'status' => 'unclaimed',
                ]);
            }

            $formattedDate = $request->lost_found_date 
                ? Carbon::parse($request->lost_found_date)->format('Y-m-d H:i:s')
                : null;

            // Queue async matching instead of blocking the request
            if ((bool) $request->boolean('include_matches', false)) {
                $refType = $request->type === 'lost' ? 'lost' : 'found';
                ComputeItemMatches::dispatch($refType, $item->id);
            }

            return response()->json([ 'item' => $item, 'matchesQueued' => (bool) $request->boolean('include_matches', false) ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create item', 'message' => $e->getMessage()], 500);
        }
    }

    public function show(Request $request, $id)
    {
        try {
           $type = $request->query('type');
           if ($type === 'found') {
               $item = FoundItem::find($id);
           } else if ($type === 'lost') {
               $item = LostItem::find($id);
           } else {
               // fallback: try both; beware overlapping ids
               $lost = LostItem::find($id);
               $found = $lost ? null : FoundItem::find($id);
               $item = $lost ?: $found;
           }

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
            $item = LostItem::find($id) ?: FoundItem::find($id);

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

            if ($item instanceof LostItem) {
                $item->update($request->only(['title','category_id','description','location','date_lost','status']));
            } else {
                $item->update($request->only(['title','category_id','description','location','date_found','status']));
            }

            // Queue async matching instead of blocking the request
            if ((bool) $request->boolean('include_matches', false)) {
                $refType = $item instanceof LostItem ? 'lost' : 'found';
                ComputeItemMatches::dispatch($refType, $item->id);
            }

            return response()->json([ 'item' => $item, 'matchesQueued' => (bool) $request->boolean('include_matches', false) ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update item', 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $itemType = $request->input('type');
            $item = null;
            if ($itemType === 'lost') {
                $item = LostItem::find($id);
            } else if ($itemType === 'found') {
                $item = FoundItem::find($id);
            } else {
                // Fallback when type is not provided
                $item = LostItem::find($id) ?: FoundItem::find($id);
            }

            if (!$item) {
                return response()->json(['message' => 'Item not found'], 404);
            }

            $userId = Auth::id();
            // Allow delete if current user created the item (user_id)
            // Keep legacy support if owner_id/finder_id present
            $isOwner = ((int) ($item->user_id ?? 0) === (int) $userId)
                || ((int) ($item->owner_id ?? 0) === (int) $userId)
                || ((int) ($item->finder_id ?? 0) === (int) $userId);
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

            if ($item instanceof LostItem) {
                $candidateItems = FoundItem::where('status','unclaimed')->get();
                $matches = $aiService->matchLostAndFound($item, $candidateItems->all(), FoundItem::class);
            } else {
                $candidateItems = LostItem::where('status','open')->get();
                $matches = $aiService->matchLostAndFound($item, $candidateItems->all(), LostItem::class);
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
            $lostItems = LostItem::where('user_id', $user->id)
                ->where('status', 'open')
                ->get();

            if ($lostItems->isEmpty()) {
                return response()->json([], 200);
            }

            // Candidates: all unclaimed found items
            $candidateFound = FoundItem::where('status', 'unclaimed')->get();

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
                            'item' => FoundItem::find($id),
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

            $item = FoundItem::find($id);
            if (!$item) return response()->json(['message' => 'Item not found'], 404);

            if (!$item) {
                return response()->json(['message' => 'Only found items can be claimed'], 422);
            }
            if ($item->status !== 'unclaimed') {
                return response()->json(['message' => 'Item is not available to claim'], 422);
            }

            $item->claimed_by = $user->id;
            $item->claim_message = $request->input('message');
            $item->claimed_at = now();
            $item->status = 'matched';
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
     * Admin/Staff: enqueue AI matching for a specific item id.
     * Optional body: { "type": "lost"|"found" } to disambiguate.
     */
    public function computeMatches(Request $request, $id)
    {
        try {
            $user = Auth::user();
            $role = $user->role ?? 'student';
            if (!in_array($role, ['admin', 'staff'])) {
                return response()->json(['message' => 'Forbidden'], 403);
            }

            $type = $request->input('type');
            $reference = null;
            $refType = null;

            if ($type === 'lost') {
                $reference = LostItem::find($id);
                $refType = 'lost';
            } else if ($type === 'found') {
                $reference = FoundItem::find($id);
                $refType = 'found';
            } else {
                // Auto-detect by searching found first, then lost
                $reference = FoundItem::find($id);
                if ($reference) {
                    $refType = 'found';
                } else {
                    $reference = LostItem::find($id);
                    if ($reference) {
                        $refType = 'lost';
                    }
                }
            }

            if (!$reference || !$refType) {
                return response()->json(['message' => 'Item not found'], 404);
            }

            ComputeItemMatches::dispatch($refType, $reference->id);

            return response()->json([
                'queued' => true,
                'referenceType' => $refType,
                'referenceId' => $reference->id,
            ], 202);
        } catch (\Throwable $e) {
            \Log::error('COMPUTE_MATCHES_ERROR', [
                'message' => $e->getMessage(),
                'itemId' => $id,
                'userId' => Auth::id(),
            ]);
            return response()->json(['message' => 'Server error'], 500);
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
