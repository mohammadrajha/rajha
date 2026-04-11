<?php

namespace App\Http\Controllers\HeadOfDepartment;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\Department;
use App\Models\Instructor;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $department = Department::where('head_user_id', $user->id)->first();

        if (!$department) {
            return view('hod.no-department');
        }

        $instructors = Instructor::where('department_id', $department->id)
            ->where('is_active', true)
            ->get();

        $instructorIds = $instructors->pluck('id');

        $today = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();

        $todayStats = [
            'present' => AttendanceLog::whereIn('instructor_id', $instructorIds)
                ->whereDate('scanned_at', $today)->where('status', 'present')->count(),
            'late' => AttendanceLog::whereIn('instructor_id', $instructorIds)
                ->whereDate('scanned_at', $today)->where('status', 'late')->count(),
            'missed' => AttendanceLog::whereIn('instructor_id', $instructorIds)
                ->whereDate('scanned_at', $today)->where('status', 'missed')->count(),
        ];

        $monthlyStats = [
            'present' => AttendanceLog::whereIn('instructor_id', $instructorIds)
                ->where('scanned_at', '>=', $monthStart)->where('status', 'present')->count(),
            'late' => AttendanceLog::whereIn('instructor_id', $instructorIds)
                ->where('scanned_at', '>=', $monthStart)->where('status', 'late')->count(),
            'missed' => AttendanceLog::whereIn('instructor_id', $instructorIds)
                ->where('scanned_at', '>=', $monthStart)->where('status', 'missed')->count(),
        ];

        $recentAlerts = AttendanceLog::whereIn('instructor_id', $instructorIds)
            ->whereIn('status', ['late', 'missed'])
            ->with(['instructor', 'schedule', 'classroom'])
            ->latest('scanned_at')
            ->take(20)
            ->get();

        // Weekly chart data
        $weeklyData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $weeklyData[] = [
                'date' => $date->format('M d'),
                'present' => AttendanceLog::whereIn('instructor_id', $instructorIds)
                    ->whereDate('scanned_at', $date)->where('status', 'present')->count(),
                'late' => AttendanceLog::whereIn('instructor_id', $instructorIds)
                    ->whereDate('scanned_at', $date)->where('status', 'late')->count(),
                'missed' => AttendanceLog::whereIn('instructor_id', $instructorIds)
                    ->whereDate('scanned_at', $date)->where('status', 'missed')->count(),
            ];
        }

        return view('hod.dashboard', compact(
            'department', 'instructors', 'todayStats', 'monthlyStats', 'recentAlerts', 'weeklyData'
        ));
    }

    public function instructorReport(Instructor $instructor)
    {
        $user = Auth::user();
        $department = Department::where('head_user_id', $user->id)->first();

        if (!$department || $instructor->department_id !== $department->id) {
            abort(403);
        }

        $logs = AttendanceLog::where('instructor_id', $instructor->id)
            ->with(['classroom', 'schedule'])
            ->latest('scanned_at')
            ->paginate(25);

        $stats = [
            'present' => AttendanceLog::where('instructor_id', $instructor->id)->where('status', 'present')->count(),
            'late' => AttendanceLog::where('instructor_id', $instructor->id)->where('status', 'late')->count(),
            'missed' => AttendanceLog::where('instructor_id', $instructor->id)->where('status', 'missed')->count(),
        ];

        return view('hod.instructor-report', compact('instructor', 'logs', 'stats'));
    }
}
