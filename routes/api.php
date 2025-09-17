<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AidRequestController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\DistributionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Aid Requests Routes
    Route::apiResource('aid-requests', AidRequestController::class);

    // Donations Routes
    Route::apiResource('donations', DonationController::class);

    // Distributions Routes
    Route::apiResource('distributions', DistributionController::class);

    // Notifications Routes
    Route::apiResource('notifications', NotificationController::class);
    
    // Users Routes (Admin only)
    Route::apiResource('users', UserController::class)->middleware('can:admin');
    
});