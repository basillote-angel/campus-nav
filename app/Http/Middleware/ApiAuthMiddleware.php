<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($response->status() === 401) {
            return response()->json([
                'message' => 'Unauthorized: Token is missing or expired.',
                'status' => 401
            ], 401);
        }

        if ($response->status() === 403) {
            return response()->json([
                'message' => 'Forbidden: Access Denied.',
                'status' => 403
            ], 403);
        }

        return $response;
    }
}
