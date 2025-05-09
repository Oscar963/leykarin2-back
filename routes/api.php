<?php

use App\Http\Controllers\AnexoController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\DependenceController;
use App\Http\Controllers\EvidenceController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\MobileController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PopupController;
use App\Http\Controllers\TypeComplaintController;
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

    Route::apiResource('complaints', ComplaintController::class);
    Route::apiResource('dependences', DependenceController::class);
    Route::apiResource('type-complaints', TypeComplaintController::class);
    Route::apiResource('evidences', EvidenceController::class);
    Route::get('/evidences/{id}/download', [EvidenceController::class, 'download']);

    Route::post('/users/reset-password/{id}', [UserController::class, 'resetPassword'])->name('users.reset-password');
    Route::post('/users/update-password', [UserController::class, 'updatePassword'])->name('users.reset-update');
    Route::post('/users/update-profile', [UserController::class, 'updateProfile'])->name('users.update-profile');
    Route::get('/users/profile', [UserController::class, 'profile'])->name('users.profile');
    Route::apiResource('users', UserController::class);

    Route::apiResource('files', FileController::class);
    Route::get('/files/{id}/download', [FileController::class, 'download']);
});

Route::prefix('web')->group(function () {
    Route::post('/complaint', [WebController::class, 'storeComplaint'])->name('web.complaint.store');
    Route::get('/typecomplaints', [WebController::class, 'getAllTypeComplaint'])->name('web.complaint.type');
    Route::get('/dependences', [WebController::class, 'getAllDependence'])->name('web.complaint.dependence');
});
