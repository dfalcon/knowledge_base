<?php

use App\Modules\Documents\Controllers\DocumentController;
use App\Modules\KnowledgeBases\Controllers\KnowledgeBaseController;
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

    Route::get('/documents', [DocumentController::class, 'index']);

    Route::prefix('knowledge-bases')->group(function () {
        Route::get('/', [KnowledgeBaseController::class, 'index']);
        Route::post('/', [KnowledgeBaseController::class, 'store']);
        Route::get('/{knowledgeBase}', [KnowledgeBaseController::class, 'show']);
        Route::put('/{knowledgeBase}', [KnowledgeBaseController::class, 'update']);
        Route::delete('/{knowledgeBase}', [KnowledgeBaseController::class, 'destroy']);
        Route::post('/{knowledgeBase}/permissions', [KnowledgeBaseController::class, 'grantPermission']);
        Route::delete('/{knowledgeBase}/permissions/{user}', [KnowledgeBaseController::class, 'revokePermission']);
    });

    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/users/pending', [AdminUserController::class, 'pending']);
        Route::post('/users/{user}/approve', [AdminUserController::class, 'approve']);
        Route::put('/users/{user}/role', [AdminUserController::class, 'updateRole']);
    });
});
