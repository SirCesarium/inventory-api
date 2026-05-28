<?php

use App\Http\Controllers\AuditController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);

    Route::apiResource('users', UserController::class);
    Route::post('/users/{user}/roles/{role}', [UserController::class, 'attachRole']);
    Route::delete('/users/{user}/roles/{role}', [UserController::class, 'detachRole']);

    Route::apiResource('roles', RoleController::class);
    Route::post('/roles/{role}/permissions/{permission}', [RoleController::class, 'attachPermission']);
    Route::delete('/roles/{role}/permissions/{permission}', [RoleController::class, 'detachPermission']);

    Route::apiResource('permissions', PermissionController::class);

    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('products', ProductController::class);

    Route::apiResource('audits', AuditController::class)->only(['index', 'show']);
});
