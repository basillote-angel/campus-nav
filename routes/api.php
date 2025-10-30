<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RecommendationController;
use App\Http\Controllers\Api\MeController;
use App\Http\Controllers\Api\AIController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\ApiAuthMiddleware;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public item browse endpoints
Route::get('/items', [ItemController::class, 'index']);
Route::get('/items/{id}', [ItemController::class, 'show'])->whereNumber('id');
Route::get('/ai/health', [AIController::class, 'health']);

// Protected routes (require Sanctum token)
Route::middleware(['auth:sanctum', ApiAuthMiddleware::class])->group(function () {
	Route::get('/user', [AuthController::class, 'userProfile']);
	Route::post('/logout', [AuthController::class, 'logout']);

	// Personalized recommendations (requires auth)
	Route::get('/items/recommended', [RecommendationController::class, 'index']);

	Route::prefix('me')->group(function () {
		Route::get('/', [ProfileController::class, 'index']);
		Route::get('/items', [MeController::class, 'items']);
	});

	// Item routes (mutations)
	Route::prefix('items')->group(function () {
		Route::post('/', [ItemController::class, 'store']); // Create a new item
		Route::put('/{id}', [ItemController::class, 'update']); // Update a specific item
		Route::delete('/{id}', [ItemController::class, 'destroy']); // Delete a specific item (owner-only)
		Route::get('/{id}/matches', [ItemController::class, 'matchesItems']);
			Route::post('/{id}/compute-matches', [ItemController::class, 'computeMatches']);

		// AI Feedback
		Route::post('/ai/feedback', [ItemController::class, 'aiFeedback']);

		// AI Batch match (admin/staff)
		Route::post('/ai/batch-match', [ItemController::class, 'batchMatch']);
	});
});
