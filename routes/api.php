<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\BudgetAllocationController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\FormF1Controller;
use App\Http\Controllers\ItemPurchaseController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\PurchasePlanController;
use App\Http\Controllers\StatusItemPurchaseController;
use App\Http\Controllers\StatusPurchasePlanController;
use App\Http\Controllers\TypeProjectController;
use App\Http\Controllers\TypePurchaseController;
use App\Http\Controllers\UnitPurchasingController;
use App\Http\Controllers\UserController;
use App\Models\Project;
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
    Route::get('user', [AuthController::class, 'user']);
    Route::get('isAuthenticated', [AuthController::class, 'isAuthenticated']);

    Route::apiResource('purchase-plans', PurchasePlanController::class);
    Route::get('purchase-plans/token/{token}', [PurchasePlanController::class, 'showByToken'])->name('purchase-plans.show.token');
    Route::put('purchase-plans/token/{token}', [PurchasePlanController::class, 'updateByToken'])->name('purchase-plans.update.token');
    Route::post('purchase-plans/upload/decreto', [PurchasePlanController::class, 'uploadDecreto'])->name('purchase-plans.upload.decreto');
    Route::post('purchase-plans/upload/form-f1', [PurchasePlanController::class, 'uploadFormF1'])->name('purchase-plans.upload.form-f1');
    Route::post('purchase-plans/send/{token}', [PurchasePlanController::class, 'send'])->name('purchase-plans.send');
    Route::put('purchase-plans/status/{id}', [PurchasePlanController::class, 'updateStatus'])->name('purchase-plans.update.status');
    Route::get('purchase-plans/year/{year}', [PurchasePlanController::class, 'showByYear'])->name('purchase-plans.show.year');
    
    Route::apiResource('projects', ProjectController::class);
    Route::get('projects/token/{token}', [ProjectController::class, 'showByToken'])->name('projects.show.token');
    Route::put('projects/token/{token}', [ProjectController::class, 'updateByToken'])->name('projects.update.token');

    Route::apiResource('item-purchases', ItemPurchaseController::class);
    Route::put('item-purchases/{id}/status', [ItemPurchaseController::class, 'updateStatus'])->name('item-purchases.update.status');
    Route::get('item-purchases/export/{project_id}', [ItemPurchaseController::class, 'export'])->name('item-purchases.export');
    
    Route::apiResource('budget-allocations', BudgetAllocationController::class);
    Route::apiResource('type-purchases', TypePurchaseController::class);
    Route::apiResource('type-projects', TypeProjectController::class);
    Route::apiResource('unit-purchasings', UnitPurchasingController::class);
    Route::apiResource('status-item-purchases', StatusItemPurchaseController::class);
    Route::apiResource('status-purchase-plans', StatusPurchasePlanController::class);
    Route::apiResource('form-f1', FormF1Controller::class);

    Route::post('/users/reset-password/{id}', [UserController::class, 'resetPassword'])->name('users.reset-password');
    Route::post('/users/update-password', [UserController::class, 'updatePassword'])->name('users.reset-update');
    Route::post('/users/update-profile', [UserController::class, 'updateProfile'])->name('users.update-profile');
    Route::get('/users/profile', [UserController::class, 'profile'])->name('users.profile');
    Route::apiResource('users', UserController::class);

    Route::apiResource('files', FileController::class);
    Route::get('/files/{id}/download', [FileController::class, 'download']);

});
