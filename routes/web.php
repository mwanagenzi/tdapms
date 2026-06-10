<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandlordController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\CaretakerController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\LeaseController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\DepositDeductionController;
use App\Http\Controllers\InspectionReportController;
use App\Http\Controllers\MaintenanceRequestController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard (role-aware)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Super Admin — landlord management
    Route::middleware(['role:super_admin'])->group(function () {
        Route::resource('landlords', LandlordController::class);
    });

    // Landlord — property setup, reports, deduction approvals
    Route::middleware(['role:super_admin|landlord'])->group(function () {
        Route::resource('properties', PropertyController::class);
        Route::resource('units', UnitController::class);
        Route::resource('caretakers', CaretakerController::class);
        Route::resource('reports', ReportController::class)->only(['index', 'show']);
    });

    // Landlord + Caretaker — shared operational features
    Route::middleware(['role:super_admin|landlord|caretaker'])->group(function () {
        Route::resource('tenants', TenantController::class);
        Route::resource('leases', LeaseController::class);

        // Deposits & Escrow
        Route::resource('deposits', DepositController::class);
        Route::post('deposits/{deposit}/initiate-collection', [DepositController::class, 'initiateCollection'])
            ->name('deposits.initiate-collection');
        Route::post('deposits/{deposit}/initiate-refund', [DepositController::class, 'initiateRefund'])
            ->name('deposits.initiate-refund');

        // Deposit Deductions
        Route::resource('deposit-deductions', DepositDeductionController::class);
        Route::post('deposit-deductions/{deduction}/approve', [DepositDeductionController::class, 'approve'])
            ->name('deposit-deductions.approve');
        Route::post('deposit-deductions/{deduction}/reject', [DepositDeductionController::class, 'reject'])
            ->name('deposit-deductions.reject');

        // Inspection Reports
        Route::resource('inspections', InspectionReportController::class);
        Route::post('inspections/{inspection}/complete', [InspectionReportController::class, 'complete'])
            ->name('inspections.complete');

        // Maintenance Requests
        Route::resource('maintenance', MaintenanceRequestController::class);
        Route::post('maintenance/{request}/update-status', [MaintenanceRequestController::class, 'updateStatus'])
            ->name('maintenance.update-status');

        // Messages
        Route::resource('messages', MessageController::class)->only(['index', 'show', 'store']);
        Route::post('messages/conversation/lease/{lease}', [MessageController::class, 'startConversation'])
            ->name('messages.start-conversation');
    });
});

require __DIR__.'/settings.php';
