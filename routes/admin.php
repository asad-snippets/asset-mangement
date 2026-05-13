<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\UserManagementController;

Route::middleware(['auth:sanctum', 'role'])
    ->prefix('admin')
    ->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard']);
        Route::get('/users', [AdminController::class, 'users']);
        Route::get('/users/{id}', [AdminController::class, 'singleUser']);
        Route::delete('/users/{id}', [AdminController::class, 'deleteUser']);
    });

Route::middleware('auth:sanctum')
    ->prefix('admin')
    ->group(function () {
        Route::post('/users', [UserManagementController::class, 'createUser'])
            ->middleware('role:admin|manager,create-user');
        Route::put('/users/{id}/permissions', [UserManagementController::class, 'updateUserPermissions'])
            ->middleware('role:admin|manager,edit-user');
    });
