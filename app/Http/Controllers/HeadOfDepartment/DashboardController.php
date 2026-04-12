<?php

namespace App\Http\Controllers\HeadOfDepartment;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\DepartmentEmail;
use App\Models\RoomSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Find department(s) where this user's email is the head_email
        $departments = DepartmentEmail::where('head_email', $user->email)->get();

        if ($departments->isEmpty()) {
            return view('hod.no-department');
        }

        $deptNos = $departments->pluck('dept_no')->toArray();

        $today = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();

        // Instructors in these departments
        $instructorNames = RoomSchedule::whereIn('dept_no', $deptNos)
            ->currentSemester()
            ->distinct()
            ->pluck('instructor_name');

        $todayStats = [
            'present' => AttendanceLog::whereIn('dept_no', $deptNos)
                ->whereDate('scanned_at', $today)->where('status', 'present')->count(),
            'late' => AttendanceLog::whereIn('dept_no', $deptNos)
                ->whereDate('scanned_at', $today)->where('status', 'late')->count(),
            'missed' => AttendanceLog::whereIn('dept_no', $deptNos)
                ->whereDate('scanned_at', $today)->where('status', 'missed')->count(),
        ];

        $monthlyStats = [
            'present' => AttendanceLog::whereIn('dept_no', $deptNos)
                ->where('scanned_at', '>=', $monthStart)->where('status', 'present')->count(),
            'late' => AttendanceLog::whereIn('dept_no', $deptNos)
                ->where('scanned_at', '>=', $monthStart)->where('status', 'late')->count(),
            'missed' => AttendanceLog::whereIn('dept_no', $deptNos)
                ->where('scanned_at', '>=', $monthStart)->where('status', 'missed')->count(),
        ];

        $recentAlerts = AttendanceLog::whereIn('dept_no', $deptNos)
            ->whereIn('status', ['late', 'missed'])
            ->latest('scanned_at')
            ->take(20)
            ->get();

        // Weekly chart data
        $weeklyData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $weeklyData[] = [
                'date' => $date->format('M d'),
                'present' => AttendanceLog::whereIn('dept_no', $deptNos)
                    ->whereDate('scanned_at', $date)->where('status', 'present')->count(),
                'late' => AttendanceLog::whereIn('dept_no', $deptNos)
                    ->whereDate('scanned_at', $date)->where('status', 'late')->count(),
                'missed' => AttendanceLog::whereIn('dept_no', $deptNos)
                    ->whereDate('scanned_at', $date)->where('status', 'missed')->count(),
            ];
        }

        return view('hod.dashboard', compact(
            'departments', 'instructorNames', 'todayStats', 'monthlyStats', 'recentAlerts', 'weeklyData'
        ));
    }
}
