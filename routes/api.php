<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\MutationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('category', CategoryController::class);

    Route::post('product/location', [ProductController::class, 'add_product_location']);
    Route::delete('product/location/{id}', [ProductController::class, 'remove_product_location']);

    Route::apiResource('product', ProductController::class);
    Route::apiResource('location', LocationController::class);
    Route::apiResource('user', UserController::class);
    Route::apiResource('mutation', MutationController::class)->only(['index', 'store', 'show']);
});

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::post('/register', [AuthController::class, 'register']);
});