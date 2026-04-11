<?php

namespace App\Http\Controllers;

use App\Models\Instructor;
use App\Services\AttendanceService;
use App\Services\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScanController extends Controller
{
    public function __construct(
        protected QrCodeService $qrService,
        protected AttendanceService $attendanceService,
    ) {}

    public function showScanPage()
    {
        return view('scan.index');
    }

    public function process(Request $request)
    {
        $request->validate([
            'payload' => 'required|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        // Decrypt and validate QR payload
        $data = $this->qrService->decryptPayload($request->input('payload'));
        if (!$data) {
            return $this->respondError(__('attendance.invalid_qr'), $request);
        }

        $classroom = $this->qrService->validatePayload($data);
        if (!$classroom) {
            return $this->respondError(__('attendance.invalid_qr_token'), $request);
        }

        // Get instructor from logged-in user
        $user = Auth::user();
        $instructor = Instructor::where('user_id', $user->id)->first();

        if (!$instructor) {
            return $this->respondError(__('attendance.not_instructor'), $request);
        }

        // Process attendance
        $result = $this->attendanceService->processAttendance(
            instructor: $instructor,
            classroom: $classroom,
            latitude: $request->input('latitude'),
            longitude: $request->input('longitude'),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        if ($request->expectsJson()) {
            return response()->json($result);
        }

        return view('scan.result', [
            'result' => $result,
            'classroom' => $classroom,
            'instructor' => $instructor,
        ]);
    }

    public function processFromUrl(Request $request, string $payload)
    {
        $request->merge(['payload' => $payload]);
        return $this->process($request);
    }

    protected function respondError(string $message, Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json(['status' => 'error', 'message' => $message], 422);
        }

        return view('scan.result', [
            'result' => ['status' => 'error', 'message' => $message],
            'classroom' => null,
            'instructor' => null,
        ]);
    }
}
