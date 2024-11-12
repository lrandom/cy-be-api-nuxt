<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;

Route::get('/v1/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');


Route::post('/v1/login', [UserController::class, 'login']);
Route::post('/v1/logout', [UserController::class, 'logout'])->middleware('auth:api');
Route::post('/v1/signup', [UserController::class, 'signup']);
Route::get('/v1/products', [ProductController::class, 'index'])->middleware('auth:api');
Route::get('/v1/products/{id}', [ProductController::class, 'show'])->middleware('auth:api');
Route::get('/v1/products/{id}/check-stock', [ProductController::class, 'checkStock'])->middleware('auth:api');

Route::post('/v1/order', [OrderController::class, 'store'])->middleware('auth:api');
Route::get('/v1/orders', [OrderController::class, 'index'])->middleware('auth:api');

Route::get('/v1/categories', [CategoryController::class, 'index'])->middleware('auth:api'));
Route::get('/v1/categories/{slug}', [CategoryController::class, 'show'])->middleware('auth:api'));
