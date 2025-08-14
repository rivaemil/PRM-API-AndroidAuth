<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthManager;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\ProductController;

// Auth (público)
Route::post('/register', [AuthManager::class, 'register']);
Route::post('/login',    [AuthManager::class, 'login']);

// Quién soy (protegido)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Rutas públicas
Route::get('/articles', [ProductController::class, 'index']); // si lo usas
Route::get('/images',   [ImageController::class, 'index']);

// Products público
Route::apiResource('products', ProductController::class)
    ->only(['index', 'show'])
    ->names([
        'index' => 'products.index',
        'show'  => 'products.show',
    ]);

// Products protegido (requiere token)
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('products', ProductController::class)
        ->only(['store', 'update', 'destroy'])
        ->names([
            'store'   => 'products.store',
            'update'  => 'products.update',
            'destroy' => 'products.destroy',
        ]);
});
