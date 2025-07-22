<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WebController;
use App\Http\Controllers\InmuebleImportController;
use App\Http\Controllers\ImportHistoryController;
use App\Http\Controllers\InmuebleController;
use App\Http\Controllers\ModulesController;

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

    // Health check
    // Route::get('/health', function () {
    //     return response()->json([
    //         'status' => 'healthy',
    //         'timestamp' => now()->toISOString(),
    //         'version' => '1.0.0'
    //     ]);
    // });

    // Authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->middleware('throttle:3,10');
        Route::post('/reset-password', [PasswordResetController::class, 'reset'])->middleware('throttle:3,10');
    });

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        // User management
        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index']);
            Route::get('/me', [UserController::class, 'me']);
            Route::get('/{user}', [UserController::class, 'show']);
            Route::put('/{user}', [UserController::class, 'update']);
            Route::delete('/{user}', [UserController::class, 'destroy']);
            Route::patch('/{user}/profile', [UserController::class, 'updateProfile']);
            Route::patch('/{user}/password', [UserController::class, 'updatePassword']);
        });

        // Roles, Permissions, Modules
        Route::get('/roles', function () {
            $roles = \Spatie\Permission\Models\Role::all(['id', 'name']);
            return response()->json(['roles' => $roles], 200);
        });
        Route::get('/permissions', function () {
            $permissions = \Spatie\Permission\Models\Permission::all(['id', 'name']);
            return response()->json(['permissions' => $permissions], 200);
        });
        Route::get('/modules', [\App\Http\Controllers\ModulesController::class, 'index']);
        Route::get('/user/roles', [\App\Http\Controllers\Auth\AuthController::class, 'roles']);
        Route::get('/user/permissions', [\App\Http\Controllers\Auth\AuthController::class, 'permissions']);

        // Inmuebles
        Route::apiResource('inmuebles', InmuebleController::class);
        Route::get('inmuebles/import/template', [InmuebleImportController::class, 'downloadTemplate'])->name('inmuebles.import.template');
        Route::post('inmuebles/import', [InmuebleImportController::class, 'import'])->name('inmuebles.import');

        // Activity logs
        Route::prefix('activity-logs')->group(function () {
            Route::get('/', [WebController::class, 'getActivityLogs']);
            Route::get('/{activityLog}', [WebController::class, 'getActivityLog']);
        });
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
