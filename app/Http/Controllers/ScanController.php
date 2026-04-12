<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AttendanceService;
use App\Services\RoomScheduleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
     * Called when an authenticated instructor scans a room QR and confirms attendance.
     *
     * The authenticated user's personal email is the identity key. We resolve the
     * matching instructor record (users.email -> users.instructor_name) and pass the
     * instructor_name into the attendance service so it can be matched against the
     * API-sourced room_schedules table.
     */
    public function markAttendance(Request $request)
    {
        $request->validate([
            'room_no' => 'required|integer|min:1',
        ]);

        $authUser = Auth::user();

        // Identify the instructor strictly by their authenticated email.
        $instructor = User::where('email', $authUser->email)
            ->where('role', 'instructor')
            ->first();

        if (!$instructor || !$instructor->instructor_name) {
            Log::info('Scan rejected: instructor email not linked', ['email' => $authUser->email]);

            if ($request->expectsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => __('attendance.set_name_first'),
                ], 422);
            }
            return redirect()->route('instructor.profile')
                ->with('error', __('attendance.set_name_first'));
        }

        $roomNo = (int) $request->input('room_no');

        // Refresh room data from API when available; fall back to stored rows on failure.
        try {
            $this->roomService->getSchedule($roomNo);
        } catch (\RuntimeException $e) {
            Log::info('Room API refresh failed, using stored schedule', ['room_no' => $roomNo]);
        }

        $result = $this->attendanceService->processAttendance(
            instructorName: $instructor->instructor_name,
            roomNo: $roomNo,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        $result['instructor_email'] = $instructor->email;

        if ($request->expectsJson()) {
            return response()->json($result);
        }

        return view('scan.result', [
            'result' => $result,
            'roomNo' => $roomNo,
        ]);
    }
}
