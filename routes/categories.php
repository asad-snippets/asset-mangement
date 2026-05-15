<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;

Route::middleware('auth:sanctum')
    ->prefix('categories')
    ->group(function () {
        Route::get('/', [CategoryController::class, 'index'])
            ->middleware('role:admin|manager,manage-assets');
        Route::get('/{id}', [CategoryController::class, 'show'])
            ->middleware('role:admin|manager,manage-assets');
        Route::post('/', [CategoryController::class, 'store'])
            ->middleware('role:admin');
        Route::put('/{id}', [CategoryController::class, 'update'])
            ->middleware('role:admin');
        Route::delete('/{id}', [CategoryController::class, 'destroy'])
            ->middleware('role:admin');
    });
