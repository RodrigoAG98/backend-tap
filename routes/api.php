<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;

// Rutas públicas
Route::post('/login', [AuthController::class, 'login'])->name('login');

//Rutas protegidas
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::prefix('users')->name('users.')->controller(UserController::class)->group(function(){
        Route::get('export','export')->name('export');
        Route::get('pdf','pdf')->name('pdf');
    });
    Route::apiResource('users',UserController::class);
    Route::prefix('products')->name('products.')->controller(ProductController::class)->group(function(){
        Route::get('export','export')->name('export');
        Route::get('pdf','pdf')->name('pdf');
    });
    Route::apiResource('products',ProductController::class);
    Route::prefix('profiles')->name('profiles.')->controller(ProfileController::class)->group(function(){
        Route::get('export','export')->name('export');
        Route::get('pdf','pdf')->name('pdf');
    });
    Route::apiResource('profiles',ProfileController::class);
});