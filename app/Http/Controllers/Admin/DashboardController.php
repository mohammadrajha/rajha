<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\DepartmentEmail;
use App\Models\RoomSchedule;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        $stats = [
            'total_rooms' => RoomSchedule::currentSemester()->distinct('room_no')->count('room_no'),
            'total_lectures' => RoomSchedule::currentSemester()->count(),
            'total_departments' => DepartmentEmail::count(),
            'total_instructors' => RoomSchedule::currentSemester()->distinct('instructor_name')->count('instructor_name'),

            'today_present' => AttendanceLog::whereDate('scanned_at', $today)->where('status', 'present')->count(),
            'today_late' => AttendanceLog::whereDate('scanned_at', $today)->where('status', 'late')->count(),
            'today_missed' => AttendanceLog::whereDate('scanned_at', $today)->where('status', 'missed')->count(),
            'today_wrong' => AttendanceLog::whereDate('scanned_at', $today)->where('status', 'wrong_classroom')->count(),
        ];

        $recentLogs = AttendanceLog::latest('scanned_at')->take(20)->get();

        // Weekly stats for chart
        $weeklyStats = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $weeklyStats[] = [
                'date' => $date->format('M d'),
                'present' => AttendanceLog::whereDate('scanned_at', $date)->where('status', 'present')->count(),
                'late' => AttendanceLog::whereDate('scanned_at', $date)->where('status', 'late')->count(),
                'missed' => AttendanceLog::whereDate('scanned_at', $date)->where('status', 'missed')->count(),
            ];
        }

        return view('admin.dashboard', compact('stats', 'recentLogs', 'weeklyStats'));
    }
}
