<?php

use App\Http\Controllers\ActivityLogController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\GoogleLoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\WebController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\FileController;

Route::prefix('v1')->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Rutas Web
    |--------------------------------------------------------------------------
    */
    Route::get('/form-data', [WebController::class, 'getFormData'])->name('web.form-data');
    Route::post('web/complaints', [WebController::class, 'storeComplaint'])
        ->middleware('throttle:5,60') // 5 denuncias por hora por IP
        ->name('web.complaints.store');

    // --- Gestión de Archivos Temporales (FilePond) ---
    Route::prefix('temporary-files')->group(function () {
        Route::post('/', [FileController::class, 'uploadTemporary'])->name('temporary-files.upload');
        Route::get('/', [FileController::class, 'getTemporaryFiles'])->name('temporary-files.index');
        Route::post('/delete/{temporaryFile}', [FileController::class, 'deleteTemporary'])->name('temporary-files.delete');
    });

    /*
    |--------------------------------------------------------------------------
    | Rutas de Autenticación Públicas
    |--------------------------------------------------------------------------
    */

    // Rutas de Google OAuth (con middleware web para sesiones)
    Route::prefix('auth')->middleware(['web'])->group(function () {
        Route::post('/google/login', [GoogleLoginController::class, 'login'])->name('auth.google.login');
        Route::get('/google/config', [GoogleLoginController::class, 'config'])->name('auth.google.config');
    });

    // Rutas de autenticación tradicional (con middleware web y CSRF)
    Route::prefix('auth')->middleware(['web'])->group(function () {
        Route::post('/login', [AuthController::class, 'login'])->name('login');
        Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum')->name('logout');
        Route::post('/two-factor-challenge', [AuthController::class, 'twoFactorChallenge'])->name('two-factor.login');
        Route::post('/two-factor-resend', [AuthController::class, 'resendTwoFactorCode'])->name('two-factor.resend');

        // Rutas de recuperación de contraseña
        Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
        Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.reset');
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
            
            // Endpoint de prueba de autenticación
            Route::get('/test', function () {
                return response()->json([
                    'authenticated' => true,
                    'user' => auth()->user(),
                    'token' => request()->bearerToken() ? 'Token presente' : 'Sin token',
                ]);
            })->name('auth.test');

            // Gestión de 2FA por email
            Route::get('/two-factor-status', [AuthController::class, 'getTwoFactorStatus'])->name('two-factor.status');
            Route::post('/two-factor-enable', [AuthController::class, 'enableTwoFactor'])->name('two-factor.enable');
            Route::post('/two-factor-disable', [AuthController::class, 'disableTwoFactor'])->name('two-factor.disable');

            // Gestión de Google OAuth (rutas protegidas)
            Route::get('/google/status', [GoogleLoginController::class, 'status'])->name('auth.google.status');
            Route::post('/google/link', [GoogleLoginController::class, 'link'])->name('auth.google.link');
            Route::post('/google/unlink', [GoogleLoginController::class, 'unlink'])->name('auth.google.unlink');
        });

        Route::prefix('profile')->group(function () {
            Route::post('/update', [ProfileController::class, 'updateProfile'])->name('profile.update');
            Route::post('/change-password', [ProfileController::class, 'changePassword'])->name('profile.change-password');
        });

        // --- Gestión de Usuarios ---
        Route::middleware(['permission:users.manage'])->group(function () {
            Route::apiResource('users', UserController::class);
            Route::post('users/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        });

        // --- Gestión de Denuncias ---
        Route::prefix('complaints')->group(function () {
            Route::get('/', [ComplaintController::class, 'index'])->name('complaints.index');
            Route::post('/', [ComplaintController::class, 'store'])->name('complaints.store');
            Route::get('/{complaint}', [ComplaintController::class, 'show'])->name('complaints.show');
            Route::patch('/{complaint}', [ComplaintController::class, 'update'])->name('complaints.update');
            Route::delete('/{complaint}', [ComplaintController::class, 'destroy'])->name('complaints.destroy');
            
            Route::get('/download-pdf/{token}', [ComplaintController::class, 'downloadPdf'])
                ->middleware('throttle:10,1')
                ->name('complaints.download-pdf');
            
            Route::post('/resend-receipt', [ComplaintController::class, 'resendReceipt'])
                ->middleware('throttle:3,1')
                ->name('complaints.resend-receipt');
        });

        // --- Gestión de Roles ---
        Route::middleware(['permission:roles.manage'])->group(function () {
            Route::apiResource('roles', RoleController::class);
        });

        // --- Gestión de Permisos ---
        Route::middleware(['permission:permissions.manage'])->group(function () {
            Route::apiResource('permissions', PermissionController::class);
        });

        // --- Gestión de Archivos ---
        Route::middleware(['permission:complaints.manage_files'])->group(function () {
            Route::apiResource('files', FileController::class);
            Route::get('/files/{id}/download', [FileController::class, 'download']);
        });

        // --- Gestión de Logs ---
        Route::middleware(['permission:activity_logs.list'])->group(function () {
            Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
        });

        // --- Gestión de Archivos de Denuncias ---
        Route::prefix('complaints/{complaint}')->group(function () {
            Route::get('/files', [FileController::class, 'getComplaintFiles'])
                ->middleware('permission:complaints.view')
                ->name('complaints.files.index');
        });

        Route::prefix('files')->middleware(['permission:complaints.manage_files'])->group(function () {
            Route::delete('/{file}', [FileController::class, 'deleteFile'])->name('files.delete');
            Route::get('/{file}/download', [FileController::class, 'downloadFile'])->name('files.download');
        });
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
