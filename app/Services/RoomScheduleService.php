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
     */
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
        $filtered = array_values(array_filter($data, function (array $record) {
            return ($record['semester'] ?? null) == self::SEMESTER;
        }));

        // Persist to database
        $this->storeSchedules($roomNo, $filtered);

        return $filtered;
    }

    /**
     * Store fetched schedule records, replacing old data for this room.
     */
    protected function storeSchedules(int $roomNo, array $records): void
    {
        // Remove old records for this room+semester so we always have fresh data
        RoomSchedule::where('room_no', $roomNo)
            ->where('semester', self::SEMESTER)
            ->delete();

        foreach ($records as $record) {
            RoomSchedule::create([
                'room_no' => $record['room_no'] ?? $roomNo,
                'day' => $record['day'] ?? '',
                'start_time' => $record['start_time'] ?? '',
                'end_time' => $record['end_time'] ?? '',
                'semester' => self::SEMESTER,
                'dept_no' => $record['dept_no'] ?? 0,
                'course_name' => $record['course_name'] ?? '',
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
    public function getStoredSchedule(int $roomNo): \Illuminate\Database\Eloquent\Collection
    {
        return RoomSchedule::forRoom($roomNo)->currentSemester()->get();
    }

    /**
     * Find the current lecture for an instructor in a specific room.
     */
    public function findCurrentLecture(string $instructorName, int $roomNo, string $currentDay, string $currentTime): ?RoomSchedule
    {
        return RoomSchedule::where('instructor_name', $instructorName)
            ->where('room_no', $roomNo)
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
