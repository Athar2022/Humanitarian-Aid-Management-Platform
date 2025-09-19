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
    Route::post('/donations/{donation}/approve', [DonationController::class, 'approve']);
    Route::post('/donations/{donation}/distribute', [DonationController::class, 'markDistributed']);

    // Distributions Routes
    Route::apiResource('distributions', DistributionController::class);
    Route::post('/distributions/{distribution}/status/{status}', [DistributionController::class, 'updateStatus']);
    Route::get('/volunteer/distributions', [DistributionController::class, 'volunteerDistributions']);

    // Notifications Routes
    Route::apiResource('notifications', NotificationController::class);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    
    // Users Routes
    Route::apiResource('users', UserController::class);
    Route::get('/users/role/{role}', [UserController::class, 'getByRole']);
    Route::get('/users/beneficiaries', [UserController::class, 'getBeneficiaries']);
    Route::get('/users/volunteers', [UserController::class, 'getVolunteers']);

    // Dashboard Statistics
    Route::get('/dashboard/stats', function (Request $request) {
        $user = $request->user();
        
        $stats = [
            'beneficiaries' => \App\Models\User::where('role', 'beneficiary')->count(),
            'volunteers' => \App\Models\User::where('role', 'volunteer')->count(),
            'donations' => \App\Models\Donation::count(),
            'aid_requests' => \App\Models\AidRequest::count(),
            'distributions' => \App\Models\Distribution::count(),
        ];

        return response()->json($stats);
    });
});