<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HeadOfDepartment;
use App\Http\Controllers\Instructor;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\RoomScheduleController;
use App\Http\Controllers\ScanController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    return auth()->check() ? redirect()->route('home') : redirect()->route('login');
});

// Auth routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Locale switching
Route::get('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

// Home redirect based on role
Route::get('/home', function () {
    return match (auth()->user()->role) {
        'admin' => redirect()->route('admin.dashboard'),
        'head_of_department' => redirect()->route('hod.dashboard'),
        default => redirect()->route('instructor.dashboard'),
    };
})->middleware('auth')->name('home');

// Scan routes (instructor must be logged in).
//
// The primary flow is the direct URL: the room QR encodes
// GET /scan/{roomNo}, so scanning with any camera app opens the page
// and ScanController@scanRoom records attendance immediately.
//
// GET /scan is only a manual fallback landing page for instructors
// who typed the URL without a room code.
Route::middleware('auth')->group(function () {
    Route::get('/scan', [ScanController::class, 'showScanPage'])->name('scan.page');
    Route::get('/scan/{roomNo}', [ScanController::class, 'scanRoom'])
        ->where('roomNo', '[A-Za-z0-9_\-]+')
        ->name('scan.process');
});

// Room schedule lookup (QR scan -> room API)
Route::middleware('auth')->group(function () {
    Route::post('/room-schedule', [RoomScheduleController::class, 'lookup'])->name('room.schedule');
});

// Admin routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');

    // Attendance logs
    Route::get('/attendance', [Admin\AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/{log}', [Admin\AttendanceController::class, 'show'])->name('attendance.show');

    // Department email mapping
    Route::get('/departments', [Admin\DepartmentEmailController::class, 'index'])->name('departments.index');
    Route::get('/departments/create', [Admin\DepartmentEmailController::class, 'create'])->name('departments.create');
    Route::post('/departments', [Admin\DepartmentEmailController::class, 'store'])->name('departments.store');
    Route::get('/departments/{department}/edit', [Admin\DepartmentEmailController::class, 'edit'])->name('departments.edit');
    Route::put('/departments/{department}', [Admin\DepartmentEmailController::class, 'update'])->name('departments.update');

    // Sync rooms from external API
    Route::get('/sync-rooms', [Admin\SyncController::class, 'index'])->name('sync-rooms.index');
    Route::post('/sync-rooms', [Admin\SyncController::class, 'syncRooms'])->name('sync-rooms.run');

    // Room schedules (synced data management)
    Route::get('/room-schedules', [Admin\RoomScheduleController::class, 'index'])->name('room-schedules.index');
    Route::get('/room-schedules/{roomSchedule}/edit', [Admin\RoomScheduleController::class, 'edit'])->name('room-schedules.edit');
    Route::put('/room-schedules/{roomSchedule}', [Admin\RoomScheduleController::class, 'update'])->name('room-schedules.update');
    Route::delete('/room-schedules/{roomSchedule}', [Admin\RoomScheduleController::class, 'destroy'])->name('room-schedules.destroy');

    // Room QR codes
    Route::get('/rooms-qr', [Admin\RoomQrController::class, 'index'])->name('rooms-qr.index');
    Route::get('/rooms-qr/{roomNo}/print', [Admin\RoomQrController::class, 'print'])
        ->where('roomNo', '[A-Za-z0-9_-]+')->name('rooms-qr.print');

    // Instructor email management
    Route::get('/instructor-emails', [Admin\InstructorEmailController::class, 'index'])->name('instructor-emails.index');
    Route::get('/instructor-emails/create', [Admin\InstructorEmailController::class, 'create'])->name('instructor-emails.create');
    Route::post('/instructor-emails', [Admin\InstructorEmailController::class, 'store'])->name('instructor-emails.store');
    Route::get('/instructor-emails/{user}/edit', [Admin\InstructorEmailController::class, 'edit'])->name('instructor-emails.edit');
    Route::put('/instructor-emails/{user}', [Admin\InstructorEmailController::class, 'update'])->name('instructor-emails.update');
    Route::delete('/instructor-emails/{user}', [Admin\InstructorEmailController::class, 'destroy'])->name('instructor-emails.destroy');
});

// Instructor routes
Route::prefix('instructor')->name('instructor.')->middleware(['auth', 'role:instructor'])->group(function () {
    Route::get('/dashboard', [Instructor\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [Instructor\ProfileController::class, 'edit'])->name('profile');
    Route::put('/profile', [Instructor\ProfileController::class, 'update'])->name('profile.update');
});

// Head of Department routes
Route::prefix('hod')->name('hod.')->middleware(['auth', 'role:head_of_department'])->group(function () {
    Route::get('/dashboard', [HeadOfDepartment\DashboardController::class, 'index'])->name('dashboard');
});
