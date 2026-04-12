<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RoomSchedule;
use SimpleSoftwareIO\QrCode\Generator;

class RoomQrController extends Controller
{
    public function index()
    {
        $rooms = RoomSchedule::currentSemester()
            ->select('room_no')
            ->distinct()
            ->orderBy('room_no')
            ->pluck('room_no');

        $qr = new Generator();
        $qrCodes = [];
        foreach ($rooms as $roomNo) {
            $qrCodes[$roomNo] = $qr->format('svg')->size(180)->margin(1)->generate((string) $roomNo);
        }

        return view('admin.rooms-qr.index', compact('rooms', 'qrCodes'));
    }

    public function print(int $roomNo)
    {
        $exists = RoomSchedule::currentSemester()->where('room_no', $roomNo)->exists();
        abort_unless($exists, 404);

        $svg = (new Generator())->format('svg')->size(400)->margin(2)->generate((string) $roomNo);

        return view('admin.rooms-qr.print', compact('roomNo', 'svg'));
    }
}
