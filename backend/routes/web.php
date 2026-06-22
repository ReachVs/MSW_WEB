<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminBookingController;
use App\Http\Controllers\Admin\AdminCalendarController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminInventoryController;
use App\Http\Controllers\Admin\AdminMechanicController;
use App\Http\Controllers\Admin\AdminQueueController;
use App\Http\Controllers\MechanicPortalController;
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
        Route::get('/queue/sync', [AdminQueueController::class, 'sync'])->name('queue.sync');
        Route::get('/inventory', [AdminInventoryController::class, 'index'])->name('inventory');
        Route::get('/inventory/logs', [AdminInventoryController::class, 'logs'])->name('inventory.logs');
        Route::get('/inventory/parts/{part}', [AdminInventoryController::class, 'show'])->name('inventory.show');
        Route::post('/inventory', [AdminInventoryController::class, 'store'])->name('inventory.store');
        Route::patch('/inventory/{part}', [AdminInventoryController::class, 'update'])->name('inventory.update');
        Route::delete('/inventory/{part}', [AdminInventoryController::class, 'destroy'])->name('inventory.destroy');
        Route::post('/inventory/parts/{part}/stock-in', [AdminInventoryController::class, 'stockIn'])->name('inventory.stock-in');
        Route::post('/inventory/parts/{part}/stock-out', [AdminInventoryController::class, 'stockOut'])->name('inventory.stock-out');
        Route::post('/inventory/parts/{part}/adjust', [AdminInventoryController::class, 'adjust'])->name('inventory.adjust');

        Route::get('/mechanics', [AdminMechanicController::class, 'index'])->name('mechanics');
        Route::post('/mechanics', [AdminMechanicController::class, 'store'])->name('mechanics.store');
        Route::patch('/mechanics/{mechanic}', [AdminMechanicController::class, 'update'])->name('mechanics.update');
        Route::delete('/mechanics/{mechanic}', [AdminMechanicController::class, 'destroy'])->name('mechanics.destroy');
        Route::get('/bookings/create', [AdminBookingController::class, 'create'])
            ->name('bookings.create');
        Route::post('/bookings', [AdminBookingController::class, 'store'])
            ->name('bookings.store');
        Route::patch('/bookings/{booking}/status', [AdminBookingController::class, 'updateStatus'])
            ->name('bookings.status');
        Route::patch('/bookings/{booking}/mechanic', [AdminBookingController::class, 'updateMechanic'])
            ->name('bookings.mechanic');
        Route::delete('/bookings/{booking}', [AdminBookingController::class, 'destroy'])
            ->name('bookings.destroy');
    });
});

Route::prefix('mechanic')->name('mechanic.')->group(function (): void {
    Route::get('/', [MechanicPortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/calendar', [MechanicPortalController::class, 'calendar'])->name('calendar');
    Route::get('/queue', [MechanicPortalController::class, 'queue'])->name('queue');
    Route::get('/queue/sync', [MechanicPortalController::class, 'queueSync'])->name('queue.sync');
    Route::get('/mechanics', [MechanicPortalController::class, 'mechanics'])->name('mechanics');
    Route::get('/inventory', [MechanicPortalController::class, 'inventory'])->name('inventory');
    Route::get('/inventory/logs', [MechanicPortalController::class, 'inventoryLogs'])->name('inventory.logs');
    Route::get('/inventory/parts/{part}', [MechanicPortalController::class, 'inventoryShow'])->name('inventory.show');
    Route::patch('/bookings/{booking}/status', [MechanicPortalController::class, 'updateStatus'])
        ->name('bookings.status');
    Route::patch('/bookings/{booking}/mechanic', [MechanicPortalController::class, 'updateMechanic'])
        ->name('bookings.mechanic');
});
