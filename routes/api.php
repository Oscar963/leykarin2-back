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
use App\Http\Controllers\PurchasePlanStatusController;
use App\Http\Controllers\StatusItemPurchaseController;
use App\Http\Controllers\StatusPurchasePlanController;
use App\Http\Controllers\TypeProjectController;
use App\Http\Controllers\TypePurchaseController;
use App\Http\Controllers\UnitPurchasingController;
use App\Http\Controllers\UserController;
use App\Models\Project;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HistoryPurchaseHistoryController;

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
    Route::post('purchase-plans/upload/decreto', [PurchasePlanController::class, 'uploadDecreto'])->name('purchase-plans.upload.decreto');
    Route::post('purchase-plans/{token}/send', [PurchasePlanController::class, 'send'])->name('purchase-plans.send');
    Route::put('purchase-plans/status/{id}', [PurchasePlanController::class, 'updateStatus'])->name('purchase-plans.update.status');
    Route::get('purchase-plans/year/{year}', [PurchasePlanController::class, 'showByYear'])->name('purchase-plans.show.year');
    Route::get('purchase-plans/year/{year}/index', [PurchasePlanController::class, 'indexByYear'])->name('purchase-plans.index.year');
    Route::get('purchase-plans/year/{year}/user', [PurchasePlanController::class, 'indexByYearForUser'])->name('purchase-plans.index.year.user');

    // Rutas para el historial de estados de planes de compra
    Route::get('purchase-plans/{purchasePlanId}/status-history', [PurchasePlanStatusController::class, 'getStatusHistory'])->name('purchase-plans.status-history');
    Route::get('purchase-plans/{purchasePlanId}/current-status', [PurchasePlanStatusController::class, 'getCurrentStatus'])->name('purchase-plans.current-status');
    Route::post('purchase-plan-statuses', [PurchasePlanStatusController::class, 'store'])->name('purchase-plan-statuses.store');
    Route::get('purchase-plan-statuses/{id}', [PurchasePlanStatusController::class, 'show'])->name('purchase-plan-statuses.show');

    // Rutas para el historial de movimientos de planes de compra
    Route::get('purchase-plans/{purchasePlanId}/movement-history', [HistoryPurchaseHistoryController::class, 'getMovementHistory'])->name('purchase-plans.movement-history');
    Route::get('purchase-plans/{purchasePlanId}/movement-statistics', [HistoryPurchaseHistoryController::class, 'getStatistics'])->name('purchase-plans.movement-statistics');
    Route::get('purchase-plans/{purchasePlanId}/movement-export', [HistoryPurchaseHistoryController::class, 'export'])->name('purchase-plans.movement-export');
    Route::get('movement-history/{id}', [HistoryPurchaseHistoryController::class, 'show'])->name('movement-history.show');

    Route::apiResource('projects', ProjectController::class);
    Route::get('projects/purchase-plan/{purchasePlanId}/index', [ProjectController::class, 'indexByPurchasePlan'])->name('projects.index.purchase-plan');
    Route::post('projects/verification', [ProjectController::class, 'verification'])->name('projects.verification');
    Route::get('projects/verification/project/{projectId}/index', [ProjectController::class, 'showVerificationProject'])->name('projects.show.verification.project');
    Route::delete('projects/{projectId}/verification/{fileId}', [ProjectController::class, 'deleteVerificationProject'])->name('projects.delete.verification.project');
    Route::get('projects/verification/{fileId}/download', [ProjectController::class, 'downloadVerificationProject'])->name('projects.download.verification.project');
    Route::get('projects/token/{token}', [ProjectController::class, 'showByToken'])->name('projects.show.token');

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
    Route::get('/form-f1/{id}/download', [FormF1Controller::class, 'download'])->name('form-f1.download');


    Route::post('/users/reset-password/{id}', [UserController::class, 'resetPassword'])->name('users.reset-password');
    Route::post('/users/update-password', [UserController::class, 'updatePassword'])->name('users.reset-update');
    Route::post('/users/update-profile', [UserController::class, 'updateProfile'])->name('users.update-profile');
    Route::get('/users/profile', [UserController::class, 'profile'])->name('users.profile');
    Route::apiResource('users', UserController::class);

    Route::apiResource('files', FileController::class);
    Route::get('/files/{id}/download', [FileController::class, 'download']);

});
