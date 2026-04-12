<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RoomSchedule;
use SimpleSoftwareIO\QrCode\Generator;

class RoomQrController extends Controller
{
    public function index()
    {
        // Distinct classrooms by public ROOM_CODE, with one representative room_desc each.
        $rooms = RoomSchedule::currentSemester()
            ->selectRaw('room_no, MIN(room_desc) as room_desc')
            ->groupBy('room_no')
            ->orderBy('room_no')
            ->get();

        $qr = new Generator();
        $qrCodes = [];
        foreach ($rooms as $room) {
            $qrCodes[$room->room_no] = $qr->format('svg')->size(180)->margin(1)->generate((string) $room->room_no);
        }

        return view('admin.rooms-qr.index', compact('rooms', 'qrCodes'));
    }

    public function print(string $roomNo)
    {
        $schedule = RoomSchedule::currentSemester()->where('room_no', $roomNo)->first();
        abort_unless($schedule !== null, 404);

        $svg = (new Generator())->format('svg')->size(400)->margin(2)->generate((string) $roomNo);

        return view('admin.rooms-qr.print', [
            'roomNo'   => $roomNo,
            'roomDesc' => $schedule->room_desc,
            'svg'      => $svg,
        ]);
    }
}
