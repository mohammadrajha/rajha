<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\RoomSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected const DAY_MAP = [
        0 => 'الأحد',
        1 => 'الإثنين',
        2 => 'الثلاثاء',
        3 => 'الأربعاء',
        4 => 'الخميس',
        5 => 'الجمعة',
        6 => 'السبت',
    ];

    public function index()
    {
        $user = Auth::user();

        if (!$user->instructor_name) {
            return redirect()->route('instructor.profile');
        }

        $instructorName = $user->instructor_name;
        $today = Carbon::today();
        $currentDay = self::DAY_MAP[$today->dayOfWeek] ?? '';

        // Today's schedule from stored room_schedules
        $todaySchedules = RoomSchedule::where('instructor_name', $instructorName)
            ->where('day', $currentDay)
            ->currentSemester()
            ->orderBy('start_time')
            ->get();

        // Today's attendance records
        $todayAttendance = AttendanceLog::where('instructor_name', $instructorName)
            ->whereDate('scanned_at', $today)
            ->whereIn('status', ['present', 'late'])
            ->pluck('room_schedule_id')
            ->toArray();

        // Recent logs
        $recentLogs = AttendanceLog::where('instructor_name', $instructorName)
            ->latest('scanned_at')
            ->take(10)
            ->get();

        // Monthly stats
        $monthStart = Carbon::now()->startOfMonth();
        $monthlyStats = [
            'present' => AttendanceLog::where('instructor_name', $instructorName)
                ->where('scanned_at', '>=', $monthStart)->where('status', 'present')->count(),
            'late' => AttendanceLog::where('instructor_name', $instructorName)
                ->where('scanned_at', '>=', $monthStart)->where('status', 'late')->count(),
            'missed' => AttendanceLog::where('instructor_name', $instructorName)
                ->where('scanned_at', '>=', $monthStart)->where('status', 'missed')->count(),
        ];

        return view('instructor.dashboard', compact(
            'user', 'instructorName', 'todaySchedules', 'todayAttendance', 'recentLogs', 'monthlyStats'
        ));
    }
}
