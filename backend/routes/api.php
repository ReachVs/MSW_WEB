<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CalendarController;
use App\Http\Controllers\Api\V1\Admin\CalendarController as AdminCalendarController;
use App\Http\Controllers\Api\V1\Admin\BookingManagementController;
use App\Http\Controllers\Api\V1\BookingController;
use App\Http\Controllers\Api\V1\ServicesController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => ['status' => 'ok']);

// Explicitly define login outside the group to ensure no inherited middleware
Route::post('/auth/login', [AuthController::class, 'login']);
Route::get('/calendar/available-slots', [CalendarController::class, 'availableSlots']);
Route::get('/calendar/month', [CalendarController::class, 'monthAvailability']);

Route::prefix('auth')->group(function (): void {
    Route::post('/register', [AuthController::class, 'register']);
    // Route::post('/login', [AuthController::class, 'login']); // This line is removed from here

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('bookings/my', [BookingController::class, 'my']);
    Route::get('bookings/active', [BookingController::class, 'active']);
    Route::get('bookings/history', [BookingController::class, 'history']);
    Route::get('bookings/vehicles', [BookingController::class, 'userVehicles']);
    Route::put('bookings/{booking}/cancel', [BookingController::class, 'cancel']);
    Route::delete('bookings/{booking}', [BookingController::class, 'destroy'])->whereNumber('booking');
    Route::get('bookings/{booking}', [BookingController::class, 'show'])->whereNumber('booking');
    Route::apiResource('bookings', BookingController::class)->only(['index', 'store']);

    Route::prefix('admin')->group(function (): void {
        Route::get('bookings', [BookingManagementController::class, 'index']);
        Route::get('bookings/{booking}', [BookingManagementController::class, 'show']);
        Route::put('bookings/{booking}/status', [BookingManagementController::class, 'updateStatus']);
        Route::delete('bookings/{booking}', [BookingManagementController::class, 'destroy']);
        Route::get('calendar', [AdminCalendarController::class, 'index']);
        Route::get('calendar/day/{date}', [AdminCalendarController::class, 'day']);
    });
});

// Public catalog services endpoint
Route::get('/services', [ServicesController::class, 'index']);
Route::get('/services/categories', [ServicesController::class, 'categories']);
Route::get('/services/{service}', [ServicesController::class, 'show']);
