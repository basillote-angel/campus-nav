<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{   
    /**
     * Get comments for a specific item
     * @queryParam itemId
     */
    public function index(Request $request)
    {
        $itemId = $request->query('item_id');

        $query = Comment::with('user')->orderBy('created_at', 'desc');

        // If itemId is provided, filter comments by item
        if ($itemId) {
            $query->where('item_id', $itemId);
        }

        $comments = $query->get();

        return response()->json($comments, 200);
    }

    /**
     * Store a new comment
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'comment' => 'required|string|max:1000',
        ]);

        $comment = Comment::create([
            'item_id' => $validated['item_id'],
            'user_id' => Auth::id(),
            'comment' => $validated['comment'],
        ]);

        return response()->json($comment->load('user'), 201);
    }
}
    