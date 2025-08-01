<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Support\Facades\Route;

// auth routes
Route::middleware('api.guest')->group(function () {
    Route::post('register', RegisterController::class);
    Route::post('login', LoginController::class);
});

Route::post('forgot-password', ForgotPasswordController::class);
Route::post('reset-password', ResetPasswordController::class);

Route::post('logout', LogoutController::class)->middleware('auth:sanctum');

// user profile
Route::middleware('auth:sanctum')->group(function () {
    Route::get('profile/show', [UserProfileController::class, 'show']);
    Route::put('profile/update', [UserProfileController::class, 'update']);
    Route::put('profile/update/password', [UserProfileController::class, 'updatePassword']);
    Route::delete('profile/delete', [UserProfileController::class, 'destroy']);
});

// products routes and categories
Route::apiResource('products', ProductController::class)->only(['index', 'show']);
Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);

Route::middleware(['auth:sanctum', 'isAdmin'])->group(function () {
    Route::get('products/create', [ProductController::class, 'create']);
    Route::apiResource('products', ProductController::class)->except(['index', 'show']);

    Route::apiResource('categories', CategoryController::class)->except(['index', 'show']);
});

// cart routes
Route::middleware('auth:sanctum')->group(function () {

    Route::delete('cart/clear', [CartController::class, 'clear']);
    Route::get('cart/count', [CartController::class, 'count']);

    Route::apiResource('cart', CartController::class)->except('show');

    // chekcout routes
    Route::controller(CheckoutController::class)->group(function () {
        Route::get('checkout/create', 'create');
        Route::post('checkout/store', 'store');
        Route::post('checkout/callback', 'paymentCallback');
    });

    // orders routes
    Route::apiResource('orders', OrderController::class)->except('store');

    Route::get('orders/{order}/cancel', [OrderController::class, 'cancel']);

});