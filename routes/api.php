<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TranslationController;

// Auth (rate limited)
Route::middleware('throttle:10,1')->group(function () {
	Route::post('/auth/register', [AuthController::class, 'register']);
	Route::post('/auth/login', [AuthController::class, 'login']);
});

// Protected routes (JWT) with rate limiting
Route::middleware(['auth:api', 'throttle:60,1'])->group(function () {
	Route::get('/auth/me', [AuthController::class, 'me']);
	Route::post('/auth/logout', [AuthController::class, 'logout']);

	// Translations CRUD & search
	Route::get('/translations', [TranslationController::class, 'index']);
	Route::post('/translations', [TranslationController::class, 'store'])->middleware('throttle:30,1');
	Route::get('/translations/{translation}', [TranslationController::class, 'show']);
	Route::match(['put','patch'],'/translations/{translation}', [TranslationController::class, 'update'])->middleware('throttle:30,1');
	Route::delete('/translations/{translation}', [TranslationController::class, 'destroy'])->middleware('throttle:30,1');
});

// Public export endpoint for CDN usage (read-optimized rate limit)
Route::middleware('throttle:120,1')->get('/translations/export', [TranslationController::class, 'export']);


