<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MaintenanceController;

Route::middleware(['auth:sanctum', 'role:admin|manager,manage-assets'])
    ->prefix('maintenances')
    ->group(function () {
        Route::get('/', [MaintenanceController::class, 'index']);
        Route::get('/{id}', [MaintenanceController::class, 'show']);
        Route::post('/', [MaintenanceController::class, 'store']);
        Route::put('/{id}', [MaintenanceController::class, 'update']);
        Route::delete('/{id}', [MaintenanceController::class, 'destroy']);
    });
