<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RecommendationController;
use App\Http\Controllers\Api\DeviceTokenController as ApiDeviceTokenController;
use App\Http\Controllers\Api\NotificationController as ApiNotificationController;
use App\Http\Controllers\Api\InternalController as ApiInternalController;
use App\Http\Controllers\Api\MeController;
use App\Http\Controllers\Api\AIController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\ApiAuthMiddleware;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/auth/google', [AuthController::class, 'googleSignIn']);
Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);

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
		
		// Claim routes
		Route::post('/{id}/claim', [ItemController::class, 'claim']); // Submit a claim for a found item

		// AI Feedback
		Route::post('/ai/feedback', [ItemController::class, 'aiFeedback']);

		// AI Batch match (admin/staff)
		Route::post('/ai/batch-match', [ItemController::class, 'batchMatch']);
	});

	// Device tokens
	Route::post('/device-tokens', [ApiDeviceTokenController::class, 'store']);
	Route::delete('/device-tokens', [ApiDeviceTokenController::class, 'destroy']);

	// Notifications
	Route::get('/notifications', [ApiNotificationController::class, 'index']);
	Route::post('/notifications/{id}/read', [ApiNotificationController::class, 'markRead']);

	// Admin test send (optional)
	Route::post('/notifications/test-send/{user}', function (\Illuminate\Http\Request $request, \App\Models\User $user) {
		abort_unless($request->user()->role === 'admin', 403);
		\App\Jobs\SendNotificationJob::dispatch(
			$user->id,
			'Message from Admin',
			'Please bring your ID...',
			'admin_message',
			null,
			null
		);
		return response()->json(['ok' => true]);
	});
});

// Internal webhook
Route::post('/internal/match-found', [ApiInternalController::class, 'matchFound'])->name('internal.match-found');
