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
use App\Http\Controllers\WebController;
use App\Http\Controllers\FileController;

Route::prefix('v1')->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Rutas Web
    |--------------------------------------------------------------------------
    */
    Route::get('/form-data', [WebController::class, 'getFormData'])->name('web.form-data');
    Route::post('/complaints', [WebController::class, 'storeComplaint'])->name('complaints.store');
    
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

            // Gestión de 2FA por email
            Route::get('/two-factor-status', [AuthController::class, 'getTwoFactorStatus'])->name('two-factor.status');
            Route::post('/two-factor-enable', [AuthController::class, 'enableTwoFactor'])->name('two-factor.enable');
            Route::post('/two-factor-disable', [AuthController::class, 'disableTwoFactor'])->name('two-factor.disable');
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

        // --- Gestión de Archivos ---
        Route::prefix('complaints/{complaint}')->group(function () {
            Route::post('/files/evidence', [FileController::class, 'uploadEvidence'])->name('complaints.files.evidence');
            Route::post('/files/signature', [FileController::class, 'uploadSignature'])->name('complaints.files.signature');
            Route::get('/files', [FileController::class, 'getComplaintFiles'])->name('complaints.files.index');
        });

        Route::prefix('files')->group(function () {
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
