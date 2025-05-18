<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ItemController extends Controller
{
    public function index()
    {
        return response()->json(Item::all(), 200);
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string',
                'description' => 'required|string',
                'type' => 'required|in:lost,found',
                'location' => 'nullable|string',
                'lost_found_date' => 'nullable|date',
                'contact_info' => 'nullable|string',
                'image' => 'required|file|max:5120|mimes:jpg,jpeg,png'
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

            $path = $request->file('image')->store('uploads', 'public');
            $url = Storage::url($path);

            $item = Item::create(array_merge($request->all(), [
                'image_url' => $url,
                'lost_found_date' => $formattedDate
            ]));
            return response()->json($item, 201);
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

    public function update(Request $request, $id)
    {
        try {
            $item = Item::find($id);

            if (!$item) {
                return response()->json(['message' => 'Item not found'], 404);
            }

            $item->update($request->only(['name', 'description', 'status', 'location', 'date_found', 'owner_name', 'owner_contact']));
            
            return response()->json($item, 200);;
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

            $item->delete();
            return response()->json(['message' => 'Item deleted successfully'], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update item', 'message' => $e->getMessage()], 500);
        }
    }
}
