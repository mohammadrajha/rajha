<?php

namespace App\Http\Controllers;

use App\Services\AttendanceService;
use App\Services\RoomScheduleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScanController extends Controller
{
    public function __construct(
        protected RoomScheduleService $roomService,
        protected AttendanceService $attendanceService,
    ) {}

    public function showScanPage()
    {
        return view('scan.index');
    }

    /**
     * Called when instructor scans QR and confirms attendance.
     */
    public function markAttendance(Request $request)
    {
        $request->validate([
            'room_no' => 'required|integer|min:1',
        ]);

        $user = Auth::user();

        if (!$user->instructor_name) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => __('attendance.set_name_first'),
                ], 422);
            }
            return redirect()->route('instructor.profile')
                ->with('error', __('attendance.set_name_first'));
        }

        $roomNo = (int) $request->input('room_no');

        // Ensure we have fresh schedule data for this room
        try {
            $this->roomService->getSchedule($roomNo);
        } catch (\RuntimeException $e) {
            // If API fails, try using stored data — it may still work
        }

        $result = $this->attendanceService->processAttendance(
            instructorName: $user->instructor_name,
            roomNo: $roomNo,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        if ($request->expectsJson()) {
            return response()->json($result);
        }

        return view('scan.result', [
            'result' => $result,
            'roomNo' => $roomNo,
        ]);
    }
}
