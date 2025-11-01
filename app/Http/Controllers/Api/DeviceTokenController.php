<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DeviceTokenController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'platform' => 'required|in:android,ios,web',
            'token' => 'required|string|max:2048', // FCM tokens can be long
        ]);

        $userId = $request->user()->id;

        try {
            // First, check if token exists for a different user
            $existing = DeviceToken::where('token', $data['token'])->first();
            
            if ($existing && $existing->user_id !== $userId) {
                // Token belongs to another user - delete old record and create new one
                $existing->delete();
            }

            // Update or create for current user
            DeviceToken::updateOrCreate(
                ['user_id' => $userId, 'token' => $data['token']],
                [
                    'platform' => $data['platform'],
                    'last_seen_at' => now(),
                ]
            );

            return response()->json(['ok' => true], 201);
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database constraint violations
            Log::error('Device token registration failed', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to register device token',
                'message' => 'Please try again later.',
            ], 500);
        }
    }

    public function destroy(Request $request)
    {
        $data = $request->validate([
            'token' => 'required|string',
        ]);

        DeviceToken::where('user_id', $request->user()->id)
            ->where('token', $data['token'])
            ->delete();

        return response()->noContent();
    }
}


