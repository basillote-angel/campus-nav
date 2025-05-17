<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CampusMapController;
use App\Http\Controllers\WebItemController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [WebAuthController::class, 'login']);

    Route::get('/register', [WebAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [WebAuthController::class, 'register']);
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Campus Map
    Route::get('/campus-map', [CampusMapController::class, 'index'])->name('campus-map');
    Route::post('/campus-map/update', [CampusMapController::class, 'update'])->name('campus-map.update');

    // Lost and found item
    Route::get('/item', [WebItemController::class, 'index'])->name('item');
    Route::post('/items', [WebItemController::class, 'store'])->name('item.store');
    
    Route::get('/items/{id}/edit', [WebItemController::class, 'edit'])->name('items.edit');
    Route::put('/items/{id}', [WebItemController::class, 'update'])->name('items.update');
    
    Route::delete('/items/{id}', [WebItemController::class, 'destroy'])->name('items.destroy');

    // Users
    Route::get('/manage-users', [UserController::class, 'showManageUsers'])->name('manage-users');
    Route::post('/item/user', [UserController::class, 'update'])->name('item.update');

    // Profile
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');

    // Logout
    Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');
});
