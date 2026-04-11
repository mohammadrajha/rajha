<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\Classroom;
use App\Models\Department;
use App\Models\Instructor;
use App\Models\Schedule;
use App\Models\SyncLog;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        $stats = [
            'total_instructors' => Instructor::where('is_active', true)->count(),
            'total_classrooms' => Classroom::where('is_active', true)->count(),
            'total_departments' => Department::count(),
            'total_schedules' => Schedule::where('is_active', true)->count(),

            'today_present' => AttendanceLog::whereDate('scanned_at', $today)->where('status', 'present')->count(),
            'today_late' => AttendanceLog::whereDate('scanned_at', $today)->where('status', 'late')->count(),
            'today_missed' => AttendanceLog::whereDate('scanned_at', $today)->where('status', 'missed')->count(),
            'today_wrong' => AttendanceLog::whereDate('scanned_at', $today)->where('status', 'wrong_classroom')->count(),
        ];

        $recentLogs = AttendanceLog::with(['instructor', 'classroom', 'schedule'])
            ->latest('scanned_at')
            ->take(20)
            ->get();

        $lastSync = SyncLog::latest()->first();

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

        return view('admin.dashboard', compact('stats', 'recentLogs', 'lastSync', 'weeklyStats'));
    }
}
