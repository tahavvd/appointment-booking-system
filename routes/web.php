<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Booking\BookingController;

Route::get('/', function () {
    return redirect()->route('booking.start');
});

Route::get('/home', function () {
    return 'Logged in.';
})->middleware('auth')->name('home');

Route::get('/book', [BookingController::class, 'create'])->name('booking.start');
Route::post('/book', [BookingController::class, 'store'])->name('booking.store');
Route::get('/book/service', [BookingController::class, 'selectService'])->name('booking.service');
Route::post('/book/service', [BookingController::class, 'storeService'])->name('booking.service.store');
Route::get('/book/staff', [BookingController::class, 'selectStaff'])->name('booking.staff');
Route::post('/book/staff', [BookingController::class, 'storeStaff'])->name('booking.staff.store');
Route::get('/book/slots', [BookingController::class, 'selectSlot'])->name('booking.slots');
Route::post('/book/confirm', [BookingController::class, 'storeAppointment'])->name('booking.confirm');
Route::get('/book/confirmation', [BookingController::class, 'confirmation'])->name('booking.confirmation');
Route::get('/my-appointments', [BookingController::class, 'myAppointments'])->name('booking.my-appointments');
Route::post('/my-appointments/{appointment}/cancel', [BookingController::class, 'cancelAppointment'])->name('booking.appointment.cancel');
Route::post('/book/logout', [BookingController::class, 'logout'])->name('booking.logout');


require __DIR__ . '/auth.php';
