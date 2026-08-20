<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;

// Rutas públicas
Route::post('/login', [AuthController::class, 'login']);

//Rutas protegidas
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::apiResource('users',UserController::class);
    Route::apiResource('products',ProductController::class);
    Route::apiResource('profiles',ProfileController::class);
});