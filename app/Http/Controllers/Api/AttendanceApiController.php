<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\Instructor;
use App\Services\AttendanceService;
use App\Services\QrCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceApiController extends Controller
{
    public function __construct(
        protected QrCodeService $qrService,
        protected AttendanceService $attendanceService,
    ) {}

    public function scan(Request $request): JsonResponse
    {
        $request->validate([
            'payload' => 'required|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $data = $this->qrService->decryptPayload($request->input('payload'));
        if (!$data) {
            return response()->json(['status' => 'error', 'message' => 'Invalid QR code'], 422);
        }

        $classroom = $this->qrService->validatePayload($data);
        if (!$classroom) {
            return response()->json(['status' => 'error', 'message' => 'Invalid QR token'], 422);
        }

        $instructor = Instructor::where('user_id', $request->user()->id)->first();
        if (!$instructor) {
            return response()->json(['status' => 'error', 'message' => 'Not an instructor'], 403);
        }

        $result = $this->attendanceService->processAttendance(
            instructor: $instructor,
            classroom: $classroom,
            latitude: $request->input('latitude'),
            longitude: $request->input('longitude'),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return response()->json($result);
    }

    public function history(Request $request): JsonResponse
    {
        $instructor = Instructor::where('user_id', $request->user()->id)->first();
        if (!$instructor) {
            return response()->json(['status' => 'error', 'message' => 'Not an instructor'], 403);
        }

        $logs = AttendanceLog::where('instructor_id', $instructor->id)
            ->with(['classroom', 'schedule'])
            ->latest('scanned_at')
            ->paginate(20);

        return response()->json($logs);
    }

    public function todaySchedule(Request $request): JsonResponse
    {
        $instructor = Instructor::where('user_id', $request->user()->id)->first();
        if (!$instructor) {
            return response()->json(['status' => 'error', 'message' => 'Not an instructor'], 403);
        }

        $schedules = $instructor->todaySchedules()->with('classroom')->get();

        return response()->json(['data' => $schedules]);
    }
}
