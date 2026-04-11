<?php

use App\Http\Controllers\Api\AttendanceApiController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/scan', [AttendanceApiController::class, 'scan']);
    Route::get('/attendance/history', [AttendanceApiController::class, 'history']);
    Route::get('/schedule/today', [AttendanceApiController::class, 'todaySchedule']);
});
