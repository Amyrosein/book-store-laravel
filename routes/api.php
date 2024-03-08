<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;
use App\Http\Controllers\AuthController;


Route::apiResource('books', BookController::class);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/login/otp', [AuthController::class, 'validate_otp']);

Route::middleware('auth:sanctum')->group(function (){
    Route::get('/buy_vip', [AuthController::class, 'buy_vip']);
    Route::get('/logout', [AuthController::class, 'logout']);
});

Route::delete('/delete_token/{phone}', [AuthController::class, 'delete_token']);
