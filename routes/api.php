<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\CommentController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\ApiAuthMiddleware;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware(['auth:sanctum', ApiAuthMiddleware::class])->group(function () {
    Route::get('/user', [AuthController::class, 'userProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    Route::prefix('me')->group(function () {
        Route::get('/', [ProfileController::class, 'index']);
        Route::get('/items', [ProfileController::class, 'postedItems']);
    });

    // All routes starts from /comments goes here
    Route::prefix('comments')->group(function () {
        Route::post('/', [CommentController::class, 'store']);
        Route::get('/', [CommentController::class, 'index']); // We can pass itemId as a query parameter
    });

    // Item routes
    Route::prefix('items')->group(function () {
        Route::get('/', [ItemController::class, 'index']); // List all items
        Route::post('/', [ItemController::class, 'store']); // Create a new item
        Route::get('/{id}', [ItemController::class, 'show']); // Get a specific item
        Route::put('/{id}', [ItemController::class, 'update']); // Update a specific item
        Route::delete('/{id}', [ItemController::class, 'destroy']); // Delete a specific item
   });  
});
