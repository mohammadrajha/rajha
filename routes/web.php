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

// Scan routes (instructor must be logged in)
Route::middleware('auth')->group(function () {
    Route::get('/scan', [ScanController::class, 'showScanPage'])->name('scan.page');
    Route::post('/scan', [ScanController::class, 'process'])->name('scan.submit');
    Route::get('/scan/{payload}', [ScanController::class, 'processFromUrl'])->name('scan.process');
});

// Room schedule lookup (QR scan -> room API)
Route::middleware('auth')->group(function () {
    Route::post('/room-schedule', [RoomScheduleController::class, 'lookup'])->name('room.schedule');
});

// Admin routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');

    // Classrooms
    Route::resource('classrooms', Admin\ClassroomController::class);
    Route::get('/classrooms/{classroom}/print-qr', [Admin\ClassroomController::class, 'printQr'])->name('classrooms.print-qr');
    Route::post('/classrooms/{classroom}/regenerate-token', [Admin\ClassroomController::class, 'regenerateToken'])->name('classrooms.regenerate-token');

    // Attendance logs
    Route::get('/attendance', [Admin\AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/{log}', [Admin\AttendanceController::class, 'show'])->name('attendance.show');

    // Sync
    Route::get('/sync', [Admin\SyncController::class, 'index'])->name('sync.index');
    Route::post('/sync/now', [Admin\SyncController::class, 'syncNow'])->name('sync.now');
});

// Instructor routes
Route::prefix('instructor')->name('instructor.')->middleware(['auth', 'role:instructor'])->group(function () {
    Route::get('/dashboard', [Instructor\DashboardController::class, 'index'])->name('dashboard');
});

// Head of Department routes
Route::prefix('hod')->name('hod.')->middleware(['auth', 'role:head_of_department'])->group(function () {
    Route::get('/dashboard', [HeadOfDepartment\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/instructor/{instructor}', [HeadOfDepartment\DashboardController::class, 'instructorReport'])->name('instructor.report');
});
