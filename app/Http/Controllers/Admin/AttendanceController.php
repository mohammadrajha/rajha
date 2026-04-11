<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\Department;
use App\Models\Instructor;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = AttendanceLog::with(['instructor', 'classroom', 'schedule'])
            ->latest('scanned_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('instructor_id')) {
            $query->where('instructor_id', $request->instructor_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('scanned_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('scanned_at', '<=', $request->date_to);
        }

        if ($request->filled('department_id')) {
            $query->whereHas('instructor', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        $logs = $query->paginate(25)->withQueryString();
        $instructors = Instructor::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();

        return view('admin.attendance.index', compact('logs', 'instructors', 'departments'));
    }

    public function show(AttendanceLog $log)
    {
        $log->load(['instructor', 'classroom', 'schedule']);
        return view('admin.attendance.show', compact('log'));
    }
}
