<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\InmuebleController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::prefix('v1')->group(function () {

    // Authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->middleware('throttle:3,10');
        Route::post('/reset-password', [PasswordResetController::class, 'reset'])->middleware('throttle:3,10');
    });

    // Protected routes
    Route::middleware(['auth:sanctum', 'active.user'])->group(function () {
        // User
        Route::get('/user', [AuthController::class, 'user'])->name('user');

        // User
        Route::patch('/users/{user}/profile', [UserController::class, 'updateProfile'])->name('users.updateProfile');
        Route::patch('/users/{user}/password', [UserController::class, 'updatePassword'])->name('users.updatePassword');
        Route::apiResource('users', UserController::class);

        // Inmuebles
        Route::apiResource('inmuebles', InmuebleController::class);
        Route::get('inmuebles/import/template', [InmuebleController::class, 'downloadTemplate'])->name('inmuebles.import.template');
        Route::post('inmuebles/import', [InmuebleController::class, 'import'])->name('inmuebles.import');
    });
});

// Fallback for undefined routes
Route::fallback(function () {
    return response()->json([
        'error' => [
            'code' => 'ENDPOINT_NOT_FOUND',
            'message' => 'The requested endpoint does not exist.',
            'documentation' => '/api/v1/docs'
        ],
        'timestamp' => now()->toISOString()
    ], 404);
});
