<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebAuthController;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [WebAuthController::class, 'login']);
Route::get('/register', [WebAuthController::class, 'showRegister'])->name('register');
Route::post('/register', [WebAuthController::class, 'register']);
Route::get('/home', [WebAuthController::class, 'home'])->middleware('auth');
Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');