<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Services\AIService;
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

            $item = Item::create(array_merge($request->all(), [
                'user_id' => Auth::id(),
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

            $item->update($request->only(['name', 'category', 'description', 'type', 'location', 'lost_found_date']));
            
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
}
