<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::middleware('auth:sanctum')
    ->prefix('users')
    ->group(function () {
        Route::get('/', [UserController::class, 'index'])
            ->middleware('role:admin|manager');
        Route::get('/{id}', [UserController::class, 'show'])
            ->middleware('role:admin|manager');
        Route::post('/', [UserController::class, 'store'])
            ->middleware('role:admin|manager,create-user');
        Route::put('/{id}', [UserController::class, 'update'])
            ->middleware('role:admin|manager,edit-user');
        Route::delete('/{id}', [UserController::class, 'destroy'])
            ->middleware('role:admin|manager,delete-user');
    });
