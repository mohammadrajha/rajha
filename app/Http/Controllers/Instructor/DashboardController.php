<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\Instructor;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $instructor = Instructor::where('user_id', $user->id)->first();

        if (!$instructor) {
            return view('instructor.no-profile');
        }

        $today = Carbon::today();
        $dayOfWeek = $today->dayOfWeek;

        $todaySchedules = Schedule::where('instructor_id', $instructor->id)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->with('classroom')
            ->orderBy('start_time')
            ->get();

        // Mark which schedules have been attended today
        $todayAttendance = AttendanceLog::where('instructor_id', $instructor->id)
            ->whereDate('scanned_at', $today)
            ->whereIn('status', ['present', 'late'])
            ->pluck('schedule_id')
            ->toArray();

        $recentLogs = AttendanceLog::where('instructor_id', $instructor->id)
            ->with(['classroom', 'schedule'])
            ->latest('scanned_at')
            ->take(10)
            ->get();

        // Monthly stats
        $monthStart = Carbon::now()->startOfMonth();
        $monthlyStats = [
            'present' => AttendanceLog::where('instructor_id', $instructor->id)
                ->where('scanned_at', '>=', $monthStart)
                ->where('status', 'present')->count(),
            'late' => AttendanceLog::where('instructor_id', $instructor->id)
                ->where('scanned_at', '>=', $monthStart)
                ->where('status', 'late')->count(),
            'missed' => AttendanceLog::where('instructor_id', $instructor->id)
                ->where('scanned_at', '>=', $monthStart)
                ->where('status', 'missed')->count(),
        ];

        return view('instructor.dashboard', compact(
            'instructor', 'todaySchedules', 'todayAttendance', 'recentLogs', 'monthlyStats'
        ));
    }
}
