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

class ItemController extends Controller
{
    public function index(Request $request) {
        $type = $request->query('type', ''); // default to all types

        if ($type === 'lost') {
            $query = LostItem::query();

            if ($request->filled('status')) {
                $query->where('status', $request->status); // open|matched|closed
            }

            if ($request->filled('search')) {
                $kw = '%' . $request->search . '%';
                $query->where(function ($q) use ($kw) {
                    $q->where('title', 'LIKE', $kw)
                      ->orWhere('description', 'LIKE', $kw)
                      ->orWhere('location', 'LIKE', $kw);
                });
            }

            $items = $query->with('category')->latest('created_at')->paginate(10);

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
                    'created_at' => $i->created_at,
                    'updated_at' => $i->updated_at,
                ];
            });
        } else if ($type === 'found') {
            $query = FoundItem::query();

            if ($request->filled('status')) {
                $query->where('status', $request->status); // unclaimed|matched|returned
            }

            if ($request->filled('search')) {
                $kw = '%' . $request->search . '%';
                $query->where(function ($q) use ($kw) {
                    $q->where('title', 'LIKE', $kw)
                      ->orWhere('description', 'LIKE', $kw)
                      ->orWhere('location', 'LIKE', $kw);
                });
            }

            $items = $query->with('category')->latest('created_at')->paginate(10);

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
                    'created_at' => $i->created_at,
                    'updated_at' => $i->updated_at,
                ];
            });
        } else {
            // All types: merge lost and found, sort by latest created_at
            $lostQuery = LostItem::query();
            $foundQuery = FoundItem::query();

            if ($request->filled('search')) {
                $kw = '%' . $request->search . '%';
                $applySearch = function ($q) use ($kw) {
                    $q->where(function ($qq) use ($kw) {
                        $qq->where('title', 'LIKE', $kw)
                           ->orWhere('description', 'LIKE', $kw)
                           ->orWhere('location', 'LIKE', $kw);
                    });
                };
                $applySearch($lostQuery);
                $applySearch($foundQuery);
            }

            $lostItems = $lostQuery->with('category')->get();
            $foundItems = $foundQuery->with('category')->get();

            $lostMapped = $lostItems->map(function ($i) {
                return [
                    'id' => $i->id,
                    'name' => $i->title,
                    'description' => $i->description,
                    'location' => $i->location,
                    'status' => $i->status,
                    'type' => 'lost',
                    'lost_found_date' => $i->date_lost,
                    'category' => optional($i->category)->name,
                    'created_at' => $i->created_at,
                    'updated_at' => $i->updated_at,
                ];
            });

            $foundMapped = $foundItems->map(function ($i) {
                return [
                    'id' => $i->id,
                    'name' => $i->title,
                    'description' => $i->description,
                    'location' => $i->location,
                    'status' => $i->status,
                    'type' => 'found',
                    'lost_found_date' => $i->date_found,
                    'category' => optional($i->category)->name,
                    'created_at' => $i->created_at,
                    'updated_at' => $i->updated_at,
                ];
            });

            $all = $lostMapped->concat($foundMapped);
            $sorted = $all->sortByDesc(function ($i) { return $i['created_at']; })->values();

            $perPage = 10;
            $page = LengthAwarePaginator::resolveCurrentPage();
            $currentPageItems = $sorted->slice(($page - 1) * $perPage, $perPage)->values();
            $items = new LengthAwarePaginator(
                $currentPageItems,
                $sorted->count(),
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        }

        if ($request->ajax()) {
            return response()->view('components.item-table', compact('items'));
        }

        return view('item', compact('items'));
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
        ];
        $categories = Category::orderBy('name')->get(['id','name']);
        $selectedCategoryId = $model->category_id;
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
        $model->update($payload);
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
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Create failed']);
    }
}

