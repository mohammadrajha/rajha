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

    /**
     * Manual-entry landing page.
     *
     * Only reached when an instructor visits /scan directly. The room QR
     * itself encodes the direct URL (/scan/{roomNo}) so normal scanning
     * never lands here — it goes straight to scanRoom().
     */
    public function showScanPage()
    {
        return view('scan.index');
    }

    /**
     * Directly process attendance for a scanned room code.
     *
     * Triggered when the instructor scans the printed room QR with any
     * camera app: the QR payload is the absolute URL to this route, so the
     * phone opens /scan/{roomNo} in the browser, Laravel enforces the auth
     * middleware (redirecting to login + back via `intended` if needed),
     * and we record attendance in a single round trip.
     *
     * Instructor identity comes from the already-stored user row matched
     * by the authenticated email — we never read identity off the device.
     */
    public function scanRoom(Request $request, string $roomNo)
    {
        $roomNo = trim($roomNo);

        $authUser = Auth::user();

        // Resolve the instructor record by the authenticated email. The
        // admin-managed instructor_emails page is the only place these
        // rows are created, so we rely on that stored mapping exclusively.
        $instructor = User::where('email', $authUser->email)
            ->where('role', 'instructor')
            ->first();

        if (!$instructor || !$instructor->instructor_name) {
            Log::info('Scan rejected: instructor email not linked', [
                'email'   => $authUser->email,
                'room_no' => $roomNo,
            ]);

            return view('scan.result', [
                'result' => [
                    'status'  => 'error',
                    'message' => __('attendance.set_name_first'),
                ],
                'roomNo' => $roomNo,
            ]);
        }

        // Best-effort refresh of this room's schedule from the upstream API
        // so same-day edits land before the lecture-lookup query runs. A
        // failure here is non-fatal: we fall through to the stored rows.
        try {
            $this->roomService->getSchedule($roomNo);
        } catch (\RuntimeException $e) {
            Log::info('Room API refresh failed, using stored schedule', [
                'room_no' => $roomNo,
            ]);
        }

        $result = $this->attendanceService->processAttendance(
            instructorName: $instructor->instructor_name,
            roomNo:         $roomNo,
            ipAddress:      $request->ip(),
            userAgent:      $request->userAgent(),
        );

        $result['instructor_email'] = $instructor->email;

        return view('scan.result', [
            'result' => $result,
            'roomNo' => $roomNo,
        ]);
    }
}
