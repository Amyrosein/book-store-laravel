<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;
use App\Http\Controllers\AuthController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request){
    return $request->user();
});




Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,2');
Route::post('/login/otp', [AuthController::class, 'validate_otp']);

Route::middleware('auth:sanctum')->group(function (){
    Route::get('/buy_vip', [AuthController::class, 'buy_vip']);
    Route::get('/logout', [AuthController::class, 'logout']);
});

Route::middleware('admin')->group(function (){
    Route::apiResource('books', BookController::class);
    Route::delete('/delete_token/{phone}', [AuthController::class, 'delete_token']);
});

