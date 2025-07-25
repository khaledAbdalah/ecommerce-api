<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// auth routes
Route::middleware('api.guest')->group(function () {
    Route::post('register', RegisterController::class);
    Route::post('login', LoginController::class);
});
