<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CampusMapController;
use App\Http\Controllers\ItemController;
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
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::middleware([RoleMiddleware::class . ':admin'])->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{id}/edit', [UserController::class, 'editView'])->name('users.edit-view'); // Edit view
        Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
        
        // Redirect old notifications route to claims management
        Route::get('/notifications', function() {
            return redirect()->route('admin.claims.index');
        })->name('notifications');
    });

    Route::middleware([RoleMiddleware::class . ':admin,staff'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/data', [DashboardController::class, 'getDashboardData'])->name('dashboard.data');
        Route::get('/dashboard/chart-data', [DashboardController::class, 'getChartData'])->name('dashboard.chartData');
        Route::get('/dashboard/export', [DashboardController::class, 'exportAnalytics'])->name('dashboard.export');
        Route::post('/dashboard/refresh', [DashboardController::class, 'refresh'])->name('dashboard.refresh');

        Route::get('/campus-map', [CampusMapController::class, 'index'])->name('campus-map');

        Route::get('/item', [ItemController::class, 'index'])->name('item');
        Route::get('/items/export', [ItemController::class, 'export'])->name('items.export');
        Route::post('/items/bulk-update', [ItemController::class, 'bulkUpdate'])->name('items.bulkUpdate');
        Route::post('/items/bulk-delete', [ItemController::class, 'bulkDelete'])->name('items.bulkDelete');
        Route::post('/items', [ItemController::class, 'store'])->name('item.store');
        Route::get('/items/{id}/edit', [ItemController::class, 'edit'])->name('items.edit');
        Route::put('/items/{id}', [ItemController::class, 'update'])->name('items.update');
        Route::delete('/items/{id}', [ItemController::class, 'destroy'])->name('items.destroy');

        Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
        Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
        Route::post('/profile/change-password', [ProfileController::class, 'changePassword'])->name('profile.changePassword');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        
        // Categories CRUD
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::get('/categories/{id}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::put('/categories/{id}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    });

    // Admin matches queue (admin + staff)
    Route::middleware([RoleMiddleware::class . ':admin,staff'])->group(function () {
        Route::get('/admin/matches', [MatchQueueController::class, 'index'])->name('admin.matches.index');
        Route::post('/admin/matches/refresh', [MatchQueueController::class, 'refresh'])->name('admin.matches.refresh');
        Route::get('/admin/claims', [ClaimsController::class, 'index'])->name('admin.claims.index');
        Route::post('/admin/claims/{id}/approve', [ClaimsController::class, 'approve'])->name('admin.claims.approve');
        Route::post('/admin/claims/{id}/reject', [ClaimsController::class, 'reject'])->name('admin.claims.reject');
        Route::post('/admin/claims/{id}/mark-collected', [ClaimsController::class, 'markCollected'])->name('admin.claims.markCollected');
    });
});
