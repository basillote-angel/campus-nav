<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ItemController extends Controller
{
    public function index(Request $request) {
        $query = Item::query();

        // Apply filters
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->search . '%')
                ->orWhere('description', 'LIKE', '%' . $request->search . '%')
                ->orWhere('location', 'LIKE', '%' . $request->search . '%');
            });
        }

        // Paginate results
        $items = $query->paginate(10);

        // If AJAX request, return only the table and pagination
        if ($request->ajax()) {
            return response()->view('components.item-table', compact('items'));
        }

        // For normal page load, render full view
        return view('item', compact('items'));
    }

    public function edit($id) {
        $item = Item::findOrFail($id);

        return view('edit-item', compact('item'));
    }
    
    /**
     * Update the specified item in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string',
            'category' => 'required|string|in:electronics,documents,accessories,idOrCards,clothing,bagOrPouches,personalItems,schoolSupplies,others',
            'description' => 'required|string',
            'type' => 'required|in:lost,found',
            'location' => 'nullable|string',
            'lost_found_date' => 'nullable|date',
        ]);

        $item = Item::findOrFail($id);

        $data = $request->only([
            'name',
            'category',
            'description',
            'type',
            'location',
            'lost_found_date',
        ]);

        $item->update($data);

        return redirect()->route('item');
    }

    /**
     * Remove the specified item from storage.
     */
    public function destroy($id)
    {
        $item = Item::findOrFail($id);
        
        $item->delete();

        session()->flash('success', 'Item deleted successfully.');
        return redirect()->route('item');
    }

    public function store(Request $request) {
        $request->validate([
            'name' => 'required|string',
            'category' => 'required|string|in:electronics,documents,accessories,idOrCards,clothing,bagOrPouches,personalItems,schoolSupplies,others',
            'description' => 'required|string',
            'type' => 'required|in:lost,found',
            'location' => 'nullable|string',
            'lost_found_date' => 'nullable|date',
        ]);

        $userId = Auth::id();

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

        $url = null;
        
        $item = Item::create(array_merge($request->all(), [
            'lost_found_date' => $formattedDate
        ]));

        if ($item) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }
}

