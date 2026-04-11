<?php

namespace App\Services;

use App\Jobs\SendLateNotificationJob;
use App\Models\AttendanceLog;
use App\Models\Classroom;
use App\Models\Instructor;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AttendanceService
{
    public function processAttendance(
        Instructor $instructor,
        Classroom $classroom,
        ?float $latitude = null,
        ?float $longitude = null,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): array {
        $now = Carbon::now();
        $dayOfWeek = $now->dayOfWeek;

        // Find the current schedule for this instructor
        $schedule = Schedule::where('instructor_id', $instructor->id)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->where('start_time', '<=', $now->format('H:i:s'))
            ->where('end_time', '>=', $now->format('H:i:s'))
            ->first();

        // Also check for upcoming lecture within grace period (instructor arrives early)
        if (!$schedule) {
            $gracePeriod = config('attendance.grace_period_minutes', 11);
            $schedule = Schedule::where('instructor_id', $instructor->id)
                ->where('day_of_week', $dayOfWeek)
                ->where('is_active', true)
                ->where('start_time', '<=', $now->copy()->addMinutes($gracePeriod)->format('H:i:s'))
                ->where('start_time', '>=', $now->format('H:i:s'))
                ->first();
        }

        // No lecture scheduled
        if (!$schedule) {
            $log = $this->createLog($instructor, $classroom, null, 'no_lecture', $now, 0, $latitude, $longitude, null, $ipAddress, $userAgent);
            return [
                'status' => 'no_lecture',
                'message' => __('attendance.no_lecture_now'),
                'log' => $log,
            ];
        }

        // Check for duplicate scans
        if (config('attendance.prevent_duplicate_scans')) {
            $existing = AttendanceLog::where('instructor_id', $instructor->id)
                ->where('schedule_id', $schedule->id)
                ->whereDate('scanned_at', today())
                ->whereIn('status', ['present', 'late'])
                ->first();

            if ($existing) {
                return [
                    'status' => 'duplicate',
                    'message' => __('attendance.already_scanned'),
                    'log' => $existing,
                ];
            }
        }

        // Wrong classroom
        if ($schedule->classroom_id !== $classroom->id) {
            $correctClassroom = $schedule->classroom;
            $log = $this->createLog($instructor, $classroom, $schedule, 'wrong_classroom', $now, 0, $latitude, $longitude, null, $ipAddress, $userAgent,
                "Expected: {$correctClassroom->name}"
            );

            return [
                'status' => 'wrong_classroom',
                'message' => __('attendance.wrong_classroom'),
                'correct_classroom' => $correctClassroom->fullName(),
                'log' => $log,
            ];
        }

        // GPS validation
        $gpsValid = null;
        if (config('attendance.gps_validation_enabled') && $latitude && $longitude) {
            $gpsValid = $this->validateGps($classroom, $latitude, $longitude);
        }

        // Calculate delay
        $startTime = Carbon::parse($schedule->start_time);
        $delayMinutes = max(0, (int) $now->diffInMinutes($startTime, false) * -1);
        $gracePeriod = config('attendance.grace_period_minutes', 11);

        // Determine status
        if ($delayMinutes > $gracePeriod) {
            $status = 'late';
            $log = $this->createLog($instructor, $classroom, $schedule, 'late', $now, $delayMinutes, $latitude, $longitude, $gpsValid, $ipAddress, $userAgent);

            // Dispatch late notification
            SendLateNotificationJob::dispatch($log);

            return [
                'status' => 'late',
                'message' => __('attendance.marked_late', ['minutes' => $delayMinutes]),
                'delay_minutes' => $delayMinutes,
                'schedule' => $schedule,
                'log' => $log,
            ];
        }

        // Success - on time
        $log = $this->createLog($instructor, $classroom, $schedule, 'present', $now, $delayMinutes, $latitude, $longitude, $gpsValid, $ipAddress, $userAgent);

        return [
            'status' => 'present',
            'message' => __('attendance.success'),
            'schedule' => $schedule,
            'log' => $log,
        ];
    }

    protected function createLog(
        Instructor $instructor,
        Classroom $classroom,
        ?Schedule $schedule,
        string $status,
        Carbon $scannedAt,
        int $delayMinutes,
        ?float $latitude,
        ?float $longitude,
        ?bool $gpsValid,
        ?string $ipAddress,
        ?string $userAgent,
        ?string $notes = null
    ): AttendanceLog {
        return AttendanceLog::create([
            'instructor_id' => $instructor->id,
            'schedule_id' => $schedule?->id,
            'classroom_id' => $classroom->id,
            'status' => $status,
            'scanned_at' => $scannedAt,
            'delay_minutes' => $delayMinutes,
            'scan_latitude' => $latitude,
            'scan_longitude' => $longitude,
            'gps_valid' => $gpsValid,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'notes' => $notes,
        ]);
    }

    protected function validateGps(Classroom $classroom, float $lat, float $lng): bool
    {
        if (!$classroom->latitude || !$classroom->longitude) {
            return true; // No GPS data for classroom, skip validation
        }

        $distance = $this->haversineDistance(
            $classroom->latitude, $classroom->longitude,
            $lat, $lng
        );

        return $distance <= ($classroom->gps_radius_meters ?: config('attendance.gps_max_distance', 100));
    }

    protected function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000; // meters
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    public function checkMissedAttendance(): void
    {
        $now = Carbon::now();
        $dayOfWeek = $now->dayOfWeek;
        $gracePeriod = config('attendance.grace_period_minutes', 11);

        // Find schedules that started more than grace_period ago and have no attendance
        $cutoffTime = $now->copy()->subMinutes($gracePeriod)->format('H:i:s');

        $missedSchedules = Schedule::where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->where('start_time', '<=', $cutoffTime)
            ->where('end_time', '>=', $now->format('H:i:s'))
            ->whereDoesntHave('attendanceLogs', function ($query) {
                $query->whereDate('scanned_at', today())
                    ->whereIn('status', ['present', 'late']);
            })
            ->with(['instructor', 'classroom'])
            ->get();

        foreach ($missedSchedules as $schedule) {
            // Check if we already created a missed log today
            $existingMissed = AttendanceLog::where('schedule_id', $schedule->id)
                ->where('status', 'missed')
                ->whereDate('scanned_at', today())
                ->exists();

            if (!$existingMissed) {
                $log = AttendanceLog::create([
                    'instructor_id' => $schedule->instructor_id,
                    'schedule_id' => $schedule->id,
                    'classroom_id' => $schedule->classroom_id,
                    'status' => 'missed',
                    'scanned_at' => $now,
                    'delay_minutes' => (int) $now->diffInMinutes(Carbon::parse($schedule->start_time)),
                ]);

                SendLateNotificationJob::dispatch($log);

                Log::info("Missed attendance recorded for instructor {$schedule->instructor_id}, schedule {$schedule->id}");
            }
        }
    }
}
