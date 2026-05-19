<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AssignmentController;

Route::middleware(['auth:sanctum', 'role:admin|manager,manage-assets'])
    ->prefix('assignments')
    ->group(function () {
        Route::get('/', [AssignmentController::class, 'index']);
        Route::get('/{id}', [AssignmentController::class, 'show']);
        Route::post('/', [AssignmentController::class, 'store']);
        Route::put('/{id}', [AssignmentController::class, 'update']);
        Route::delete('/{id}', [AssignmentController::class, 'destroy']);
    });
