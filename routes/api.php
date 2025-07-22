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
    Route::get('/health', function () {
        return response()->json([
            'status' => 'healthy',
            'timestamp' => now()->toISOString(),
            'version' => '1.0.0'
        ]);
    });

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

        // Inmuebles (Properties) - RESTful CRUD
        Route::prefix('inmuebles')->group(function () {
            // CRUD operations (RESTful)
            Route::apiResource('inmuebles', InmuebleController::class);

            // Search and filters
            Route::get('/search', [InmuebleController::class, 'search']);
            Route::get('/filter', [InmuebleController::class, 'filter']);

            // Statistics
            Route::get('/statistics', [InmuebleController::class, 'statistics']);

            // Import/Export operations
            Route::prefix('import')->group(function () {
                Route::get('/template', [InmuebleImportController::class, 'downloadTemplate']);
                Route::get('/column-mapping', [InmuebleImportController::class, 'getColumnMapping']);
                Route::post('/preview', [InmuebleImportController::class, 'preview']);
                Route::post('/', [InmuebleImportController::class, 'import']);
                Route::get('/statistics', [InmuebleImportController::class, 'getImportStatistics']);
                Route::delete('/{importId}', [InmuebleImportController::class, 'cancelImport']);
            });

            // Import History
            Route::prefix('import-history')->group(function () {
                Route::get('/', [ImportHistoryController::class, 'index']);
                Route::get('/statistics', [ImportHistoryController::class, 'statistics']);
                Route::get('/recent-summary', [ImportHistoryController::class, 'recentSummary']);
                Route::get('/{importId}', [ImportHistoryController::class, 'show']);
                Route::get('/{importId}/versions', [ImportHistoryController::class, 'versionHistory']);
                Route::post('/{importId}/versions', [ImportHistoryController::class, 'createVersion']);
                Route::post('/{importId}/rollback', [ImportHistoryController::class, 'rollback']);
                Route::post('/export', [ImportHistoryController::class, 'export']);
                Route::delete('/{importId}', [ImportHistoryController::class, 'destroy']);
            });

            Route::prefix('export')->group(function () {
                Route::get('/', [InmuebleController::class, 'export']);
                Route::post('/custom', [InmuebleController::class, 'customExport']);
            });
        });

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
