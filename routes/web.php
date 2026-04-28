<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\CenterController;
use App\Http\Controllers\Web\RouteController;
use App\Http\Controllers\Web\ClientController;
use App\Http\Controllers\Web\RequestController;
use App\Http\Controllers\Web\PedidoController;
use App\Http\Controllers\Web\RouteClosureWebController;
use Illuminate\Support\Facades\Route;

// Guest routes
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/password/change', [AuthController::class, 'showChangePassword']);
    Route::post('/password/change', [AuthController::class, 'changePassword']);

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Admin only (ti_admin)
    Route::middleware('role:ti_admin')->prefix('admin')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('admin.users');
        Route::post('/users', [UserController::class, 'store']);
        Route::put('/users/{id}', [UserController::class, 'update']);
        Route::post('/users/{id}/toggle', [UserController::class, 'toggleActive']);
        Route::post('/users/{id}/reset-password', [UserController::class, 'resetPassword']);

        Route::get('/centers', [CenterController::class, 'index'])->name('admin.centers');
        Route::post('/centers', [CenterController::class, 'store']);
        Route::put('/centers/{id}', [CenterController::class, 'update']);
        Route::post('/centers/{id}/assign', [CenterController::class, 'assign']);

        Route::get('/routes', [RouteController::class, 'index'])->name('admin.routes');
        Route::post('/routes', [RouteController::class, 'store']);
        Route::put('/routes/{id}', [RouteController::class, 'update']);
        Route::delete('/routes/{id}', [RouteController::class, 'destroy']);
    });

    // Shared (ti_admin + supervisor)
    Route::middleware('role:ti_admin,supervisor')->group(function () {
        Route::get('/clients', [ClientController::class, 'index'])->name('clients');
        Route::post('/clients', [ClientController::class, 'store']);
        Route::put('/clients/{id}', [ClientController::class, 'update']);
        Route::delete('/clients/{id}', [ClientController::class, 'destroy']);

        Route::get('/requests', [RequestController::class, 'index'])->name('requests');
        Route::post('/requests/{id}/approve', [RequestController::class, 'approve']);
        Route::post('/requests/{id}/reject', [RequestController::class, 'reject']);

        Route::get('/pedidos', [PedidoController::class, 'index'])->name('pedidos');
        Route::get('/cierres', [RouteClosureWebController::class, 'index'])->name('cierres');
        Route::get('/cierres/{id}', [RouteClosureWebController::class, 'show'])->name('cierres.show');
    });
});
