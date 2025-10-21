<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RecommendationController;
use App\Http\Controllers\Api\MeController;
use App\Http\Controllers\CommentController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\ApiAuthMiddleware;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public item browse endpoints
Route::get('/items', [ItemController::class, 'index']);
Route::get('/items/{id}', [ItemController::class, 'show']);
Route::get('/items/recommended', [RecommendationController::class, 'index']);

// Protected routes (require Sanctum token)
Route::middleware(['auth:sanctum', ApiAuthMiddleware::class])->group(function () {
    Route::get('/user', [AuthController::class, 'userProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::prefix('me')->group(function () {
        Route::get('/', [ProfileController::class, 'index']);
        Route::get('/items', [MeController::class, 'items']);
    });

    // All routes starts from /comments goes here
    Route::prefix('comments')->group(function () {
        Route::post('/', [CommentController::class, 'store']);
        Route::get('/', [CommentController::class, 'index']); // We can pass itemId as a query parameter
    });

    // Item routes (mutations)
    Route::prefix('items')->group(function () {
        Route::post('/', [ItemController::class, 'store']); // Create a new item
        Route::put('/{id}', [ItemController::class, 'update']); // Update a specific item
        Route::delete('/{id}', [ItemController::class, 'destroy']); // Delete a specific item (owner-only)
        Route::get('/{id}/matches', [ItemController::class, 'matchesItems']);

        // AI Feedback
        Route::post('/ai/feedback', [ItemController::class, 'aiFeedback']);

        // AI Batch match (admin/staff)
        Route::post('/ai/batch-match', [ItemController::class, 'batchMatch']);
    });
});
