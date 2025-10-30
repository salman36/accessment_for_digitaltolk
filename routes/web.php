<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TranslationController;

// API routes (prefixed) since routes/api.php is not present in this skeleton
Route::prefix('api')->group(function () {
	// Auth
	Route::post('/auth/register', [AuthController::class, 'register']);
	Route::post('/auth/login', [AuthController::class, 'login']);

	Route::middleware('auth:api')->group(function () {
		Route::get('/auth/me', [AuthController::class, 'me']);
		Route::post('/auth/logout', [AuthController::class, 'logout']);

		// Translations CRUD & search
		Route::get('/translations', [TranslationController::class, 'index']);
		Route::post('/translations', [TranslationController::class, 'store']);
		Route::get('/translations/{translation}', [TranslationController::class, 'show']);
		Route::match(['put','patch'],'/translations/{translation}', [TranslationController::class, 'update']);
		Route::delete('/translations/{translation}', [TranslationController::class, 'destroy']);
	});

	// Public export endpoint (can be protected later if required or signed)
	Route::get('/translations/export', [TranslationController::class, 'export']);
});
