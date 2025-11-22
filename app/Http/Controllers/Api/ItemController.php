<?php

namespace App\Http\Controllers\Api;

use App\Enums\ClaimStatus;
use App\Enums\FoundItemStatus;
use App\Enums\LostItemStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\FoundItemResource;
use App\Http\Resources\LostItemResource;
use App\Jobs\ComputeItemMatches;
use App\Jobs\SendNotificationJob;
use App\Models\ClaimedItem;
use App\Models\FoundItem;
use App\Models\LostItem;
use App\Services\AIService;
use App\Services\DomainEventService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = min(max((int) $request->query('perPage', 20), 1), 100);
            $includeReturned = filter_var($request->query('includeReturned', false), FILTER_VALIDATE_BOOLEAN);
            // Merge lost and found views via simple union approach based on type filter
            $type = $request->query('type');
            if ($type === 'found') {
                $query = FoundItem::with(['category', 'user']); // Add eager loading to prevent N+1
                if (!$includeReturned) {
                    $query->whereNotIn('status', [
                        FoundItemStatus::COLLECTED->value,
                        FoundItemStatus::CLAIM_APPROVED->value,
                    ]);
                }
            } else if ($type === 'lost') {
                $query = LostItem::with(['category', 'user']); // Add eager loading to prevent N+1
            } else {
                // default list latest across both
                $lost = LostItem::with(['category', 'user']); // Add eager loading
                $found = FoundItem::with(['category', 'user']); // Add eager loading
                if (!$includeReturned) {
                    $found->whereNotIn('status', [
                        FoundItemStatus::COLLECTED->value,
                        FoundItemStatus::CLAIM_APPROVED->value,
                    ]);
                }
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
        // Ensure we always return JSON for API requests
        $request->headers->set('Accept', 'application/json');
        
        try {
            $validated = $request->validate([
                'title' => 'required|string',
                'category_id' => 'required|integer|exists:categories,id',
                'description' => 'required|string',
                'type' => 'required|in:lost,found',
                'location' => 'nullable|string',
                'date' => 'nullable|date',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }

        try {

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

            // Ensure category_id is an integer
            $categoryId = (int) $validated['category_id'];
            
            // Verify category exists (validation already checks this, but double-check for safety)
            if (!\App\Models\Category::where('id', $categoryId)->exists()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => ['category_id' => ['The selected category does not exist.']]
                ], 422);
            }

            $item = null;
            if ($request->type === 'lost') {
                $item = LostItem::create([
                    'user_id' => $userId,
                    'category_id' => $categoryId,
                    'title' => $validated['title'],
                    'description' => $validated['description'],
                    'image_path' => $request->image_path,
                    'location' => $validated['location'] ?? null,
                    'date_lost' => isset($validated['date']) ? Carbon::parse($validated['date'])->format('Y-m-d') : null,
                    'status' => LostItemStatus::LOST_REPORTED->value,
                ]);
            } else {
                $item = FoundItem::create([
                    'user_id' => $userId,
                    'category_id' => $categoryId,
                    'title' => $validated['title'],
                    'description' => $validated['description'],
                    'image_path' => $request->image_path,
                    'location' => $validated['location'] ?? null,
                    'date_found' => isset($validated['date']) ? Carbon::parse($validated['date'])->format('Y-m-d') : null,
                    'status' => FoundItemStatus::FOUND_UNCLAIMED->value,
                ]);
            }

            $formattedDate = $request->lost_found_date 
                ? Carbon::parse($request->lost_found_date)->format('Y-m-d H:i:s')
                : null;

            // Queue async matching instead of blocking the request
            $matchesQueued = (bool) $request->boolean('include_matches', false);
            if ($matchesQueued) {
                $refType = $request->type === 'lost' ? 'lost' : 'found';
                ComputeItemMatches::dispatch($refType, $item->id);
            }

            try {
                $this->loadItemRelations($item);
            } catch (\Exception $e) {
                \Log::warning('Failed to load item relations', [
                    'item_id' => $item->id,
                    'item_type' => $request->type,
                    'error' => $e->getMessage()
                ]);
                // Continue without relations if loading fails
            }

            try {
                return $this->respondWithItemResource(
                    $item,
                    201,
                    [
                        'matchesQueued' => $matchesQueued,
                        'type' => $request->type,
                    ]
                );
            } catch (\Exception $e) {
                \Log::error('Failed to create resource response', [
                    'item_id' => $item->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                // Return a simple JSON response if resource creation fails
                return response()->json([
                    'id' => $item->id,
                    'title' => $item->title,
                    'description' => $item->description,
                    'status' => $item->status,
                    'type' => $request->type,
                    'meta' => [
                        'matchesQueued' => $matchesQueued,
                        'type' => $request->type,
                    ]
                ], 201);
            }
        } catch (\Exception $e) {
            \Log::error('ITEM_CREATE_ERROR', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'userId' => Auth::id(),
            ]);
            return response()->json([
                'error' => 'Failed to create item',
                'message' => $e->getMessage()
            ], 500);
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

            $this->loadItemRelations($item);

            return $this->resourceForModel($item);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch items', 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id, AIService $aiService)
    {
        // Ensure we always return JSON for API requests
        $request->headers->set('Accept', 'application/json');
        
        try {
            $item = LostItem::find($id) ?: FoundItem::find($id);

            if (!$item) {
                return response()->json(['message' => 'Item not found'], 404);
            }

            // Enforce role-based rules on updates to type/status
            $user = Auth::user();
            $role = $user->role ?? 'student';
            $isAdminOrStaff = in_array($role, ['admin', 'staff']);

            if (!$isAdminOrStaff && ($request->has('type') && $request->input('type') === 'found')) {
                return response()->json([
                    'message' => 'Only admin or staff can set type to found.'
                ], 403);
            }

            if (!$isAdminOrStaff && $request->has('status')) {
                return response()->json([
                    'message' => 'Only admin or staff can change item status.'
                ], 403);
            }

            if ($item instanceof LostItem) {
                $request->validate([
                    'title' => 'sometimes|string',
                    'category_id' => 'sometimes|integer|exists:categories,id',
                    'description' => 'sometimes|string',
                    'location' => 'sometimes|nullable|string',
                    'date_lost' => 'sometimes|date',
                    'status' => ['sometimes', Rule::in(LostItemStatus::values())],
                ]);

                $payload = [];
                if ($request->has('title')) {
                    $payload['title'] = $request->input('title');
                }
                if ($request->has('category_id') && $request->filled('category_id')) {
                    $payload['category_id'] = (int) $request->input('category_id');
                }
                if ($request->has('description')) {
                    $payload['description'] = $request->input('description');
                }
                if ($request->has('location')) {
                    $payload['location'] = $request->input('location');
                }
                if ($request->has('date_lost') && $request->filled('date_lost')) {
                    $payload['date_lost'] = Carbon::parse($request->input('date_lost'))->format('Y-m-d');
                }
                if ($request->has('status') && $request->filled('status')) {
                    $payload['status'] = $request->input('status');
                }
                
                if (!empty($payload)) {
                    $item->update($payload);
                }
            } else {
                $request->validate([
                    'title' => 'sometimes|string',
                    'category_id' => 'sometimes|integer|exists:categories,id',
                    'description' => 'sometimes|string',
                    'location' => 'sometimes|nullable|string',
                    'date_found' => 'sometimes|date',
                    'collection_deadline' => 'sometimes|date',
                    'status' => ['sometimes', Rule::in(FoundItemStatus::values())],
                ]);

                $payload = [];
                if ($request->has('title')) {
                    $payload['title'] = $request->input('title');
                }
                if ($request->has('category_id') && $request->filled('category_id')) {
                    $payload['category_id'] = (int) $request->input('category_id');
                }
                if ($request->has('description')) {
                    $payload['description'] = $request->input('description');
                }
                if ($request->has('location')) {
                    $payload['location'] = $request->input('location');
                }
                if ($request->has('date_found') && $request->filled('date_found')) {
                    $payload['date_found'] = Carbon::parse($request->input('date_found'))->format('Y-m-d');
                }
                if ($request->has('status') && $request->filled('status')) {
                    $payload['status'] = $request->input('status');
                }
                if ($request->has('collection_deadline') && $request->filled('collection_deadline')) {
                    $payload['collection_deadline'] = Carbon::parse($request->input('collection_deadline'))->toDateTimeString();
                }
                
                if (!empty($payload)) {
                    $item->update($payload);
                }
            }

            $matchesQueued = (bool) $request->boolean('include_matches', false);
            if ($matchesQueued) {
                $refType = $item instanceof LostItem ? 'lost' : 'found';
                ComputeItemMatches::dispatch($refType, $item->id);
            }

            $this->loadItemRelations($item);

            return $this->respondWithItemResource(
                $item,
                200,
                [
                    'matchesQueued' => $matchesQueued,
                ]
            );
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
            // Try to find item in both LostItem and FoundItem tables
            $item = LostItem::find($id);
            $itemType = 'lost';
            
            if (!$item) {
                $item = FoundItem::find($id);
                $itemType = 'found';
            }

            if (!$item) {
                return response()->json(['message' => 'Item not found'], 404);
            }

            if ($itemType === 'lost') {
                $candidateItems = FoundItem::query()
                    ->select(['id', 'title', 'description', 'category_id', 'location', 'updated_at'])
                    ->where('status', FoundItemStatus::FOUND_UNCLAIMED->value)
                    ->latest('created_at')
                    ->limit((int) env('AI_CANDIDATE_LIMIT', 200))
                    ->get();
                $matches = $aiService->matchLostAndFound($item, $candidateItems->all(), FoundItem::class);
            } else {
                $candidateItems = LostItem::query()
                    ->select(['id', 'title', 'description', 'category_id', 'location', 'updated_at'])
                    ->where('status', LostItemStatus::LOST_REPORTED->value)
                    ->latest('created_at')
                    ->limit((int) env('AI_CANDIDATE_LIMIT', 200))
                    ->get();
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
                ->where('status', LostItemStatus::LOST_REPORTED->value)
                ->get();

            if ($lostItems->isEmpty()) {
                return response()->json([], 200);
            }

            // Candidates: all unclaimed found items (with eager loading to prevent N+1)
            $candidateFound = FoundItem::where('status', FoundItemStatus::FOUND_UNCLAIMED->value)
                ->with(['category', 'user'])
                ->get();

            if ($candidateFound->isEmpty()) {
                return response()->json([], 200);
            }

            // Create a lookup map for O(1) access instead of N+1 queries
            $candidateMap = $candidateFound->keyBy('id');

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
                        // Use the item from the map instead of querying database
                        $aggregated[$id] = [
                            'item' => $candidateMap->get($id),
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

			try {
				$validated = $request->validate([
					'message' => 'required|string|min:20|max:2000',
					'contactName' => 'required_without:contactInfo|nullable|string|min:2|max:255',
					'contactInfo' => 'required_without_all:email,phoneNumber|nullable|string|max:255',
					'email' => 'required_without:contactInfo|nullable|email',
					'phoneNumber' => 'required_without:contactInfo|nullable|string|min:10|max:32',
					'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
					'matchedLostItemId' => 'nullable|integer|exists:lost_items,id',
				]);
			} catch (ValidationException $e) {
				return response()->json([
					'message' => 'Validation failed',
					'errors' => $e->errors(),
				], 422);
			}

			$item = FoundItem::with('category')->find($id);
			if (!$item) {
				return response()->json(['message' => 'Item not found'], 404);
			}

			if (!in_array($item->status, [FoundItemStatus::FOUND_UNCLAIMED, FoundItemStatus::CLAIM_PENDING], true)) {
				return response()->json(['message' => 'Item is not available to claim'], 422);
			}

			$hasActiveClaim = ClaimedItem::query()
				->where('found_item_id', $item->id)
				->where('claimant_id', $user->id)
				->whereIn('status', [ClaimStatus::PENDING->value, ClaimStatus::APPROVED->value])
				->exists();

			if ($hasActiveClaim) {
				return response()->json(['message' => 'You already have an active claim for this item.'], 422);
			}

			$contactName = $validated['contactName'] ?? $user->name;
			$legacyContactInfo = $validated['contactInfo'] ?? null;
			$email = $validated['email'] ?? null;
			$phoneNumber = $validated['phoneNumber'] ?? null;

			if ($legacyContactInfo) {
				if (!$email && filter_var($legacyContactInfo, FILTER_VALIDATE_EMAIL)) {
					$email = $legacyContactInfo;
				} elseif (!$phoneNumber) {
					$phoneNumber = $legacyContactInfo;
				}
			}

			if (!$email && $user->email) {
				$email = $user->email;
			}

			$legacyContactInfo = $this->buildLegacyContactInfo($email, $phoneNumber, $legacyContactInfo);
			$imagePath = $this->storeClaimImage($request, $item->id, $user->id);

			$matchedLostItemId = $validated['matchedLostItemId'] ?? null;
			$claimMessage = $validated['message'];

			$meta = [
				'hasMultipleClaims' => false,
			];

			if ($item->status === FoundItemStatus::CLAIM_PENDING && (int) $item->claimed_by !== (int) $user->id) {
				$claim = ClaimedItem::create([
					'found_item_id' => $item->id,
					'claimant_id' => $user->id,
					'message' => $claimMessage,
					'claimant_contact_name' => $contactName,
					'claimant_contact_info' => $legacyContactInfo,
					'claimant_email' => $email,
					'claimant_phone' => $phoneNumber,
					'claim_image' => $imagePath,
					'matched_lost_item_id' => $matchedLostItemId,
					'status' => ClaimStatus::PENDING->value,
				]);

				$this->dispatchClaimSubmittedEvent($claim, $item, $user);

				$meta['hasMultipleClaims'] = true;
				$meta['message'] = 'Your claim has been submitted. This item already has other pending claims.';
				$meta['claimId'] = $claim->id;

				$notification = \App\Services\NotificationMessageService::generate('claimSubmitted', [
					'item_title' => $item->title,
					'user_name' => $user->name,
				]);
				SendNotificationJob::dispatch(
					$user->id,
					$notification['title'],
					$notification['body'],
					'claimSubmitted',
					$claim->id
				);

				$admins = \App\Models\User::where('role', 'admin')->get();
				foreach ($admins as $admin) {
					$notification = \App\Services\NotificationMessageService::generate('multipleClaims', [
						'item_title' => $item->title,
					]);
					SendNotificationJob::dispatch(
						$admin->id,
						$notification['title'],
						$notification['body'],
						'multipleClaims',
						$item->id
					);
				}

				$item->refresh();
				$this->loadItemRelations($item);

				return $this->respondWithItemResource($item, 200, $meta);
			}

			$item->claimed_by = $user->id;
			$item->claim_message = $claimMessage;
			$item->claimant_contact_name = $contactName;
			$item->claimant_contact_info = $legacyContactInfo;
			$item->claimant_email = $email;
			$item->claimant_phone = $phoneNumber;
			$item->claim_image = $imagePath;
			$item->markClaimPending(now());
			$item->save();

			$claimRecord = ClaimedItem::create([
				'found_item_id' => $item->id,
				'claimant_id' => $user->id,
				'message' => $claimMessage,
				'claimant_contact_name' => $contactName,
				'claimant_contact_info' => $legacyContactInfo,
				'claimant_email' => $email,
				'claimant_phone' => $phoneNumber,
				'claim_image' => $imagePath,
				'matched_lost_item_id' => $matchedLostItemId,
				'status' => ClaimStatus::PENDING->value,
			]);

			$this->dispatchClaimSubmittedEvent($claimRecord, $item, $user);

			$meta['claimId'] = $claimRecord->id;
			$meta['message'] = 'Claim submitted. Admin will review shortly.';

			$notification = \App\Services\NotificationMessageService::generate('claimSubmitted', [
				'item_title' => $item->title,
				'user_name' => $user->name,
			]);
			SendNotificationJob::dispatch(
				$user->id,
				$notification['title'],
				$notification['body'],
				'claimSubmitted',
				$claimRecord->id
			);

			$admins = \App\Models\User::where('role', 'admin')->get();
			$claimMessagePreview = strlen($item->claim_message) > 100
				? substr($item->claim_message, 0, 100) . '...'
				: $item->claim_message;

			$categoryName = $item->category ? $item->category->name : 'Unknown';

			foreach ($admins as $admin) {
				$notification = \App\Services\NotificationMessageService::generate('newClaim', [
					'item_title' => $item->title,
					'claimant_name' => $user->name,
					'claimant_email' => $email,
					'category' => $categoryName,
					'location' => $item->location,
					'message_preview' => $claimMessagePreview,
				]);
				SendNotificationJob::dispatch(
					$admin->id,
					$notification['title'],
					$notification['body'],
					'newClaim',
					$item->id
				);
			}

			$item->refresh();
			$this->loadItemRelations($item);

			return $this->respondWithItemResource($item, 200, $meta);
		} catch (\Exception $e) {
			return response()->json(['error' => 'Failed to submit claim', 'message' => $e->getMessage()], 500);
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
     * Expects: { "itemIds": [1,2,3], "types": ["lost","found","lost"], ... }
     * OR: { "lostItemIds": [1,2], "foundItemIds": [3,4], ... }
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
                'itemIds' => 'nullable|array|min:1',
                'itemIds.*' => 'integer',
                'types' => 'nullable|array',
                'types.*' => 'in:lost,found',
                'lostItemIds' => 'nullable|array',
                'lostItemIds.*' => 'integer',
                'foundItemIds' => 'nullable|array',
                'foundItemIds.*' => 'integer',
                'topK' => 'nullable|integer|min:1|max:50',
                'threshold' => 'nullable|numeric|min:0|max:1',
            ]);

            $topK = (int)($request->input('topK', config('services.navistfind_ai.top_k', 10)));
            $threshold = (float)($request->input('threshold', config('services.navistfind_ai.threshold', 0.6)));

            // Collect all items from both tables
            $items = collect();
            
            // Support both formats: itemIds+types or separate arrays
            if ($request->has('lostItemIds') || $request->has('foundItemIds')) {
                if ($request->has('lostItemIds')) {
                    $lostItems = LostItem::whereIn('id', $request->lostItemIds)->get();
                    foreach ($lostItems as $item) {
                        $items->push(['item' => $item, 'type' => 'lost']);
                    }
                }
                if ($request->has('foundItemIds')) {
                    $foundItems = FoundItem::whereIn('id', $request->foundItemIds)->get();
                    foreach ($foundItems as $item) {
                        $items->push(['item' => $item, 'type' => 'found']);
                    }
                }
            } else if ($request->has('itemIds')) {
                $itemIds = $request->itemIds;
                $types = $request->input('types', []);
                
                // Fetch items based on provided types
                foreach ($itemIds as $index => $itemId) {
                    $type = $types[$index] ?? null;
                    if ($type === 'lost') {
                        $item = LostItem::find($itemId);
                        if ($item) {
                            $items->push(['item' => $item, 'type' => 'lost']);
                        }
                    } else if ($type === 'found') {
                        $item = FoundItem::find($itemId);
                        if ($item) {
                            $items->push(['item' => $item, 'type' => 'found']);
                        }
                    } else {
                        // Auto-detect: try found first, then lost
                        $item = FoundItem::find($itemId);
                        if ($item) {
                            $items->push(['item' => $item, 'type' => 'found']);
                        } else {
                            $item = LostItem::find($itemId);
                            if ($item) {
                                $items->push(['item' => $item, 'type' => 'lost']);
                            }
                        }
                    }
                }
            }

            if ($items->isEmpty()) {
                return response()->json([], 200);
            }

            $results = [];
            foreach ($items as $entry) {
                $ref = $entry['item'];
                $refType = $entry['type'];
                
                $candidates = collect();
                if ($refType === 'lost') {
                    $candidates = FoundItem::where('status', FoundItemStatus::FOUND_UNCLAIMED->value)
                        ->where('id', '!=', $ref->id)
                        ->latest('created_at')
                        ->limit(200)
                        ->get();
                } else {
                    $candidates = LostItem::where('status', LostItemStatus::LOST_REPORTED->value)
                        ->where('id', '!=', $ref->id)
                        ->latest('created_at')
                        ->limit(200)
                        ->get();
                }

                $matches = [];
                if ($candidates->isNotEmpty()) {
                    $candidateClass = $refType === 'lost' ? FoundItem::class : LostItem::class;
                    $raw = $aiService->matchLostAndFound($ref, $candidates->all(), $candidateClass);
                    $filtered = array_values(array_filter($raw, function ($m) use ($threshold) {
                        return isset($m['score']) && $m['score'] >= $threshold && isset($m['item']);
                    }));
                    usort($filtered, function ($a, $b) { return ($b['score'] <=> $a['score']); });
                    $matches = array_slice($filtered, 0, $topK);
                }

                $results[] = [
                    'referenceId' => $ref->id,
                    'referenceType' => $refType,
                    'matches' => $matches,
                ];
            }

            return response()->json($results, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to batch match', 'message' => $e->getMessage()], 500);
        }
    }

	private function respondWithItemResource($item, int $status = 200, array $meta = [])
	{
		try {
			$resource = $this->resourceForModel($item);

			if (!empty($meta)) {
				$resource->additional(['meta' => $meta]);
			}

			$response = $resource->response()->setStatusCode($status);
			
			// Ensure JSON content type is set
			$response->header('Content-Type', 'application/json');
			
			return $response;
		} catch (\Exception $e) {
			\Log::error('RESOURCE_RESPONSE_ERROR', [
				'message' => $e->getMessage(),
				'trace' => $e->getTraceAsString(),
			]);
			// Fallback to simple JSON response
			return response()->json([
				'id' => $item->id ?? null,
				'title' => $item->title ?? null,
				'error' => 'Failed to format resource response',
				'message' => $e->getMessage()
			], $status)->header('Content-Type', 'application/json');
		}
	}

	private function resourceForModel($item)
	{
		if ($item instanceof FoundItem) {
			return new FoundItemResource($item);
		}

		return new LostItemResource($item);
	}

	private function loadItemRelations($item): void
	{
		if ($item instanceof FoundItem) {
			$item->loadMissing(['category', 'claims.claimant', 'transitionLogs.user']);
		} else {
			$item->loadMissing(['category', 'transitionLogs.user']);
		}
	}

	private function buildLegacyContactInfo(?string $email, ?string $phone, ?string $fallback = null): ?string
	{
		if ($fallback) {
			return $fallback;
		}

		$parts = [];

		if ($email) {
			$parts[] = "Email: {$email}";
		}

		if ($phone) {
			$parts[] = "Phone: {$phone}";
		}

		return !empty($parts) ? implode(' | ', $parts) : null;
	}

	private function storeClaimImage(Request $request, int $itemId, int $userId): ?string
	{
		if (!$request->hasFile('image')) {
			return null;
		}

		$image = $request->file('image');
		$extension = $image->getClientOriginalExtension();
		$fileName = sprintf('%s_%s_%s.%s', now()->timestamp, $itemId, $userId, $extension);

		return $image->storeAs('claim_images', $fileName, 'public');
	}

	private function dispatchClaimSubmittedEvent(\App\Models\ClaimedItem $claim, FoundItem $item, $claimant): void
	{
		$domainEvents = app(DomainEventService::class);

		$domainEvents->dispatch(
			'claim.submitted',
			[
				'claimId' => $claim->id,
				'foundItem' => [
					'id' => $item->id,
					'status' => $item->status->value,
					'title' => $item->title,
				],
				'claimant' => [
					'id' => $claimant->id,
					'name' => $claimant->name,
				],
				'message' => $claim->message,
				'submittedAt' => $claim->created_at?->toIso8601String(),
			],
			[
				'id' => $claimant->id,
				'role' => 'student',
			],
			'campus-nav.api'
		);
	}
}
