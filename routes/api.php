<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;

Route::get('/v1/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');


Route::post('/v1/login', [UserController::class, 'login']);
Route::get('/v1/logout', [UserController::class, 'logout']);
Route::post('/v1/signup', [UserController::class, 'signup']);

Route::get('/v1/products', [ProductController::class, 'index']);
