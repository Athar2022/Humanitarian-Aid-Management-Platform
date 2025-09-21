<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AidRequestController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\DistributionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    Route::apiResource('aid-requests', AidRequestController::class);
    Route::patch('/aid-requests/{aid_request}/status', [AidRequestController::class, 'updateStatus']);
    
    Route::apiResource('donations', DonationController::class);
    Route::post('/donations/{donation}/approve', [DonationController::class, 'approve']);
    Route::post('/donations/{donation}/distribute', [DonationController::class, 'markDistributed']);

    Route::apiResource('distributions', DistributionController::class);
    Route::post('/distributions/{distribution}/status/{status}', [DistributionController::class, 'updateStatus']);
    Route::get('/volunteer/distributions', [DistributionController::class, 'volunteerDistributions']);

    Route::apiResource('notifications', NotificationController::class);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    
    Route::apiResource('users', UserController::class);
    Route::get('/users/role/{role}', [UserController::class, 'getByRole']);
    Route::get('/users/beneficiaries', [UserController::class, 'getBeneficiaries']);
    Route::get('/users/volunteers', [UserController::class, 'getVolunteers']);

    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('/dashboard/charts', [DashboardController::class, 'charts']);
    Route::get('/dashboard/activity', [DashboardController::class, 'activity']);
    Route::get('/dashboard/user-stats', [DashboardController::class, 'userStats']);
    
    Route::post('/upload-document', function (Request $request) {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'type' => 'sometimes|in:document,proof' // نوع الملف
        ]);
        
        $type = $request->type ?? 'document';
        $folder = $type === 'proof' ? 'proofs' : 'documents';
        
        $path = $request->file('file')->store($folder, 'public');
        
        return response()->json([
            'url' => asset("storage/$path"),
            'path' => $path,
            'name' => $request->file('file')->getClientOriginalName(),
            'size' => $request->file('file')->getSize()
        ]);
    });
});