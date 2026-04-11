<?php

namespace App\Http\Controllers;

use App\Services\RoomScheduleService;
use Illuminate\Http\Request;

class RoomScheduleController extends Controller
{
    public function __construct(
        protected RoomScheduleService $roomService,
    ) {}

    /**
     * Called via AJAX after QR scan or manual room number entry.
     */
    public function lookup(Request $request)
    {
        $request->validate([
            'room_no' => 'required|integer|min:1',
        ]);

        $roomNo = (int) $request->input('room_no');

        try {
            $schedules = $this->roomService->getSchedule($roomNo);
        } catch (\RuntimeException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ], 502);
            }
            return view('scan.room-schedule', [
                'roomNo' => $roomNo,
                'schedules' => [],
                'error' => $e->getMessage(),
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'room_no' => $roomNo,
                'schedules' => $schedules,
            ]);
        }

        return view('scan.room-schedule', [
            'roomNo' => $roomNo,
            'schedules' => $schedules,
            'error' => null,
        ]);
    }
}
