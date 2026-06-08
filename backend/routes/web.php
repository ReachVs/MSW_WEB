<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminBookingController;
use App\Http\Controllers\Admin\AdminCalendarController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminInventoryController;
use App\Http\Controllers\Admin\AdminQueueController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', fn () => redirect()->route('admin.login'))->name('login');

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/login', fn () => view('admin.login'))->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.post');

    Route::middleware('auth')->group(function (): void {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/calendar', [AdminCalendarController::class, 'index'])->name('calendar');
        Route::patch('/calendar/settings', [AdminCalendarController::class, 'updateSettings'])
            ->name('calendar.settings');
        Route::get('/queue', [AdminQueueController::class, 'index'])->name('queue');
        Route::get('/inventory', [AdminInventoryController::class, 'index'])->name('inventory');
        Route::get('/bookings/create', [AdminBookingController::class, 'create'])
            ->name('bookings.create');
        Route::post('/bookings', [AdminBookingController::class, 'store'])
            ->name('bookings.store');
        Route::patch('/bookings/{booking}/status', [AdminBookingController::class, 'updateStatus'])
            ->name('bookings.status');
        Route::delete('/bookings/{booking}', [AdminBookingController::class, 'destroy'])
            ->name('bookings.destroy');
    });
});
