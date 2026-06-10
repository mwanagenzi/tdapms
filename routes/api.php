<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\DepositApiController;
use App\Http\Controllers\Api\InspectionReportApiController;
use App\Http\Controllers\Api\MaintenanceRequestApiController;
use App\Http\Controllers\Api\MessageApiController;
use App\Http\Controllers\Api\NotificationApiController;
use App\Http\Controllers\Api\TenantProfileApiController;

/*
|--------------------------------------------------------------------------
| API Routes — Tenant Mobile App (Flutter)
|--------------------------------------------------------------------------
| All routes are prefixed with /api automatically.
| Authentication: Laravel Sanctum token-based (Bearer token).
*/

// Public — tenant authentication
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
});

// MPESA Daraja callbacks — not auth protected (Safaricom hits these)
Route::prefix('mpesa')->group(function () {
    Route::post('stk-callback', [\App\Http\Controllers\MpesaCallbackController::class, 'stkCallback']);
    Route::post('b2c-callback', [\App\Http\Controllers\MpesaCallbackController::class, 'b2cCallback']);
    Route::post('b2c-timeout', [\App\Http\Controllers\MpesaCallbackController::class, 'b2cTimeout']);
});

// Protected tenant endpoints
Route::middleware(['auth:sanctum', 'role:tenant'])->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);

    // Tenant profile
    Route::get('profile', [TenantProfileApiController::class, 'show']);
    Route::put('profile', [TenantProfileApiController::class, 'update']);

    // Deposit
    Route::get('deposits', [DepositApiController::class, 'index']);
    Route::get('deposits/{deposit}', [DepositApiController::class, 'show']);

    // Inspections
    Route::get('inspections', [InspectionReportApiController::class, 'index']);
    Route::get('inspections/{inspection}', [InspectionReportApiController::class, 'show']);

    // Maintenance requests
    Route::get('maintenance', [MaintenanceRequestApiController::class, 'index']);
    Route::post('maintenance', [MaintenanceRequestApiController::class, 'store']);
    Route::get('maintenance/{request}', [MaintenanceRequestApiController::class, 'show']);

    // Notifications
    Route::get('notifications', [NotificationApiController::class, 'index']);
    Route::patch('notifications/{notification}/read', [NotificationApiController::class, 'markRead']);
    Route::post('notifications/read-all', [NotificationApiController::class, 'markAllRead']);

    // Messages
    Route::post('messages', [MessageApiController::class, 'create']);           // start new conversation
    Route::get('messages', [MessageApiController::class, 'index']);
    Route::get('messages/{conversation}', [MessageApiController::class, 'show']);
    Route::post('messages/{conversation}', [MessageApiController::class, 'store']); // reply
});
