<?php

namespace App\Services;

use App\Models\Classroom;
use Illuminate\Support\Facades\Crypt;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeService
{
    public function generatePayload(Classroom $classroom): string
    {
        $data = [
            'cid' => $classroom->id,
            'token' => $classroom->qr_token,
            't' => now()->timestamp,
        ];

        return Crypt::encryptString(json_encode($data));
    }

    public function decryptPayload(string $encrypted): ?array
    {
        try {
            $json = Crypt::decryptString($encrypted);
            $data = json_decode($json, true);

            if (!isset($data['cid'], $data['token'])) {
                return null;
            }

            return $data;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function validatePayload(array $payload): ?Classroom
    {
        $classroom = Classroom::find($payload['cid']);

        if (!$classroom || $classroom->qr_token !== $payload['token']) {
            return null;
        }

        if (!$classroom->is_active) {
            return null;
        }

        return $classroom;
    }

    public function generateSvg(Classroom $classroom, int $size = null): string
    {
        $size = $size ?? config('attendance.qr_size', 300);
        $scanUrl = route('scan.process', ['payload' => $this->generatePayload($classroom)]);

        return QrCode::format('svg')
            ->size($size)
            ->errorCorrection('H')
            ->generate($scanUrl);
    }

    public function generatePrintable(Classroom $classroom): string
    {
        $payload = $this->generatePayload($classroom);
        $scanUrl = route('scan.process', ['payload' => $payload]);

        return QrCode::format('svg')
            ->size(400)
            ->errorCorrection('H')
            ->margin(2)
            ->generate($scanUrl);
    }
}
