<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendNotificationJob;
use Illuminate\Http\Request;

class InternalController extends Controller
{
    public function matchFound(Request $request)
    {
        // Basic guard using an internal key; replace as needed with signed routes
        abort_unless($request->header('X-Internal-Key') === config('app.key'), 403);

        $data = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'item_id' => 'required|integer',
            'score' => 'nullable|numeric',
            'title' => 'required|string',
            'body' => 'required|string',
        ]);

        SendNotificationJob::dispatch(
            (int) $data['user_id'],
            $data['title'],
            $data['body'],
            'match_found',
            (int) $data['item_id'],
            isset($data['score']) ? number_format((float) $data['score'], 2, '.', '') : null
        );

        return response()->json(['ok' => true]);
    }
}


