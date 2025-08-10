<?php

use App\Http\Controllers\ActivityLogController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\InmuebleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;

Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Rutas de Autenticación Públicas
    |--------------------------------------------------------------------------
    */
    Route::prefix('auth')->group(function () {
        // Las rutas de Clave Única deben ir en routes/web.php para usar sesiones (Socialite)
        // Fortify expone /api/v1/auth/login, /api/v1/auth/logout, /api/v1/auth/forgot-password y /api/v1/auth/reset-password.
        // Evitamos duplicidad de rutas definiéndolas solo desde Fortify.
    });

    /*
    |--------------------------------------------------------------------------
    | Rutas Protegidas
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:sanctum', 'active.user'])->group(function () {
        // --- Autenticación (para usuario logueado) ---
        Route::prefix('auth')->group(function () {
            Route::get('/user', [AuthController::class, 'user'])->name('auth.user');
        });

        Route::prefix('profile')->group(function () {
            Route::post('/update', [ProfileController::class, 'updateProfile'])->name('profile.update');
            Route::post('/change-password', [ProfileController::class, 'changePassword'])->name('profile.change-password');
        });

        // --- Gestión de Usuarios ---
        Route::apiResource('users', UserController::class);
        Route::post('users/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');

        // --- Gestión de Inmuebles ---
        Route::get('inmuebles/export', [InmuebleController::class, 'export'])->name('inmuebles.export');
        Route::get('inmuebles/import/template', [InmuebleController::class, 'downloadTemplate'])->name('inmuebles.import.template');
        Route::post('inmuebles/import', [InmuebleController::class, 'import'])->name('inmuebles.import');
        Route::apiResource('inmuebles', InmuebleController::class);

        // --- Gestión de Roles ---
        Route::apiResource('roles', RoleController::class);

        // --- Gestión de Permisos ---
        Route::apiResource('permissions', PermissionController::class);

        // --- Gestión de Logs ---
        Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
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
