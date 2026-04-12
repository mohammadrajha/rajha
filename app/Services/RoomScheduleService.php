<?php

namespace App\Services;

use App\Models\DepartmentEmail;
use App\Models\RoomSchedule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RoomScheduleService
{
    protected const SEMESTER = 20252;

    /**
     * Fetch room schedule from API, filter to current semester, and persist.
     *
     * Takes the public ROOM_CODE (e.g. "10018"), resolves the internal
     * ROOM_NO (room_id) from stored data, and calls the single-room API
     * with that internal id. Throws if no mapping is known yet — the
     * caller is expected to fall back to already-stored data.
     */
    public function getSchedule(string $roomCode): array
    {
        $baseUrl = rtrim(config('attendance.room_api_base_url', ''), '/');

        if (empty($baseUrl)) {
            throw new \RuntimeException('Room API base URL is not configured.');
        }

        $roomId = RoomSchedule::where('room_no', $roomCode)
            ->currentSemester()
            ->value('room_id');

        if (!$roomId) {
            throw new \RuntimeException("Unknown room code: {$roomCode}");
        }

        $response = Http::timeout(config('attendance.api_timeout', 30))
            ->get("{$baseUrl}/api/rooms/{$roomId}/full-details");

        if (!$response->successful()) {
            Log::warning("Room API error for room_id {$roomId} (code {$roomCode}): HTTP {$response->status()}");
            throw new \RuntimeException("Failed to fetch room schedule (HTTP {$response->status()}).");
        }

        $data = $response->json();

        if (!is_array($data)) {
            return [];
        }

        // Filter to current semester only
        $filtered = array_values(array_filter($data, function (array $record) {
            return ($record['semester'] ?? null) == self::SEMESTER;
        }));

        // Persist to database
        $this->storeSchedules($roomCode, $roomId, $filtered);

        return $filtered;
    }

    /**
     * Store fetched schedule records, replacing old data for this room.
     */
    protected function storeSchedules(string $roomCode, int $roomId, array $records): void
    {
        // Remove old records for this room+semester so we always have fresh data
        RoomSchedule::where('room_no', $roomCode)
            ->where('semester', self::SEMESTER)
            ->delete();

        foreach ($records as $record) {
            RoomSchedule::create([
                'room_id'         => $record['room_id'] ?? $roomId,
                'room_no'         => (string) ($record['room_no'] ?? $roomCode),
                'room_desc'       => $record['room_desc'] ?? null,
                'day'             => $record['day'] ?? '',
                'start_time'      => $record['start_time'] ?? '',
                'end_time'        => $record['end_time'] ?? '',
                'semester'        => self::SEMESTER,
                'dept_no'         => $record['dept_no'] ?? 0,
                'course_name'     => $record['course_name'] ?? '',
                'instructor_name' => $record['instructor_name'] ?? '',
            ]);

            // Auto-create department_emails row if it doesn't exist yet
            $deptNo = $record['dept_no'] ?? null;
            if ($deptNo) {
                DepartmentEmail::firstOrCreate(
                    ['dept_no' => $deptNo],
                    ['dept_name' => null, 'head_email' => null, 'head_name' => null]
                );
            }
        }
    }

    /**
     * Get stored schedule for a room (from DB, no API call).
     */
    public function getStoredSchedule(string $roomCode): \Illuminate\Database\Eloquent\Collection
    {
        return RoomSchedule::forRoom($roomCode)->currentSemester()->get();
    }

    /**
     * Find the current lecture for an instructor in a specific room.
     */
    public function findCurrentLecture(string $instructorName, string $roomCode, string $currentDay, string $currentTime): ?RoomSchedule
    {
        return RoomSchedule::where('instructor_name', $instructorName)
            ->where('room_no', $roomCode)
            ->where('day', $currentDay)
            ->where('semester', self::SEMESTER)
            ->where('start_time', '<=', $currentTime)
            ->where('end_time', '>=', $currentTime)
            ->first();
    }

    /**
     * Get all schedules for an instructor this semester.
     */
    public function getInstructorSchedules(string $instructorName): \Illuminate\Database\Eloquent\Collection
    {
        return RoomSchedule::forInstructor($instructorName)
            ->currentSemester()
            ->orderBy('day')
            ->orderBy('start_time')
            ->get();
    }
}
