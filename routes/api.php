<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthManager;
use App\Models\User;

Route::post('/login', [AuthManager::class, 'login']);
Route::post('/register', [AuthManager::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/list', function (Request $request) {
        return User::all();
    });
});
