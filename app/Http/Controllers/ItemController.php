<?php

namespace App\Http\Controllers;

use App\Models\LostItem;
use App\Models\FoundItem;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use App\Jobs\ComputeItemMatches;

class ItemController extends Controller
{
    public function index(Request $request) {
        $type = $request->query('type', ''); // default to all types

        // Get categories for filter dropdown
        $categories = Category::orderBy('name')->get(['id', 'name']);
        
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
        
        if ($type === 'lost') {
            $query = LostItem::query();

            if ($request->filled('status')) {
                $statuses = is_array($request->status) ? $request->status : [$request->status];
                $query->whereIn('status', $statuses);
            }
            
            if ($request->filled('category')) {
                $categoryIds = is_array($request->category) ? $request->category : [$request->category];
                $query->whereIn('category_id', $categoryIds);
            }

            if ($request->filled('search')) {
                $kw = '%' . $request->search . '%';
                $query->where(function ($q) use ($kw) {
                    $q->where('title', 'LIKE', $kw)
                      ->orWhere('description', 'LIKE', $kw)
                      ->orWhere('location', 'LIKE', $kw);
                });
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
                $query->whereIn('status', $statuses);
            }
            
            if ($request->filled('category')) {
                $categoryIds = is_array($request->category) ? $request->category : [$request->category];
                $query->whereIn('category_id', $categoryIds);
            }

            if ($request->filled('search')) {
                $kw = '%' . $request->search . '%';
                $query->where(function ($q) use ($kw) {
                    $q->where('title', 'LIKE', $kw)
                      ->orWhere('description', 'LIKE', $kw)
                      ->orWhere('location', 'LIKE', $kw);
                });
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
                $kw = '%' . $request->search . '%';
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

            if ($request->filled('status')) {
                $statuses = is_array($request->status) ? $request->status : [$request->status];
                $lostStatuses = array_intersect($statuses, ['open', 'matched', 'closed']);
                $foundStatuses = array_intersect($statuses, ['unclaimed', 'matched', 'returned']);
                
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
        $request->validate([
            'title' => 'required|string',
            'category_id' => 'nullable|integer|exists:categories,id',
            'description' => 'required|string',
            'type' => 'required|in:lost,found',
            'location' => 'nullable|string',
            'date' => 'nullable|date',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
        ]);

        $originalType = $request->input('originalType', $request->type);
        $model = $originalType === 'lost' ? (LostItem::findOrFail($id)) : (FoundItem::findOrFail($id));
        $payload = [
            'title' => $request->title,
            'category_id' => $request->category_id,
            'description' => $request->description,
            'location' => $request->location,
        ];
        
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
        ]);

        $title = $request->input('title') ?? $request->input('name');
        $date = $request->input('date') ?? $request->input('lost_found_date');

        // Resolve category_id from either explicit id or legacy category name
        $categoryId = $request->input('category_id');
        if (!$categoryId && $request->filled('category')) {
            $catName = $request->input('category');
            $cat = Category::where('name', $catName)->first();
            if ($cat) { $categoryId = $cat->id; }
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
                'status' => 'open',
            ]);
        } else {
            $created = FoundItem::create([
                'user_id' => $userId,
                'category_id' => $categoryId,
                'title' => $title,
                'description' => $request->description,
                'location' => $request->location,
                'date_found' => $date,
                'status' => 'unclaimed',
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

        // Handle bulk export (specific IDs)
        if ($ids) {
            $idArray = explode(',', $ids);
            $lostItems = LostItem::whereIn('id', $idArray)->with(['category', 'user'])->get();
            $foundItems = FoundItem::whereIn('id', $idArray)->with(['category', 'user'])->get();
            
            $items = collect();
            foreach ($lostItems as $i) {
                $items->push([
                    'id' => $i->id,
                    'name' => $i->title,
                    'description' => $i->description,
                    'location' => $i->location,
                    'status' => $i->status,
                    'type' => 'lost',
                    'date' => $i->date_lost ? $i->date_lost->format('Y-m-d') : '',
                    'category' => optional($i->category)->name,
                    'posted_by' => optional($i->user)->name,
                    'created_at' => $i->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            foreach ($foundItems as $i) {
                $items->push([
                    'id' => $i->id,
                    'name' => $i->title,
                    'description' => $i->description,
                    'location' => $i->location,
                    'status' => $i->status,
                    'type' => 'found',
                    'date' => $i->date_found ? $i->date_found->format('Y-m-d') : '',
                    'category' => optional($i->category)->name,
                    'posted_by' => optional($i->user)->name,
                    'created_at' => $i->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            $items = $items->sortByDesc('created_at')->values();
        }
        // Build query similar to index method
        else if ($type === 'lost') {
            $query = LostItem::query();
            if ($status) $query->where('status', $status);
            if ($category) $query->where('category_id', $category);
            if ($search) {
                $kw = '%' . $search . '%';
                $query->where(function ($q) use ($kw) {
                    $q->where('title', 'LIKE', $kw)
                      ->orWhere('description', 'LIKE', $kw)
                      ->orWhere('location', 'LIKE', $kw);
                });
            }
            $items = $query->with(['category', 'user'])->latest('created_at')->get();
            $items = $items->map(function ($i) {
                return [
                    'id' => $i->id,
                    'name' => $i->title,
                    'description' => $i->description,
                    'location' => $i->location,
                    'status' => $i->status,
                    'type' => 'lost',
                    'date' => $i->date_lost ? $i->date_lost->format('Y-m-d') : '',
                    'category' => optional($i->category)->name,
                    'posted_by' => optional($i->user)->name,
                    'created_at' => $i->created_at->format('Y-m-d H:i:s'),
                ];
            });
        } else if ($type === 'found') {
            $query = FoundItem::query();
            if ($status) $query->where('status', $status);
            if ($category) $query->where('category_id', $category);
            if ($search) {
                $kw = '%' . $search . '%';
                $query->where(function ($q) use ($kw) {
                    $q->where('title', 'LIKE', $kw)
                      ->orWhere('description', 'LIKE', $kw)
                      ->orWhere('location', 'LIKE', $kw);
                });
            }
            $items = $query->with(['category', 'user'])->latest('created_at')->get();
            $items = $items->map(function ($i) {
                return [
                    'id' => $i->id,
                    'name' => $i->title,
                    'description' => $i->description,
                    'location' => $i->location,
                    'status' => $i->status,
                    'type' => 'found',
                    'date' => $i->date_found ? $i->date_found->format('Y-m-d') : '',
                    'category' => optional($i->category)->name,
                    'posted_by' => optional($i->user)->name,
                    'created_at' => $i->created_at->format('Y-m-d H:i:s'),
                ];
            });
        } else {
            // All types - get both
            $lostQuery = LostItem::query();
            $foundQuery = FoundItem::query();
            
            if ($status) {
                if (in_array($status, ['open', 'matched', 'closed'])) {
                    $lostQuery->where('status', $status);
                }
                if (in_array($status, ['unclaimed', 'matched', 'returned'])) {
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
            
            $lostItems = $lostQuery->with(['category', 'user'])->get()->map(function ($i) {
                return [
                    'id' => $i->id,
                    'name' => $i->title,
                    'description' => $i->description,
                    'location' => $i->location,
                    'status' => $i->status,
                    'type' => 'lost',
                    'date' => $i->date_lost ? $i->date_lost->format('Y-m-d') : '',
                    'category' => optional($i->category)->name,
                    'posted_by' => optional($i->user)->name,
                    'created_at' => $i->created_at->format('Y-m-d H:i:s'),
                ];
            });
            
            $foundItems = $foundQuery->with(['category', 'user'])->get()->map(function ($i) {
                return [
                    'id' => $i->id,
                    'name' => $i->title,
                    'description' => $i->description,
                    'location' => $i->location,
                    'status' => $i->status,
                    'type' => 'found',
                    'date' => $i->date_found ? $i->date_found->format('Y-m-d') : '',
                    'category' => optional($i->category)->name,
                    'posted_by' => optional($i->user)->name,
                    'created_at' => $i->created_at->format('Y-m-d H:i:s'),
                ];
            });
            
            $items = $lostItems->concat($foundItems)->sortByDesc('created_at')->values();
        }

        if ($format === 'csv') {
            return $this->exportItemsCsv($items, $ids ? 'selected' : $type, $status, $category);
        } elseif ($format === 'pdf') {
            return $this->exportItemsPdf($items, $ids ? 'selected' : $type, $status, $category);
        }

        return redirect()->route('item')->with('error', 'Invalid export format');
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
            
            // Column headers
            fputcsv($file, ['ID', 'Item Name', 'Type', 'Category', 'Description', 'Location', 'Status', 'Date Lost/Found', 'Posted By', 'Created At']);
            
            // Data rows
            foreach ($items as $item) {
                fputcsv($file, [
                    $item['id'],
                    $item['name'],
                    ucfirst($item['type']),
                    $item['category'] ?? 'N/A',
                    $item['description'] ?? '',
                    $item['location'] ?? '',
                    ucfirst($item['status']),
                    $item['date'] ?? '',
                    $item['posted_by'] ?? 'System',
                    $item['created_at'],
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export items as PDF (placeholder - returns message for now)
     */
    private function exportItemsPdf($items, $type, $status, $category)
    {
        // PDF export would require a library like dompdf or mpdf
        // For now, return a message indicating it's coming soon
        return redirect()->route('item')->with('info', 'PDF export feature is coming soon. Please use CSV export for now.');
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

        foreach ($items as $itemData) {
            $id = $itemData['id'];
            $type = $itemData['type'];
            
            if ($type === 'lost') {
                $model = LostItem::find($id);
                if (!$model) continue;
                
                if ($action === 'status' && in_array($value, ['open', 'matched', 'closed'])) {
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
                
                if ($action === 'status' && in_array($value, ['unclaimed', 'matched', 'returned'])) {
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

