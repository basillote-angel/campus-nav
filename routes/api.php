<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\ItemController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\ApiAuthMiddleware;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware(['auth:sanctum', ApiAuthMiddleware::class])->group(function () {
    Route::get('/user', [AuthController::class, 'userProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Item routes
    Route::prefix('items')->group(function () {
        Route::get('/', [ItemController::class, 'index']); // List all items
        Route::post('/', [ItemController::class, 'store']); // Create a new item
        Route::get('/{id}', [ItemController::class, 'show']); // Get a specific item
        Route::put('/{id}', [ItemController::class, 'update']); // Update a specific item
        Route::delete('/{id}', [ItemController::class, 'destroy']); // Delete a specific item
    });
});

Route::get('/debug', function () {
    return response()->json(['message' => 'API is working!'], 200);
});