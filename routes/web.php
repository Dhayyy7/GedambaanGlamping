<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FacilityController;
use App\Http\Controllers\Admin\ExtraFacilityController;
use App\Http\Controllers\Admin\HolidayController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\RoomMaintenanceController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;

use App\Http\Controllers\LandingController;

// Homepage Landing Page
Route::get('/', [LandingController::class, 'index'])->name('home');
Route::get('/check-room', [LandingController::class, 'checkRoom'])->name('check-room');
Route::get('/room/{room}', [LandingController::class, 'booking'])->name('booking');
Route::post('/room/booking/store', [LandingController::class, 'storeBooking'])->name('booking.store');

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');

// Protected Admin Routes
Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Analytics Dashboard Route (Accessible to All Auth Admin Users/Staff)
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/dashboard/report/pdf', [DashboardController::class, 'reportPdf'])->name('admin.dashboard.report.pdf');
    Route::get('/admin/dashboard/report/excel', [DashboardController::class, 'reportExcel'])->name('admin.dashboard.report.excel');

    // Room Details Route (Accessible to All Auth Users/Staff)
    Route::get('/admin/rooms/details', [RoomController::class, 'details'])->name('admin.rooms.details');

    // Custom Holiday Routes
    Route::post('/admin/holidays', [HolidayController::class, 'store'])->name('admin.holidays.store');
    Route::delete('/admin/holidays/{holiday}', [HolidayController::class, 'destroy'])->name('admin.holidays.destroy');

    // Room Maintenance Routes
    Route::post('/admin/room-maintenances', [RoomMaintenanceController::class, 'store'])->name('admin.room-maintenances.store');
    Route::delete('/admin/room-maintenances/{maintenance}', [RoomMaintenanceController::class, 'destroy'])->name('admin.room-maintenances.destroy');

    // Booking Management Routes (Accessible to Auth Users/Staff)
    Route::get('/admin/bookings', [BookingController::class, 'index'])->name('admin.bookings.index');
    Route::post('/admin/bookings', [BookingController::class, 'store'])->name('admin.bookings.store');
    Route::put('/admin/bookings/{booking}', [BookingController::class, 'update'])->name('admin.bookings.update');
    Route::patch('/admin/bookings/{booking}/lunas', [BookingController::class, 'markAsLunas'])->name('admin.bookings.mark-lunas');
    Route::get('/admin/bookings/{booking}/receipt', [BookingController::class, 'receipt'])->name('admin.bookings.receipt');
    Route::delete('/admin/bookings/{booking}', [BookingController::class, 'destroy'])->name('admin.bookings.destroy');

    // Super Admin Only Routes
    Route::middleware(['superadmin'])->group(function () {
        // Room & Unit Management Routes
        Route::get('/admin/rooms', [RoomController::class, 'index'])->name('admin.rooms.index');
        Route::post('/admin/rooms', [RoomController::class, 'store'])->name('admin.rooms.store');
        Route::put('/admin/rooms/{room}', [RoomController::class, 'update'])->name('admin.rooms.update');
        Route::delete('/admin/rooms/{room}', [RoomController::class, 'destroy'])->name('admin.rooms.destroy');

        // Master Facility Routes
        Route::get('/admin/facilities', [FacilityController::class, 'index'])->name('admin.facilities.index');
        Route::post('/admin/facilities', [FacilityController::class, 'store'])->name('admin.facilities.store');
        Route::put('/admin/facilities/{facility}', [FacilityController::class, 'update'])->name('admin.facilities.update');
        Route::delete('/admin/facilities/{facility}', [FacilityController::class, 'destroy'])->name('admin.facilities.destroy');

        // Master Extra Facility Routes
        Route::get('/admin/extra-facilities', [ExtraFacilityController::class, 'index'])->name('admin.extra-facilities.index');
        Route::post('/admin/extra-facilities', [ExtraFacilityController::class, 'store'])->name('admin.extra-facilities.store');
        Route::put('/admin/extra-facilities/{extraFacility}', [ExtraFacilityController::class, 'update'])->name('admin.extra-facilities.update');
        Route::delete('/admin/extra-facilities/{extraFacility}', [ExtraFacilityController::class, 'destroy'])->name('admin.extra-facilities.destroy');

        // Settings / Pengaturan Routes
        Route::get('/admin/settings', [SettingController::class, 'index'])->name('admin.settings.index');
        Route::post('/admin/settings', [SettingController::class, 'update'])->name('admin.settings.update');

        // User Management Routes
        Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users.index');
        Route::post('/admin/users', [UserController::class, 'store'])->name('admin.users.store');
        Route::put('/admin/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
        Route::delete('/admin/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');

        // Role Management Routes
        Route::get('/admin/roles', [RoleController::class, 'index'])->name('admin.roles.index');
        Route::post('/admin/roles', [RoleController::class, 'store'])->name('admin.roles.store');
        Route::put('/admin/roles/{role}', [RoleController::class, 'update'])->name('admin.roles.update');
        Route::delete('/admin/roles/{role}', [RoleController::class, 'destroy'])->name('admin.roles.destroy');
    });
});
