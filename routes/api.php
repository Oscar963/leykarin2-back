<?php

use App\Http\Controllers\AnexoController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\MobileController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PopupController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WebController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::get('/isAuthenticated', [AuthController::class, 'isAuthenticated']);

    Route::apiResource('banners', BannerController::class);
    Route::apiResource('popups', PopupController::class);

    Route::post('/pages/files', [PageController::class, 'uploadFile']);
    Route::get('/pages/files', [PageController::class, 'indexFile']);
    Route::apiResource('pages', PageController::class);

    Route::post('/users/reset-password/{id}', [UserController::class, 'resetPassword'])->name('users.reset-password');
    Route::post('/users/update-password', [UserController::class, 'updatePassword'])->name('users.reset-update');
    Route::post('/users/update-profile', [UserController::class, 'updateProfile'])->name('users.update-profile');
    Route::get('/users/profile', [UserController::class, 'profile'])->name('users.profile');
    Route::apiResource('users', UserController::class);

    Route::post('/anexos/import', [AnexoController::class, 'import'])->name('anexos.import');
    Route::get('/anexos/export', [AnexoController::class, 'export'])->name('anexos.export');
    Route::apiResource('anexos', AnexoController::class);

    Route::post('/mobiles/import', [MobileController::class, 'import'])->name('mobiles.import');
    Route::get('/mobiles/export', [MobileController::class, 'export'])->name('mobiles.export');
    Route::apiResource('mobiles', MobileController::class);

    //Files
    // Route::get('/files', [FileController::class, 'index']);
    // Route::post('/files', [FileController::class, 'store']);
    // Route::delete('/files/{id}', [FileController::class, 'destroy']);
    Route::apiResource('files', FileController::class);
    Route::get('/files/{id}/download', [FileController::class, 'download']);
});

Route::middleware('web')->prefix('web')->group(function () {
    Route::get('/banners', [WebController::class, 'getAllBanners']);
    Route::get('/banners/{id}', [WebController::class, 'getBannerById']);
    Route::get('/popups', [WebController::class, 'getAllPopups']);
    Route::get('/popups/{id}', [WebController::class, 'getPopupById']);
    Route::get('/pages', [WebController::class, 'getAllPages']);
    Route::get('/pages/id/{id}', [WebController::class, 'getPageById']);
    Route::get('/pages/slug/{slug}', [WebController::class, 'getPageSlug']);
    Route::get('/pages/files/{id}/download', [FileController::class, 'download']);
    Route::get('/files/search', [WebController::class, 'searchFiles']);
    Route::get('/files/{id}/download', [WebController::class, 'downloadFile']);
    Route::get('/anexos/search', [WebController::class, 'searchAnexos']);
    Route::get('/mobiles/search', [WebController::class, 'searchMobiles']);
});
