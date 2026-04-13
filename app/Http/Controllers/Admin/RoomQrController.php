<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RoomSchedule;
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Generator;

class RoomQrController extends Controller
{
    public function index()
    {
        // Distinct classrooms by public ROOM_CODE, with one representative room_desc each.
        // Skip rows where room_no is missing so the QR generator never receives an
        // empty string (bacon-qr-code throws "Found empty contents" on blank input).
        $rooms = RoomSchedule::currentSemester()
            ->whereNotNull('room_no')
            ->where('room_no', '!=', '')
            ->selectRaw('room_no, MIN(room_desc) as room_desc')
            ->groupBy('room_no')
            ->orderBy('room_no')
            ->get();

        $qrCodes = [];
        foreach ($rooms as $room) {
            $raw   = (string) $room->room_no;
            $clean = self::sanitizeRoomCode($raw);
            // Key the map by the raw DB value so the blade view's lookup matches.
            $qrCodes[$raw] = $clean === '' ? null : self::generateQrSvg($clean, 180);
        }

        return view('admin.rooms-qr.index', compact('rooms', 'qrCodes'));
    }

    public function print(string $roomNo)
    {
        $roomNo = self::sanitizeRoomCode($roomNo);
        abort_if($roomNo === '', 404);

        $schedule = RoomSchedule::currentSemester()
            ->where('room_no', $roomNo)
            ->first();
        abort_unless($schedule !== null, 404);

        $svg = self::generateQrSvg($roomNo, 400);
        abort_if($svg === null, 500, 'Failed to generate QR code.');

        return view('admin.rooms-qr.print', [
            'roomNo'   => $roomNo,
            'roomDesc' => $schedule->room_desc,
            'svg'      => $svg,
        ]);
    }

    /**
     * Reduce a room code to plain ASCII alphanumerics plus `_` and `-`.
     *
     * bacon-qr-code defaults to ISO-8859-1 byte-mode encoding. Any non-Latin
     * character in the input (Arabic-Indic digits, RTL marks, zero-width
     * characters, stray Unicode leaked from the upstream API) triggers
     * "Could not encode content to ISO-8859-1". Room codes are ASCII in our
     * domain, and the scan page already accepts `[A-Za-z0-9_\-]+`, so
     * sanitizing here keeps the generation <-> scan round-trip compatible.
     */
    private static function sanitizeRoomCode(?string $value): string
    {
        if ($value === null) {
            return '';
        }
        return (string) preg_replace('/[^A-Za-z0-9_\-]/', '', trim($value));
    }

    /**
     * Render a QR SVG for the given ASCII code. Returns null on failure so
     * the view can show a graceful placeholder instead of 500-ing.
     */
    private static function generateQrSvg(string $code, int $size): ?string
    {
        if ($code === '') {
            return null;
        }
        try {
            return (new Generator())
                ->format('svg')
                ->size($size)
                ->margin(1)
                ->errorCorrection('M')
                // Force UTF-8 ECI mode so byte-mode encoding never falls back
                // to the broken ISO-8859-1 path even if input ever contains
                // an unexpected character.
                ->encoding('UTF-8')
                ->generate($code);
        } catch (\Throwable $e) {
            Log::warning('QR generation failed for room', [
                'room_no' => $code,
                'error'   => $e->getMessage(),
            ]);
            return null;
        }
    }
}
