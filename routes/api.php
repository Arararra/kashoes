<?php

use App\Http\Controllers\Api\CashFlowController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/google', [AuthController::class, 'googleLogin']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

    Route::apiResource('cash-flows', CashFlowController::class)->parameters(['cash-flows' => 'id']);
    Route::apiResource('customers', CustomerController::class)->parameters(['customers' => 'id']);
    Route::apiResource('orders', OrderController::class)->parameters(['orders' => 'id']);
    Route::apiResource('services', ServiceController::class)->parameters(['services' => 'id']);
    Route::apiResource('users', UserController::class)->parameters(['users' => 'id']);
});
