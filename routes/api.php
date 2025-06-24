<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthManager;

use App\Models\User;
use App\Models\Product;
use App\Models\Image;

use App\Http\Controllers\ImageController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;

Route::post('/register', [AuthManager::class, 'register']);
Route::post('/login', [AuthManager::class, 'login']);
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('/articles', [ProductController::class, 'index']);
Route::get('/images', [ImageController::class, 'index']);
