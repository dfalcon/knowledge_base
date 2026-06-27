<?php

use App\Modules\Users\Controllers\AdminUserController;
use App\Modules\Users\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });

    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/users/pending', [AdminUserController::class, 'pending']);
        Route::post('/users/{user}/approve', [AdminUserController::class, 'approve']);
        Route::put('/users/{user}/role', [AdminUserController::class, 'updateRole']);
    });
});
