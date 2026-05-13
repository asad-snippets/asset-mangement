<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AssetController;

Route::middleware(['auth:sanctum', 'role:admin|manager,manage-assets'])
    ->prefix('assets')
    ->group(function () {
        Route::get('/', [AssetController::class, 'index']);
        Route::get('/{id}', [AssetController::class, 'show']);
        Route::post('/', [AssetController::class, 'store']);
        Route::put('/{id}', [AssetController::class, 'update']);
        Route::delete('/{id}', [AssetController::class, 'destroy']);
    });
