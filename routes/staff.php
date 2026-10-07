<?php

use App\Http\Controllers\Staff\AccountController;
use App\Http\Controllers\Staff\AppointmentController;
use App\Http\Controllers\Staff\DashboardController;
use App\Http\Controllers\Staff\HistoryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:staff'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('history', [HistoryController::class, 'index'])->name('history');

    Route::patch('appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])
        ->name('appointments.update-status');

    Route::get('account', [AccountController::class, 'show'])->name('account');
    Route::put('account/password', [AccountController::class, 'updatePassword'])->name('account.password');
});
