<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CampusMapController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\CategoryController;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Controllers\Admin\MatchQueueController;
use App\Http\Controllers\Admin\ClaimsController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::middleware([RoleMiddleware::class . ':admin'])->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{id}/edit', [UserController::class, 'editView'])->name('users.edit-view'); // Edit view
        Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
        
        // Notifications routes for admin
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
        Route::post('/notifications/{id}/approve', [NotificationController::class, 'approve'])->name('notifications.approve');
        Route::post('/notifications/{id}/reject', [NotificationController::class, 'reject'])->name('notifications.reject');
    });

    Route::middleware([RoleMiddleware::class . ':admin,staff'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/campus-map', [CampusMapController::class, 'index'])->name('campus-map');

        Route::get('/item', [ItemController::class, 'index'])->name('item');
        Route::post('/items', [ItemController::class, 'store'])->name('item.store');
        Route::get('/items/{id}/edit', [ItemController::class, 'edit'])->name('items.edit');
        Route::put('/items/{id}', [ItemController::class, 'update'])->name('items.update');
        Route::delete('/items/{id}', [ItemController::class, 'destroy'])->name('items.destroy');

        Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
        Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    });

    // Admin matches queue (admin + staff)
    Route::middleware([RoleMiddleware::class . ':admin,staff'])->group(function () {
        Route::get('/admin/matches', [MatchQueueController::class, 'index'])->name('admin.matches.index');
        Route::get('/admin/claims', [ClaimsController::class, 'index'])->name('admin.claims.index');
        Route::post('/admin/claims/{id}/approve', [ClaimsController::class, 'approve'])->name('admin.claims.approve');
        Route::post('/admin/claims/{id}/reject', [ClaimsController::class, 'reject'])->name('admin.claims.reject');
    });
});
