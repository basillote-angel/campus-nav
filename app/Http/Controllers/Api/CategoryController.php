<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    /**
     * Get all categories for mobile app
     * Returns JSON list of categories with id and name
     */
    public function index(Request $request)
    {
        try {
            $categories = Cache::remember('api.categories.list', now()->addHour(), function () {
                return Category::orderBy('name')
                    ->get(['id', 'name', 'description']);
            });

            return response()->json($categories, 200);
        } catch (\Exception $e) {
            \Log::error('CATEGORY_INDEX_ERROR', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'error' => 'Failed to fetch categories',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}


