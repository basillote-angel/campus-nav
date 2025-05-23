<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{  /**
     * Get comments for a specific item
     */
    public function index(Item $item)
    {
        // Eager load user data with each comment
        $comments = $item->comments()->with('user')->latest()->get();
        return response()->json($comments);
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

    /**
     * Show a single comment
     */
    public function show(Request $request)
    {
       $itemId = $request->query('itemId');

    $comments = Comment::where('item_id', $itemId)
                ->orderBy('created_at', 'desc')
                ->get();

    return response()->json($comments); // 👈 just a list, no wrapping
}
    }
    