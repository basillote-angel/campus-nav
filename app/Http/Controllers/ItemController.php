<?php

namespace App\Http\Controllers;

use App\Enums\FoundItemStatus;
use App\Enums\LostItemStatus;
use App\Jobs\ComputeItemMatches;
use App\Models\Category;
use App\Models\FoundItem;
use App\Models\LostItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ItemController extends Controller
{
    public function index(Request $request) {
        $type = $request->query('type', ''); // default to all types

        // Get categories for filter dropdown (cached for 1 hour)
        $categories = Cache::remember('categories.list', now()->addHour(), function () {
            return Category::orderBy('name')->get(['id', 'name']);
        });
        
        // Get sort parameters
        $sortColumn = $request->query('sort', 'created_at');
        $sortDirection = $request->query('direction', 'desc');
        
        // Validate sort column and direction
        $allowedSortColumns = ['title', 'category_id', 'status', 'date_lost', 'date_found', 'location', 'created_at'];
        $allowedDirections = ['asc', 'desc'];
        
        if (!in_array($sortColumn, $allowedSortColumns)) {
            $sortColumn = 'created_at';
        }
        if (!in_array($sortDirection, $allowedDirections)) {
            $sortDirection = 'desc';
        }

        $lostStatusValues = LostItemStatus::values();
        $foundStatusValues = FoundItemStatus::values();
        
        if ($type === 'lost') {
            $query = LostItem::query();

            if ($request->filled('status')) {
                $statuses = is_array($request->status) ? $request->status : [$request->status];
                $filteredStatuses = array_intersect($statuses, $lostStatusValues);
                if (!empty($filteredStatuses)) {
                    $query->whereIn('status', $filteredStatuses);
                }
            }
            
            if ($request->filled('category')) {
                $categoryIds = is_array($request->category) ? $request->category : [$request->category];
                $query->whereIn('category_id', $categoryIds);
            }

            if ($request->filled('search')) {
                $searchTerm = trim($request->search);
                $kw = '%' . $searchTerm . '%';
                $dbDriver = DB::connection()->getDriverName();
                
                // Use full-text search for MySQL/MariaDB (3+ chars), fall back to LIKE for SQLite
                $useFullText = (strlen($searchTerm) >= 3) && in_array($dbDriver, ['mysql', 'mariadb']);
                
                if ($useFullText) {
                    // Use full-text search for better performance on indexed columns (MySQL/MariaDB only)
                    $query->where(function ($q) use ($searchTerm, $kw) {
                        $q->whereRaw('MATCH(title, description) AGAINST(? IN BOOLEAN MODE)', [$searchTerm])
                          ->orWhere('location', 'like', $kw);
                    });
                } else {
                    // Fall back to LIKE for short search terms or SQLite
                    $query->where(function ($q) use ($kw) {
                        $q->where('title', 'LIKE', $kw)
                          ->orWhere('description', 'LIKE', $kw)
                          ->orWhere('location', 'LIKE', $kw);
                    });
                }
            }

            // Advanced filters
            if ($request->filled('date_from')) {
                $query->where('date_lost', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->where('date_lost', '<=', $request->date_to);
            }

            // Apply sorting
            if ($sortColumn === 'category_id') {
                $query->with(['category', 'user'])
                      ->join('categories', 'lost_items.category_id', '=', 'categories.id')
                      ->select('lost_items.*')
                      ->orderBy('categories.name', $sortDirection);
            } else {
                $query->with(['category', 'user'])->orderBy($sortColumn, $sortDirection);
            }

            $items = $query->paginate(10);

            // Map to legacy fields expected by views (name, lost_found_date, type)
            $items->getCollection()->transform(function ($i) {
                return [
                    'id' => $i->id,
                    'name' => $i->title,
                    'description' => $i->description,
                    'location' => $i->location,
                    'status' => $i->status,
                    'type' => 'lost',
                    'lost_found_date' => $i->date_lost,
                    'category' => optional($i->category)->name,
                    'category_id' => $i->category_id,
                    'image_path' => $i->image_path,
                    'user_id' => $i->user_id,
                    'user_name' => optional($i->user)->name,
                    'user_role' => optional($i->user)->role,
                    'created_at' => $i->created_at,
                    'updated_at' => $i->updated_at,
                ];
            });
        } else if ($type === 'found') {
            $query = FoundItem::query();

            if ($request->filled('status')) {
                $statuses = is_array($request->status) ? $request->status : [$request->status];
                $filteredStatuses = array_intersect($statuses, $foundStatusValues);
                if (!empty($filteredStatuses)) {
                    $query->whereIn('status', $filteredStatuses);
                }
            }
            
            if ($request->filled('category')) {
                $categoryIds = is_array($request->category) ? $request->category : [$request->category];
                $query->whereIn('category_id', $categoryIds);
            }

            if ($request->filled('search')) {
                $searchTerm = trim($request->search);
                $kw = '%' . $searchTerm . '%';
                $dbDriver = DB::connection()->getDriverName();
                
                // Use full-text search for MySQL/MariaDB (3+ chars), fall back to LIKE for SQLite
                $useFullText = (strlen($searchTerm) >= 3) && in_array($dbDriver, ['mysql', 'mariadb']);
                
                if ($useFullText) {
                    // Use full-text search for better performance on indexed columns (MySQL/MariaDB only)
                    $query->where(function ($q) use ($searchTerm, $kw) {
                        $q->whereRaw('MATCH(title, description) AGAINST(? IN BOOLEAN MODE)', [$searchTerm])
                          ->orWhere('location', 'like', $kw);
                    });
                } else {
                    // Fall back to LIKE for short search terms or SQLite
                    $query->where(function ($q) use ($kw) {
                        $q->where('title', 'LIKE', $kw)
                          ->orWhere('description', 'LIKE', $kw)
                          ->orWhere('location', 'LIKE', $kw);
                    });
                }
            }

            // Advanced filters
            if ($request->filled('date_from')) {
                $query->where('date_found', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->where('date_found', '<=', $request->date_to);
            }

            // Apply sorting
            if ($sortColumn === 'category_id') {
                $query->with(['category', 'user'])
                      ->join('categories', 'found_items.category_id', '=', 'categories.id')
                      ->select('found_items.*')
                      ->orderBy('categories.name', $sortDirection);
            } else {
                // Map date_lost to date_found for found items
                $sortField = ($sortColumn === 'date_lost') ? 'date_found' : $sortColumn;
                $query->with(['category', 'user'])->orderBy($sortField, $sortDirection);
            }

            $items = $query->paginate(10);

            $items->getCollection()->transform(function ($i) {
                return [
                    'id' => $i->id,
                    'name' => $i->title,
                    'description' => $i->description,
                    'location' => $i->location,
                    'status' => $i->status,
                    'type' => 'found',
                    'lost_found_date' => $i->date_found,
                    'category' => optional($i->category)->name,
                    'category_id' => $i->category_id,
                    'image_path' => $i->image_path,
                    'user_id' => $i->user_id,
                    'user_name' => optional($i->user)->name,
                    'user_role' => optional($i->user)->role,
                    'created_at' => $i->created_at,
                    'updated_at' => $i->updated_at,
                ];
            });
        } else {
            // All types: build a single paginated union query in SQL
            $perPage = 10;

            $lost = DB::table('lost_items')
                ->leftJoin('categories', 'lost_items.category_id', '=', 'categories.id')
                ->leftJoin('users', 'lost_items.user_id', '=', 'users.id')
                ->select([
                    'lost_items.id as id',
                    DB::raw("lost_items.title as name"),
                    'lost_items.description as description',
                    'lost_items.location as location',
                    'lost_items.status as status',
                    DB::raw("'lost' as type"),
                    'lost_items.date_lost as lost_found_date',
                    'lost_items.image_path as image_path',
                    'lost_items.category_id as category_id',
                    'lost_items.user_id as user_id',
                    'categories.name as category',
                    'users.name as user_name',
                    'users.role as user_role',
                    'lost_items.created_at as created_at',
                    'lost_items.updated_at as updated_at',
                ]);

            $found = DB::table('found_items')
                ->leftJoin('categories', 'found_items.category_id', '=', 'categories.id')
                ->leftJoin('users', 'found_items.user_id', '=', 'users.id')
                ->select([
                    'found_items.id as id',
                    DB::raw("found_items.title as name"),
                    'found_items.description as description',
                    'found_items.location as location',
                    'found_items.status as status',
                    DB::raw("'found' as type"),
                    'found_items.date_found as lost_found_date',
                    'found_items.image_path as image_path',
                    'found_items.category_id as category_id',
                    'found_items.user_id as user_id',
                    'categories.name as category',
                    'users.name as user_name',
                    'users.role as user_role',
                    'found_items.created_at as created_at',
                    'found_items.updated_at as updated_at',
                ]);

            if ($request->filled('search')) {
                $searchTerm = trim($request->search);
                $kw = '%' . $searchTerm . '%';
                $dbDriver = DB::connection()->getDriverName();
                
                // Use full-text search for MySQL/MariaDB (3+ chars), fall back to LIKE for SQLite
                $useFullText = (strlen($searchTerm) >= 3) && in_array($dbDriver, ['mysql', 'mariadb']);
                
                if ($useFullText) {
                    // Use full-text search for better performance on indexed columns (MySQL/MariaDB only)
                    $lost->where(function ($q) use ($searchTerm, $kw) {
                        $q->whereRaw('MATCH(lost_items.title, lost_items.description) AGAINST(? IN BOOLEAN MODE)', [$searchTerm])
                          ->orWhere('lost_items.location', 'like', $kw);
                    });
                    $found->where(function ($q) use ($searchTerm, $kw) {
                        $q->whereRaw('MATCH(found_items.title, found_items.description) AGAINST(? IN BOOLEAN MODE)', [$searchTerm])
                          ->orWhere('found_items.location', 'like', $kw);
                    });
                } else {
                    // Fall back to LIKE for short search terms or SQLite
                    $lost->where(function ($q) use ($kw) {
                        $q->where('lost_items.title', 'like', $kw)
                          ->orWhere('lost_items.description', 'like', $kw)
                          ->orWhere('lost_items.location', 'like', $kw);
                    });
                    $found->where(function ($q) use ($kw) {
                        $q->where('found_items.title', 'like', $kw)
                          ->orWhere('found_items.description', 'like', $kw)
                          ->orWhere('found_items.location', 'like', $kw);
                    });
                }
            }

            if ($request->filled('status')) {
                $statuses = is_array($request->status) ? $request->status : [$request->status];
                $lostStatuses = array_intersect($statuses, $lostStatusValues);
                $foundStatuses = array_intersect($statuses, $foundStatusValues);
                
                if (!empty($lostStatuses)) {
                    $lost->whereIn('lost_items.status', $lostStatuses);
                }
                if (!empty($foundStatuses)) {
                    $found->whereIn('found_items.status', $foundStatuses);
                }
            }

            if ($request->filled('category')) {
                $categoryIds = is_array($request->category) ? $request->category : [$request->category];
                $lost->whereIn('lost_items.category_id', $categoryIds);
                $found->whereIn('found_items.category_id', $categoryIds);
            }

            // Advanced filters for union query
            if ($request->filled('date_from')) {
                $lost->where('lost_items.date_lost', '>=', $request->date_from);
                $found->where('found_items.date_found', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $lost->where('lost_items.date_lost', '<=', $request->date_to);
                $found->where('found_items.date_found', '<=', $request->date_to);
            }

            // Map sort column for union query
            $sortColumnForUnion = $sortColumn;
            if ($sortColumn === 'category_id') {
                $sortColumnForUnion = 'category'; // Use the joined category name
            } elseif ($sortColumn === 'date_lost') {
                $sortColumnForUnion = 'lost_found_date'; // Use the unified column name
            } elseif ($sortColumn === 'title') {
                $sortColumnForUnion = 'name'; // Use the unified column name
            }

            $merged = DB::query()
                ->fromSub($lost->unionAll($found), 'items')
                ->orderBy($sortColumnForUnion, $sortDirection)
                ->paginate($perPage);

            // Normalize items to arrays to match blade expectations ($item['key'])
            $merged->getCollection()->transform(function ($row) {
                return [
                    'id' => $row->id,
                    'name' => $row->name,
                    'description' => $row->description,
                    'location' => $row->location,
                    'status' => $row->status,
                    'type' => $row->type,
                    'lost_found_date' => $row->lost_found_date,
                    'category' => $row->category,
                    'category_id' => $row->category_id ?? null,
                    'image_path' => $row->image_path ?? null,
                    'user_id' => $row->user_id ?? null,
                    'user_name' => $row->user_name ?? null,
                    'user_role' => $row->user_role ?? null,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ];
            });

            $items = $merged;
        }

        // Pass sort info to view
        $currentSort = [
            'column' => $sortColumn,
            'direction' => $sortDirection,
        ];

        if ($request->ajax()) {
            return response()->view('components.item-table', compact('items', 'categories', 'currentSort'));
        }

        return view('item', compact('items', 'categories', 'currentSort'));
    }

    public function edit(Request $request, $id) {
        $type = $request->query('type');
        if ($type === 'lost') {
            $model = LostItem::findOrFail($id);
        } else if ($type === 'found') {
            $model = FoundItem::findOrFail($id);
        } else {
            // Fallback for legacy links without type
            $model = LostItem::find($id) ?: FoundItem::findOrFail($id);
        }

        // Normalize for view
        $rawDate = $model instanceof LostItem ? $model->date_lost : $model->date_found;
        $dateValue = $rawDate ? Carbon::parse($rawDate)->format('Y-m-d') : null;

        $item = (object) [
            'id' => $model->id,
            'name' => $model->title,
            'description' => $model->description,
            'location' => $model->location,
            'type' => $model instanceof LostItem ? 'lost' : 'found',
            'lost_found_date' => $dateValue,
            'status' => $model->status,
            'image_path' => $model->image_path,
        ];
        $categories = Category::orderBy('name')->get(['id','name']);
        $selectedCategoryId = $model->category_id;
        
        // Check if this is an AJAX/JSON request
        $isAjax = $request->ajax() 
            || $request->wantsJson() 
            || $request->header('X-Requested-With') === 'XMLHttpRequest'
            || $request->expectsJson();
        
        // If AJAX request, return JSON
        if ($isAjax) {
            return response()->json([
                'item' => $item,
                'categories' => $categories->map(function($cat) {
                    return [
                        'id' => $cat->id,
                        'name' => $cat->name
                    ];
                })->values(),
                'selectedCategoryId' => $selectedCategoryId
            ]);
        }
        
        return view('edit-item', compact('item','categories','selectedCategoryId'));
    }
    
    /**
     * Update the specified item in storage.
     */
    public function update(Request $request, $id)
    {
        // Determine item type first to validate status correctly
        $originalType = $request->input('originalType', $request->type);
        $lostStatusValues = LostItemStatus::values();
        $foundStatusValues = FoundItemStatus::values();
        $statusSet = $originalType === 'lost' ? $lostStatusValues : $foundStatusValues;

        $request->validate([
            'title' => 'required|string',
            'category_id' => 'nullable|integer|exists:categories,id',
            'description' => 'required|string',
            'type' => 'required|in:lost,found',
            'status' => ['nullable', Rule::in($statusSet)],
            'location' => 'nullable|string',
            'date' => 'nullable|date',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
        ]);

        $model = $originalType === 'lost' ? (LostItem::findOrFail($id)) : (FoundItem::findOrFail($id));
        $payload = [
            'title' => $request->title,
            'description' => $request->description,
            'location' => $request->location,
        ];
        
        // Only update category_id if provided and not null
        if ($request->filled('category_id')) {
            $payload['category_id'] = (int) $request->category_id;
        }
        
        // Add status if provided
        if ($request->filled('status')) {
            $payload['status'] = $request->status;
        }
        
        if ($request->filled('date')) {
            if ($request->type === 'lost') {
                $payload['date_lost'] = $request->date;
            } else {
                $payload['date_found'] = $request->date;
            }
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($model->image_path && !str_starts_with($model->image_path, 'http')) {
                $oldImagePath = str_replace('storage/', '', $model->image_path);
                if (Storage::disk('public')->exists($oldImagePath)) {
                    Storage::disk('public')->delete($oldImagePath);
                }
            }
            
            // Store new image
            $imagePath = $request->file('image')->store('items', 'public');
            $payload['image_path'] = 'storage/' . $imagePath;
        }

        $model->update($payload);

        // Queue async match computation for the updated item
        $refType = $request->type === 'lost' ? 'lost' : 'found';
        ComputeItemMatches::dispatch($refType, $model->id);
        
        // Check if this is an AJAX/JSON request
        $isAjax = $request->ajax() 
            || $request->wantsJson() 
            || $request->header('X-Requested-With') === 'XMLHttpRequest'
            || $request->expectsJson();
        
        // If AJAX request, return JSON
        if ($isAjax) {
            return response()->json([
                'success' => true,
                'message' => 'Item updated successfully.'
            ]);
        }
        
        session()->flash('success', 'Item updated successfully.');
        return redirect()->route('item');
    }

    /**
     * Remove the specified item from storage.
     */
    public function destroy(Request $request, $id)
    {
        $itemType = $request->input('type');
        if ($itemType === 'lost') {
            $model = LostItem::findOrFail($id);
        } else if ($itemType === 'found') {
            $model = FoundItem::findOrFail($id);
        } else {
            // Fallback for legacy requests without type (attempt both)
            $model = LostItem::find($id) ?: FoundItem::findOrFail($id);
        }

        $model->delete();

        session()->flash('success', 'Item deleted successfully.');
        return redirect()->route('item');
    }

    public function store(Request $request) {
        // Accept both new (title, category_id, date) and legacy (name, category, lost_found_date)
        $request->validate([
            'type' => 'required|in:lost,found',
            'description' => 'required|string',
            'location' => 'nullable|string',
            'category_id' => 'nullable|integer|exists:categories,id',
        ]);

        $title = $request->input('title') ?? $request->input('name');
        $date = $request->input('date') ?? $request->input('lost_found_date');

        // Resolve category_id from either explicit id or legacy category name
        $categoryId = $request->input('category_id');
        if (!$categoryId && $request->filled('category')) {
            $catName = $request->input('category');
            $cat = Category::whereRaw('LOWER(name) = ?', [strtolower($catName)])->first();
            if ($cat) { $categoryId = $cat->id; }
        }

        if (!$categoryId) {
            return response()->json(['success' => false, 'message' => 'Category is required.'], 422);
        }

        if (!$title) {
            return response()->json(['success' => false, 'message' => 'Title is required.'], 422);
        }

        $userId = Auth::id();

        if ($request->type === 'lost') {
            $created = LostItem::create([
                'user_id' => $userId,
                'category_id' => $categoryId,
                'title' => $title,
                'description' => $request->description,
                'location' => $request->location,
                'date_lost' => $date,
                'status' => LostItemStatus::LOST_REPORTED->value,
            ]);
        } else {
            $created = FoundItem::create([
                'user_id' => $userId,
                'category_id' => $categoryId,
                'title' => $title,
                'description' => $request->description,
                'location' => $request->location,
                'date_found' => $date,
                'status' => FoundItemStatus::FOUND_UNCLAIMED->value,
            ]);
        }

        if ($created) {
            // Queue async match computation for the new item
            $refType = $request->type === 'lost' ? 'lost' : 'found';
            ComputeItemMatches::dispatch($refType, $created->id);

            return response()->json(['success' => true, 'matchesQueued' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Create failed']);
    }

    /**
     * Export items as CSV or PDF
     */
    public function export(Request $request)
    {
        $format = $request->input('format', 'csv');
        $type = $request->query('type', '');
        $status = $request->query('status', '');
        $category = $request->query('category', '');
        $search = $request->query('search', '');
        $ids = $request->query('ids', ''); // For bulk export

        $lostStatusValues = LostItemStatus::values();
        $foundStatusValues = FoundItemStatus::values();

        // Handle bulk export (specific IDs)
        if ($ids) {
            $idArray = explode(',', $ids);
            $lostItems = LostItem::whereIn('id', $idArray)->with(['category', 'user', 'matches'])->get();
            $foundItems = FoundItem::whereIn('id', $idArray)->with(['category', 'user', 'matches', 'claimedBy', 'approvedBy', 'collectedBy', 'claims'])->get();
            
            $items = collect();
            foreach ($lostItems as $i) {
                $items->push([
                    'id' => $i->id,
                    'name' => $i->title,
                    'title' => $i->title,
                    'description' => $i->description,
                    'location' => $i->location,
                    'status' => $i->status->value ?? $i->status,
                    'type' => 'lost',
                    'date' => $i->date_lost ? $i->date_lost->format('Y-m-d') : '',
                    'date_lost' => $i->date_lost ? $i->date_lost->format('Y-m-d') : '',
                    'category' => optional($i->category)->name,
                    'category_id' => $i->category_id,
                    'posted_by' => optional($i->user)->name,
                    'user_name' => optional($i->user)->name,
                    'user_email' => optional($i->user)->email ?? '',
                    'posted_by_email' => optional($i->user)->email ?? '',
                    'match_count' => $i->matches()->count(),
                    'best_match_score' => $i->matches()->max('similarity_score'),
                    'created_at' => $i->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $i->updated_at->format('Y-m-d H:i:s'),
                ]);
            }
            foreach ($foundItems as $i) {
                $items->push([
                    'id' => $i->id,
                    'name' => $i->title,
                    'title' => $i->title,
                    'description' => $i->description,
                    'location' => $i->location,
                    'status' => $i->status->value ?? $i->status,
                    'type' => 'found',
                    'date' => $i->date_found ? $i->date_found->format('Y-m-d') : '',
                    'date_found' => $i->date_found ? $i->date_found->format('Y-m-d') : '',
                    'category' => optional($i->category)->name,
                    'category_id' => $i->category_id,
                    'posted_by' => optional($i->user)->name,
                    'user_name' => optional($i->user)->name,
                    'user_email' => optional($i->user)->email ?? '',
                    'posted_by_email' => optional($i->user)->email ?? '',
                    'claim_status' => $i->status->value ?? $i->status,
                    'claimed_by' => optional($i->claimedBy)->name,
                    'claimed_at' => $i->claimed_at ? $i->claimed_at->format('Y-m-d H:i:s') : null,
                    'approved_by' => optional($i->approvedBy)->name,
                    'approved_at' => $i->approved_at ? $i->approved_at->format('Y-m-d H:i:s') : null,
                    'collection_deadline' => $i->collection_deadline ? $i->collection_deadline->format('Y-m-d H:i:s') : null,
                    'collected_at' => $i->collected_at ? $i->collected_at->format('Y-m-d H:i:s') : null,
                    'collection_notes' => $i->collection_notes,
                    'match_count' => $i->matches()->count(),
                    'created_at' => $i->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $i->updated_at->format('Y-m-d H:i:s'),
                ]);
            }
            $items = $items->sortByDesc('created_at')->values();
        }
        // Build query similar to index method
        else if ($type === 'lost') {
            $query = LostItem::query();
            if ($status && in_array($status, $lostStatusValues, true)) {
                $query->where('status', $status);
            }
            if ($category) $query->where('category_id', $category);
            if ($search) {
                $kw = '%' . $search . '%';
                $query->where(function ($q) use ($kw) {
                    $q->where('title', 'LIKE', $kw)
                      ->orWhere('description', 'LIKE', $kw)
                      ->orWhere('location', 'LIKE', $kw);
                });
            }
            // Process in chunks to avoid memory issues with large datasets
            $items = collect();
            $query->with(['category', 'user', 'matches'])->latest('created_at')->chunk(500, function ($chunk) use (&$items) {
                $chunk->each(function ($i) use (&$items) {
                    $items->push([
                        'id' => $i->id,
                        'name' => $i->title,
                        'title' => $i->title,
                        'description' => $i->description,
                        'location' => $i->location,
                        'status' => $i->status->value ?? $i->status,
                        'type' => 'lost',
                        'date' => $i->date_lost ? $i->date_lost->format('Y-m-d') : '',
                        'date_lost' => $i->date_lost ? $i->date_lost->format('Y-m-d') : '',
                        'category' => optional($i->category)->name,
                        'category_id' => $i->category_id,
                        'posted_by' => optional($i->user)->name,
                        'user_name' => optional($i->user)->name,
                        'user_email' => optional($i->user)->email ?? '',
                        'posted_by_email' => optional($i->user)->email ?? '',
                        'match_count' => $i->matches()->count(),
                        'best_match_score' => $i->matches()->max('similarity_score'),
                        'created_at' => $i->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => $i->updated_at->format('Y-m-d H:i:s'),
                    ]);
                });
            });
        } else if ($type === 'found') {
            $query = FoundItem::query();
            if ($status && in_array($status, $foundStatusValues, true)) {
                $query->where('status', $status);
            }
            if ($category) $query->where('category_id', $category);
            if ($search) {
                $kw = '%' . $search . '%';
                $query->where(function ($q) use ($kw) {
                    $q->where('title', 'LIKE', $kw)
                      ->orWhere('description', 'LIKE', $kw)
                      ->orWhere('location', 'LIKE', $kw);
                });
            }
            // Process in chunks to avoid memory issues with large datasets
            $items = collect();
            $query->with(['category', 'user', 'matches', 'claimedBy', 'approvedBy', 'collectedBy', 'claims'])->latest('created_at')->chunk(500, function ($chunk) use (&$items) {
                $chunk->each(function ($i) use (&$items) {
                    $items->push([
                        'id' => $i->id,
                        'name' => $i->title,
                        'title' => $i->title,
                        'description' => $i->description,
                        'location' => $i->location,
                        'status' => $i->status->value ?? $i->status,
                        'type' => 'found',
                        'date' => $i->date_found ? $i->date_found->format('Y-m-d') : '',
                        'date_found' => $i->date_found ? $i->date_found->format('Y-m-d') : '',
                        'category' => optional($i->category)->name,
                        'category_id' => $i->category_id,
                        'posted_by' => optional($i->user)->name,
                        'user_name' => optional($i->user)->name,
                        'user_email' => optional($i->user)->email ?? '',
                        'posted_by_email' => optional($i->user)->email ?? '',
                        'claim_status' => $i->status->value ?? $i->status,
                        'claimed_by' => optional($i->claimedBy)->name,
                        'claimed_at' => $i->claimed_at ? $i->claimed_at->format('Y-m-d H:i:s') : null,
                        'approved_by' => optional($i->approvedBy)->name,
                        'approved_at' => $i->approved_at ? $i->approved_at->format('Y-m-d H:i:s') : null,
                        'collection_deadline' => $i->collection_deadline ? $i->collection_deadline->format('Y-m-d H:i:s') : null,
                        'collected_at' => $i->collected_at ? $i->collected_at->format('Y-m-d H:i:s') : null,
                        'collection_notes' => $i->collection_notes,
                        'match_count' => $i->matches()->count(),
                        'created_at' => $i->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => $i->updated_at->format('Y-m-d H:i:s'),
                    ]);
                });
            });
        } else {
            // All types - get both
            $lostQuery = LostItem::query();
            $foundQuery = FoundItem::query();
            
            if ($status) {
                if (in_array($status, $lostStatusValues, true)) {
                    $lostQuery->where('status', $status);
                }
                if (in_array($status, $foundStatusValues, true)) {
                    $foundQuery->where('status', $status);
                }
            }
            if ($category) {
                $lostQuery->where('category_id', $category);
                $foundQuery->where('category_id', $category);
            }
            if ($search) {
                $kw = '%' . $search . '%';
                $lostQuery->where(function ($q) use ($kw) {
                    $q->where('title', 'LIKE', $kw)
                      ->orWhere('description', 'LIKE', $kw)
                      ->orWhere('location', 'LIKE', $kw);
                });
                $foundQuery->where(function ($q) use ($kw) {
                    $q->where('title', 'LIKE', $kw)
                      ->orWhere('description', 'LIKE', $kw)
                      ->orWhere('location', 'LIKE', $kw);
                });
            }
            
            // Process in chunks to avoid memory issues with large datasets
            $lostItems = collect();
            $lostQuery->with(['category', 'user', 'matches'])->chunk(500, function ($chunk) use (&$lostItems) {
                $chunk->each(function ($i) use (&$lostItems) {
                    $lostItems->push([
                        'id' => $i->id,
                        'name' => $i->title,
                        'title' => $i->title,
                        'description' => $i->description,
                        'location' => $i->location,
                        'status' => $i->status->value ?? $i->status,
                        'type' => 'lost',
                        'date' => $i->date_lost ? $i->date_lost->format('Y-m-d') : '',
                        'date_lost' => $i->date_lost ? $i->date_lost->format('Y-m-d') : '',
                        'category' => optional($i->category)->name,
                        'category_id' => $i->category_id,
                        'posted_by' => optional($i->user)->name,
                        'user_name' => optional($i->user)->name,
                        'user_email' => optional($i->user)->email ?? '',
                        'posted_by_email' => optional($i->user)->email ?? '',
                        'match_count' => $i->matches()->count(),
                        'best_match_score' => $i->matches()->max('similarity_score'),
                        'created_at' => $i->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => $i->updated_at->format('Y-m-d H:i:s'),
                    ]);
                });
            });
            
            $foundItems = collect();
            $foundQuery->with(['category', 'user', 'matches', 'claimedBy', 'approvedBy', 'collectedBy', 'claims'])->chunk(500, function ($chunk) use (&$foundItems) {
                $chunk->each(function ($i) use (&$foundItems) {
                    $foundItems->push([
                        'id' => $i->id,
                        'name' => $i->title,
                        'title' => $i->title,
                        'description' => $i->description,
                        'location' => $i->location,
                        'status' => $i->status->value ?? $i->status,
                        'type' => 'found',
                        'date' => $i->date_found ? $i->date_found->format('Y-m-d') : '',
                        'date_found' => $i->date_found ? $i->date_found->format('Y-m-d') : '',
                        'category' => optional($i->category)->name,
                        'category_id' => $i->category_id,
                        'posted_by' => optional($i->user)->name,
                        'user_name' => optional($i->user)->name,
                        'user_email' => optional($i->user)->email ?? '',
                        'posted_by_email' => optional($i->user)->email ?? '',
                        'claim_status' => $i->status->value ?? $i->status,
                        'claimed_by' => optional($i->claimedBy)->name,
                        'claimed_at' => $i->claimed_at ? $i->claimed_at->format('Y-m-d H:i:s') : null,
                        'approved_by' => optional($i->approvedBy)->name,
                        'approved_at' => $i->approved_at ? $i->approved_at->format('Y-m-d H:i:s') : null,
                        'collection_deadline' => $i->collection_deadline ? $i->collection_deadline->format('Y-m-d H:i:s') : null,
                        'collected_at' => $i->collected_at ? $i->collected_at->format('Y-m-d H:i:s') : null,
                        'collection_notes' => $i->collection_notes,
                        'match_count' => $i->matches()->count(),
                        'created_at' => $i->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => $i->updated_at->format('Y-m-d H:i:s'),
                    ]);
                });
            });
            
            $items = $lostItems->concat($foundItems)->sortByDesc('created_at')->values();
        }

        if ($format === 'csv') {
            return $this->exportItemsCsv($items, $ids ? 'selected' : $type, $status, $category);
        }

        // Only CSV export is supported
        return redirect()->route('item')->with('error', 'Invalid export format. Only CSV export is available.');
    }

    /**
     * Export items as CSV
     */
    private function exportItemsCsv($items, $type, $status, $category)
    {
        $typeLabel = $type ? ucfirst($type) : 'All';
        $filename = 'lost_found_items_' . $typeLabel . '_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($items, $typeLabel, $status, $category) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, ['Lost & Found Items Export Report']);
            fputcsv($file, ['Generated', now()->format('Y-m-d H:i:s')]);
            fputcsv($file, ['Type', $typeLabel ?: 'All Types']);
            if ($status) {
                fputcsv($file, ['Status', ucfirst($status)]);
            }
            if ($category) {
                $categoryName = \App\Models\Category::find($category)?->name ?? 'Unknown';
                fputcsv($file, ['Category', $categoryName]);
            }
            fputcsv($file, ['Total Records', $items->count()]);
            fputcsv($file, []); // Empty row
            
            // Column headers - Enhanced with more fields
            // Determine if we have both types
            $hasLost = $items->contains('type', 'lost');
            $hasFound = $items->contains('type', 'found');
            
            $headers = ['ID', 'Item Name', 'Type', 'Category', 'Description', 'Location', 'Status', 'Date Lost/Found', 'Posted By', 'Posted By Email'];
            
            // Add type-specific headers - always add all columns if both types exist
            if ($hasLost) {
                $headers = array_merge($headers, ['Match Count', 'Best Match Score']);
            }
            if ($hasFound) {
                $headers = array_merge($headers, ['Claim Status', 'Claimed By', 'Claimed At', 'Approved By', 'Approved At', 'Collection Deadline', 'Collected At', 'Collection Notes', 'Match Count']);
            }
            
            $headers = array_merge($headers, ['Created At', 'Updated At']);
            fputcsv($file, $headers);
            
            // Data rows - ensure all rows have same number of columns
            foreach ($items as $item) {
                $row = [
                    $item['id'],
                    $item['name'] ?? '',
                    ucfirst($item['type'] ?? ''),
                    $item['category'] ?? 'N/A',
                    $item['description'] ?? '',
                    $item['location'] ?? '',
                    ucfirst($item['status'] ?? ''),
                    $item['date'] ?? '',
                    $item['posted_by'] ?? $item['user_name'] ?? 'System',
                    $item['posted_by_email'] ?? $item['user_email'] ?? '',
                ];
                
                // Add type-specific data - ensure columns align properly
                if (($item['type'] ?? '') === 'lost') {
                    // Lost item columns
                    $row[] = $item['match_count'] ?? 0;
                    $row[] = isset($item['best_match_score']) && $item['best_match_score'] !== null ? number_format($item['best_match_score'], 2) : 'N/A';
                    // Add empty columns for found item fields if both types exist
                    if ($hasFound) {
                        $row[] = ''; // Claim Status
                        $row[] = ''; // Claimed By
                        $row[] = ''; // Claimed At
                        $row[] = ''; // Approved By
                        $row[] = ''; // Approved At
                        $row[] = ''; // Collection Deadline
                        $row[] = ''; // Collected At
                        $row[] = ''; // Collection Notes
                        $row[] = ''; // Match Count (for found)
                    }
                } elseif (($item['type'] ?? '') === 'found') {
                    // Add empty columns for lost item fields if both types exist
                    if ($hasLost) {
                        $row[] = ''; // Match Count (for lost)
                        $row[] = ''; // Best Match Score
                    }
                    // Found item columns
                    $row[] = $item['claim_status'] ?? $item['status'] ?? '';
                    $row[] = $item['claimed_by'] ?? 'N/A';
                    $row[] = $item['claimed_at'] ?? 'N/A';
                    $row[] = $item['approved_by'] ?? 'N/A';
                    $row[] = $item['approved_at'] ?? 'N/A';
                    $row[] = $item['collection_deadline'] ?? 'N/A';
                    $row[] = $item['collected_at'] ?? 'N/A';
                    $row[] = $item['collection_notes'] ?? 'N/A';
                    $row[] = $item['match_count'] ?? 0;
                }
                
                $row[] = $item['created_at'] ?? '';
                $row[] = $item['updated_at'] ?? '';
                
                fputcsv($file, $row);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }


    /**
     * Bulk update items (status, category, etc.)
     */
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|integer',
            'items.*.type' => 'required|in:lost,found',
            'action' => 'required|in:status,category',
            'value' => 'required',
        ]);

        $items = $request->input('items');
        $action = $request->input('action');
        $value = $request->input('value');
        $updated = 0;
        $lostStatusValues = LostItemStatus::values();
        $foundStatusValues = FoundItemStatus::values();

        foreach ($items as $itemData) {
            $id = $itemData['id'];
            $type = $itemData['type'];
            
            if ($type === 'lost') {
                $model = LostItem::find($id);
                if (!$model) continue;
                
                if ($action === 'status' && in_array($value, $lostStatusValues, true)) {
                    $model->status = $value;
                    $model->save();
                    $updated++;
                } elseif ($action === 'category') {
                    $model->category_id = $value;
                    $model->save();
                    $updated++;
                }
            } else if ($type === 'found') {
                $model = FoundItem::find($id);
                if (!$model) continue;
                
                if ($action === 'status' && in_array($value, $foundStatusValues, true)) {
                    $model->status = $value;
                    $model->save();
                    $updated++;
                } elseif ($action === 'category') {
                    $model->category_id = $value;
                    $model->save();
                    $updated++;
                }
            }
        }

        return response()->json([
            'success' => true,
            'count' => $updated,
            'message' => "Successfully updated {$updated} item(s)."
        ]);
    }

    /**
     * Bulk delete items
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|integer',
            'items.*.type' => 'required|in:lost,found',
        ]);

        $items = $request->input('items');
        $deleted = 0;

        foreach ($items as $itemData) {
            $id = $itemData['id'];
            $type = $itemData['type'];
            
            if ($type === 'lost') {
                $model = LostItem::find($id);
                if ($model) {
                    $model->delete();
                    $deleted++;
                }
            } else if ($type === 'found') {
                $model = FoundItem::find($id);
                if ($model) {
                    $model->delete();
                    $deleted++;
                }
            }
        }

        return response()->json([
            'success' => true,
            'count' => $deleted,
            'message' => "Successfully deleted {$deleted} item(s)."
        ]);
    }
}

