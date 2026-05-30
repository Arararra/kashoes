<?php

use App\Http\Controllers\Api\CashFlowController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api.bearer'])->group(function () {
    Route::apiResource('cash-flows', CashFlowController::class)->parameters(['cash-flows' => 'id']);
    Route::apiResource('customers', CustomerController::class)->parameters(['customers' => 'id']);
    Route::apiResource('orders', OrderController::class)->parameters(['orders' => 'id']);
    Route::apiResource('services', ServiceController::class)->parameters(['services' => 'id']);
    Route::apiResource('users', UserController::class)->parameters(['users' => 'id']);
});
