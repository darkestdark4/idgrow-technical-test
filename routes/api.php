<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ProductController;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('category', CategoryController::class);
    Route::apiResource('product', ProductController::class);
    Route::apiResource('location', LocationController::class);
});

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::post('/register', [AuthController::class, 'register']);
});