<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = AttendanceLog::latest('scanned_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('instructor_name')) {
            $query->where('instructor_name', 'like', "%{$request->instructor_name}%");
        }

        if ($request->filled('room_no')) {
            $query->where('room_no', $request->room_no);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('scanned_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('scanned_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(25)->withQueryString();

        return view('admin.attendance.index', compact('logs'));
    }

    public function show(AttendanceLog $log)
    {
        return view('admin.attendance.show', compact('log'));
    }
}
