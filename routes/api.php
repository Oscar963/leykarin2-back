<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\InmuebleController;

Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Rutas de Autenticación Públicas
    |--------------------------------------------------------------------------
    */
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
        Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->middleware('throttle:3,10');
        Route::post('/reset-password', [PasswordResetController::class, 'reset'])->middleware('throttle:3,10');
    });

    /*
    |--------------------------------------------------------------------------
    | Rutas Protegidas
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:sanctum', 'active.user'])->group(function () {

        // --- Autenticación (para usuario logueado) ---
        Route::prefix('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
            Route::get('/user', [AuthController::class, 'user'])->name('auth.user');
        });

        // --- Gestión de Usuarios ---
        Route::patch('/users/{user}/profile', [UserController::class, 'updateProfile'])->name('users.updateProfile');
        Route::patch('/users/{user}/password', [UserController::class, 'updatePassword'])->name('users.updatePassword');
        Route::apiResource('users', UserController::class);

        // --- Gestión de Inmuebles ---
        Route::get('inmuebles/export', [InmuebleController::class, 'export'])->name('inmuebles.export');
        Route::get('inmuebles/import/template', [InmuebleController::class, 'downloadTemplate'])->name('inmuebles.import.template');
        Route::post('inmuebles/import', [InmuebleController::class, 'import'])->name('inmuebles.import');
        Route::apiResource('inmuebles', InmuebleController::class);
    });
});

// Fallback para rutas no definidas
Route::fallback(function () {
    return response()->json([
        'error' => [
            'code' => 'ENDPOINT_NOT_FOUND',
            'message' => 'The requested endpoint does not exist.',
        ],
    ], 404);
});
