<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AIService;

class AIController extends Controller
{
    /**
     * GET /api/ai/health
     */
    public function health(AIService $aiService)
    {
        $result = $aiService->health();
        $status = ($result['ok'] ?? false) ? 200 : 503;
        return response()->json($result, $status);
    }
}


