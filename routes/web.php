<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Public routes
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
})->name('home');

// Authentication routes
require __DIR__.'/auth.php';

// Protected routes (require authentication)
Route::middleware(['auth', 'verified'])->group(function () {
    // Posts (only accessible after login)
    Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
    // JSON data endpoint used by the Vue frontend (axios)
    Route::get('/posts/data', [PostController::class, 'data'])->name('posts.data');
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::match(['put', 'patch'], '/posts/{post}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');

    // Dashboard
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});