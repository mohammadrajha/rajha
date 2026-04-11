<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RoomScheduleService
{
    protected const SEMESTER = 20252;

    public function getSchedule(int $roomNo): array
    {
        $baseUrl = rtrim(config('attendance.room_api_base_url', ''), '/');

        if (empty($baseUrl)) {
            throw new \RuntimeException('Room API base URL is not configured.');
        }

        $response = Http::timeout(config('attendance.api_timeout', 30))
            ->get("{$baseUrl}/api/rooms/{$roomNo}/full-details");

        if (!$response->successful()) {
            Log::warning("Room API error for room {$roomNo}: HTTP {$response->status()}");
            throw new \RuntimeException("Failed to fetch room schedule (HTTP {$response->status()}).");
        }

        $data = $response->json();

        if (!is_array($data)) {
            return [];
        }

        // Filter to current semester only
        return array_values(array_filter($data, function (array $record) {
            return ($record['semester'] ?? null) == self::SEMESTER;
        }));
    }
}
