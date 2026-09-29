<?php

use App\Http\Controllers\Admin\AppointmentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\StaffController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('appointments', [AppointmentController::class, 'index'])->name('appointments.index');
    Route::patch('appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])
        ->name('appointments.update-status');

    Route::get('services', [ServiceController::class, 'index'])->name('services.index');
    Route::get('staff', [StaffController::class, 'index'])->name('staff.index');
    Route::get('schedules', [ScheduleController::class, 'index'])->name('schedules.index');
});
