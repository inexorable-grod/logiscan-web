<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DeviceTokenController;
use App\Http\Controllers\Api\RouteController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\ScanController;
use App\Http\Controllers\Api\RequestController;
use App\Http\Controllers\Api\RouteClosureController;
use App\Http\Controllers\Api\ClosureDocumentController;
use App\Http\Controllers\Api\ManifestItemController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\MachComparisonController;
use App\Http\Controllers\Shared\RequestResolutionController;
use App\Http\Controllers\Shared\ClientController as SharedClientController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CenterController;
use App\Http\Controllers\Admin\RouteController as AdminRouteController;
use App\Http\Controllers\Supervisor\CenterSelectorController;
use App\Http\Controllers\Supervisor\DashboardController;

// Public
Route::post('/login', [AuthController::class, 'login']);

// Authenticated (any role)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/password/change', [AuthController::class, 'changePassword']);
});

// Operario
Route::middleware(['auth:sanctum', 'role:operario', 'center.access', 'force.password'])->group(function () {
    Route::post('/device-tokens', [DeviceTokenController::class, 'store']);
    Route::get('/routes', [RouteController::class, 'index']);
    Route::get('/clients', [ClientController::class, 'index']);
    Route::post('/scans', [ScanController::class, 'store']);
    Route::post('/scans/batch', [ScanController::class, 'batch']);
    Route::get('/scans/history', [ScanController::class, 'history']);
    Route::post('/requests', [RequestController::class, 'store']);
    Route::get('/requests/mine', [RequestController::class, 'index']);

    // Route closures (operario can initiate + view own)
    Route::post('/route-closures', [RouteClosureController::class, 'store']);
    Route::get('/route-closures', [RouteClosureController::class, 'index']);
    Route::get('/route-closures/{id}', [RouteClosureController::class, 'show']);
    Route::post('/route-closures/{id}/documents', [ClosureDocumentController::class, 'store']);
    Route::get('/route-closures/{id}/comparison', [MachComparisonController::class, 'show']);
    Route::get('/routes/{routeId}/orders', [OrderController::class, 'index']);
});

// Shared (Supervisor + TI Admin)
Route::prefix('shared')
    ->middleware(['auth:sanctum', 'role:supervisor,ti_admin', 'center.access'])
    ->group(function () {
        Route::get('/requests', [RequestResolutionController::class, 'index']);
        Route::patch('/requests/{id}/approve', [RequestResolutionController::class, 'approve']);
        Route::patch('/requests/{id}/reject', [RequestResolutionController::class, 'reject']);
        Route::get('/clients', [SharedClientController::class, 'index']);
        Route::post('/clients', [SharedClientController::class, 'store']);
        Route::put('/clients/{id}', [SharedClientController::class, 'update']);

        // Route closures management
        Route::get('/route-closures', [RouteClosureController::class, 'index']);
        Route::get('/route-closures/{id}', [RouteClosureController::class, 'show']);
        Route::post('/route-closures/{id}/approve', [RouteClosureController::class, 'approve']);
        Route::post('/route-closures/{id}/reject', [RouteClosureController::class, 'reject']);
        Route::post('/route-closures/{id}/documents', [ClosureDocumentController::class, 'store']);
        Route::delete('/route-closures/{id}/documents/{docId}', [ClosureDocumentController::class, 'destroy']);
        Route::post('/route-closures/{id}/documents/{docId}/reprocess', [ClosureDocumentController::class, 'reprocess']);
        Route::get('/route-closures/{id}/manifest-items', [ManifestItemController::class, 'index']);
        Route::post('/route-closures/{id}/manifest-items', [ManifestItemController::class, 'store']);
        Route::patch('/route-closures/{id}/manifest-items/{itemId}', [ManifestItemController::class, 'update']);
        Route::delete('/route-closures/{id}/manifest-items/{itemId}', [ManifestItemController::class, 'destroy']);
        Route::get('/route-closures/{id}/comparison', [MachComparisonController::class, 'show']);
        Route::post('/route-closures/{id}/generate-orders', [OrderController::class, 'generateFromManifest']);
        Route::get('/routes/{routeId}/orders', [OrderController::class, 'index']);
    });

// Admin (TI only)
Route::prefix('admin')
    ->middleware(['auth:sanctum', 'role:ti_admin'])
    ->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::put('/users/{id}', [UserController::class, 'update']);
        Route::patch('/users/{id}/deactivate', [UserController::class, 'deactivate']);
        Route::patch('/users/{id}/reset-password', [UserController::class, 'resetPassword']);

        Route::get('/centers', [CenterController::class, 'index']);
        Route::post('/centers', [CenterController::class, 'store']);
        Route::put('/centers/{id}', [CenterController::class, 'update']);
        Route::post('/centers/{id}/assign', [CenterController::class, 'assign']);

        Route::get('/routes', [AdminRouteController::class, 'index']);
        Route::post('/routes', [AdminRouteController::class, 'store']);
        Route::put('/routes/{id}', [AdminRouteController::class, 'update']);
    });

// Supervisor
Route::prefix('supervisor')
    ->middleware(['auth:sanctum', 'role:supervisor'])
    ->group(function () {
        Route::get('/centers', [CenterSelectorController::class, 'index']);
        Route::post('/select-center', [CenterSelectorController::class, 'select']);
        Route::get('/dashboard', [DashboardController::class, 'index']);
    });
