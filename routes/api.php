<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\BudgetAllocationController;
use App\Http\Controllers\DirectionController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\FormF1Controller;
use App\Http\Controllers\DecretoController;
use App\Http\Controllers\GoalController;
use App\Http\Controllers\ItemPurchaseController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\PublicationMonthController;
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

    // Rutas protegidas por roles específicos
    Route::middleware(['role:Administrador del Sistema|Administrador Municipal'])->group(function () {
        Route::apiResource('users', UserController::class)->middleware('validate.hierarchical.user');
        Route::post('/users/reset-password/{id}', [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::get('directions-stats/users', [DirectionController::class, 'getUserStats'])->name('directions.user-stats');
    });

    // Rutas protegidas por permisos específicos
    Route::middleware(['permission:purchase_plans.list'])->group(function () {
        Route::apiResource('purchase-plans', PurchasePlanController::class);
        Route::get('purchase-plans/year/{year}', [PurchasePlanController::class, 'showByYear'])->name('purchase-plans.show.year');
        Route::get('purchase-plans/year/{year}/index', [PurchasePlanController::class, 'indexByYear'])->name('purchase-plans.index.year');
        Route::get('purchase-plans/available-directions', [PurchasePlanController::class, 'getAvailableDirections'])->name('purchase-plans.available-directions');
    });

    // Rutas para upload de decretos - SOLO para roles específicos
    Route::middleware(['role:Administrador del Sistema|Administrador Municipal|Director|Subrogante de Director'])->group(function () {
        Route::post('purchase-plans/upload/decreto', [PurchasePlanController::class, 'uploadDecreto'])->name('purchase-plans.upload.decreto');
    });

    // Rutas para cambio de estado de planes de compra
    Route::middleware(['permission:purchase_plans.visar|purchase_plans.approve', 'validate.purchase.plan.status'])->group(function () {
        Route::put('purchase-plans/status/{id}', [PurchasePlanController::class, 'updateStatus'])->name('purchase-plans.update.status');
    });

    // Ruta de envío restringida solo a administradores, directores y visadores
    Route::middleware(['permission:purchase_plans.send|purchase_plans.visar', 'can.send.purchase.plan', 'validate.purchase.plan.status'])->group(function () {
        Route::post('purchase-plans/{token}/send', [PurchasePlanController::class, 'send'])->name('purchase-plans.send');
    });

    // Rutas para el historial de estados de planes de compra
    Route::middleware(['permission:purchase_plan_statuses.history'])->group(function () {
        Route::get('purchase-plans/{purchasePlanId}/status-history', [PurchasePlanStatusController::class, 'getStatusHistory'])->name('purchase-plans.status-history');
        Route::get('purchase-plans/{purchasePlanId}/current-status', [PurchasePlanStatusController::class, 'getCurrentStatus'])->name('purchase-plans.current-status');
        Route::get('purchase-plan-statuses/{id}', [PurchasePlanStatusController::class, 'show'])->name('purchase-plan-statuses.show');
    });

    Route::middleware(['permission:purchase_plan_statuses.create'])->group(function () {
        Route::post('purchase-plan-statuses', [PurchasePlanStatusController::class, 'store'])->name('purchase-plan-statuses.store');
    });

    // Rutas para el historial de movimientos de planes de compra
    Route::middleware(['permission:history_purchase_histories.list'])->group(function () {
        Route::get('purchase-plans/{purchasePlanId}/movement-history', [HistoryPurchaseHistoryController::class, 'getMovementHistory'])->name('purchase-plans.movement-history');
        Route::get('purchase-plans/{purchasePlanId}/movement-statistics', [HistoryPurchaseHistoryController::class, 'getStatistics'])->name('purchase-plans.movement-statistics');
        Route::get('purchase-plans/{purchasePlanId}/movement-export', [HistoryPurchaseHistoryController::class, 'export'])->name('purchase-plans.movement-export');
        Route::get('movement-history/{id}', [HistoryPurchaseHistoryController::class, 'show'])->name('movement-history.show');
    });

    // Rutas para proyectos
    Route::middleware(['permission:projects.list'])->group(function () {
        Route::apiResource('projects', ProjectController::class);
        Route::get('projects/purchase-plan/{purchasePlanId}/index', [ProjectController::class, 'indexByPurchasePlan'])->name('projects.index.purchase-plan');
        Route::get('projects/export-word/{purchasePlanId}', [ProjectController::class, 'exportWord'])->name('projects.export.word');
        Route::get('projects/token/{token}', [ProjectController::class, 'showByToken'])->name('projects.show.token');
        Route::get('projects/dashboard', [ProjectController::class, 'getDashboard'])->name('projects.dashboard');
    });

    Route::middleware(['permission:projects.verification'])->group(function () {
        Route::post('projects/verification', [ProjectController::class, 'verification'])->name('projects.verification');
        Route::get('projects/verification/project/{projectId}/index', [ProjectController::class, 'showVerificationProject'])->name('projects.show.verification.project');
        Route::delete('projects/{projectId}/verification/{fileId}', [ProjectController::class, 'deleteVerificationProject'])->name('projects.delete.verification.project');
        Route::get('projects/verification/{fileId}/download', [ProjectController::class, 'downloadVerificationProject'])->name('projects.download.verification.project');
    });

    // Rutas para ítems de compra
    Route::middleware(['permission:item_purchases.list'])->group(function () {
        Route::get('item-purchases/template', [ItemPurchaseController::class, 'downloadTemplate'])->name('item-purchases.template');
        Route::apiResource('item-purchases', ItemPurchaseController::class);
        Route::get('item-purchases/export/{project_id}', [ItemPurchaseController::class, 'export'])->name('item-purchases.export');
        Route::post('item-purchases/import/{projectId}', [ItemPurchaseController::class, 'import'])->name('item-purchases.import');
    });

    Route::middleware(['permission:item_purchases.update_status'])->group(function () {
        Route::put('item-purchases/{id}/status', [ItemPurchaseController::class, 'updateStatus'])->name('item-purchases.update.status');
    });

    // Rutas para metas de proyectos estratégicos
    Route::middleware(['permission:goals.list', 'validate.strategic.project'])->group(function () {
        Route::apiResource('goals', GoalController::class);
        Route::get('goals/project/{projectId}/statistics', [GoalController::class, 'getProjectStatistics'])->name('goals.project.statistics');
        Route::get('goals/overdue', [GoalController::class, 'getOverdueGoals'])->name('goals.overdue');
    });

    Route::middleware(['permission:goals.update_progress', 'validate.strategic.project'])->group(function () {
        Route::put('goals/{id}/progress', [GoalController::class, 'updateProgress'])->name('goals.update.progress');
    });

    // Rutas para configuraciones (solo administradores)
    Route::middleware(['role:Administrador del Sistema|Administrador Municipal'])->group(function () {
        Route::apiResource('status-purchase-plans', StatusPurchasePlanController::class);
        Route::apiResource('directions', DirectionController::class);
    });

    // Rutas para módulos de configuración (todos los usuarios autenticados)
    Route::apiResource('type-projects', TypeProjectController::class);
    Route::apiResource('unit-purchasings', UnitPurchasingController::class);
    Route::apiResource('type-purchases', TypePurchaseController::class);
    Route::apiResource('budget-allocations', BudgetAllocationController::class);
    Route::apiResource('status-item-purchases', StatusItemPurchaseController::class);
    Route::apiResource('publication-months', PublicationMonthController::class);


    // Rutas para gestionar relaciones director-dirección
    Route::middleware(['permission:directions.list'])->group(function () {
        Route::get('directions/{direction}/director', [DirectionController::class, 'getDirector'])->name('directions.director');
        Route::get('directions/{direction}/users', [DirectionController::class, 'getUsers'])->name('directions.users');
        Route::get('directions/{direction}/users-by-role', [DirectionController::class, 'getUsersByRole'])->name('directions.users-by-role');
    });

    Route::middleware(['permission:directions.edit'])->group(function () {
        Route::post('directions/{direction}/assign-director', [DirectionController::class, 'assignDirector'])->name('directions.assign-director');
        Route::post('directions/{direction}/assign-users', [DirectionController::class, 'assignUsers'])->name('directions.assign-users')->middleware('validate.hierarchical.user');
        Route::delete('directions/{direction}/remove-users', [DirectionController::class, 'removeUsers'])->name('directions.remove-users');
    });

    // Rutas para archivos
    Route::middleware(['permission:files.list'])->group(function () {
        Route::apiResource('files', FileController::class);
        Route::get('/files/{id}/download', [FileController::class, 'download']);
    });

    // Rutas para formularios F1
    Route::middleware(['permission:form_f1.list'])->group(function () {
        Route::apiResource('form-f1', FormF1Controller::class);
    });

    // Ruta específica para descarga de formularios F1
    Route::middleware(['permission:form_f1.download'])->group(function () {
        Route::get('/form-f1/{id}/download', [FormF1Controller::class, 'download'])->name('form-f1.download');
    });

    // Rutas para decretos 
    Route::middleware(['role:Administrador del Sistema|Administrador Municipal|Director|Subrogante de Director'])->group(function () {
        Route::apiResource('decretos', DecretoController::class);
    });

    // Ruta específica para descarga de decretos
    Route::middleware(['permission:decretos.download'])->group(function () {
        Route::get('/decretos/{id}/download', [DecretoController::class, 'download'])->name('decretos.download');
    });

    // Rutas de perfil de usuario
    Route::post('/users/update-password', [UserController::class, 'updatePassword'])->name('users.reset-update');
    Route::post('/users/update-profile', [UserController::class, 'updateProfile'])->name('users.update-profile');
    Route::get('/users/profile', [UserController::class, 'profile'])->name('users.profile');
});
