<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController;

Route::middleware('auth:sanctum')
    ->prefix('employees')
    ->group(function () {
        Route::get('/', [EmployeeController::class, 'index'])
            ->middleware('role:admin|manager');
        Route::get('/{id}', [EmployeeController::class, 'show'])
            ->middleware('role:admin|manager');
        Route::post('/', [EmployeeController::class, 'store'])
            ->middleware('role:admin|manager,create-user');
        Route::put('/{id}', [EmployeeController::class, 'update'])
            ->middleware('role:admin|manager,edit-user');
        Route::delete('/{id}', [EmployeeController::class, 'destroy'])
            ->middleware('role:admin|manager,delete-user');
    });
