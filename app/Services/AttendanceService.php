<?php

namespace App\Services;

use App\Jobs\SendLateNotificationJob;
use App\Models\AttendanceLog;
use App\Models\RoomSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AttendanceService
{
    protected const DAY_MAP = [
        0 => 'الأحد',
        1 => 'الإثنين',
        2 => 'الثلاثاء',
        3 => 'الأربعاء',
        4 => 'الخميس',
        5 => 'الجمعة',
        6 => 'السبت',
    ];

    /**
     * Process attendance when an instructor scans a room QR code.
     */
    public function processAttendance(
        string $instructorName,
        string $roomNo,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): array {
        $now = Carbon::now();
        $currentDay = self::DAY_MAP[$now->dayOfWeek] ?? '';
        $currentTime = $now->format('H:i');

        // Find matching lecture for this instructor in this room right now
        $lecture = RoomSchedule::where('instructor_name', $instructorName)
            ->where('room_no', $roomNo)
            ->where('day', $currentDay)
            ->where('semester', 20252)
            ->where('start_time', '<=', $currentTime)
            ->where('end_time', '>=', $currentTime)
            ->first();

        // Also check if instructor has a lecture in a DIFFERENT room right now
        $correctLecture = null;
        if (!$lecture) {
            $correctLecture = RoomSchedule::where('instructor_name', $instructorName)
                ->where('day', $currentDay)
                ->where('semester', 20252)
                ->where('start_time', '<=', $currentTime)
                ->where('end_time', '>=', $currentTime)
                ->first();
        }

        // No lecture at this time for this instructor
        if (!$lecture && !$correctLecture) {
            $log = $this->createLog($instructorName, $roomNo, null, 'no_lecture', $now, 0, $ipAddress, $userAgent);
            return [
                'status' => 'no_lecture',
                'message' => __('attendance.no_lecture_now'),
                'log' => $log,
            ];
        }

        // Wrong classroom - instructor has a lecture but in a different room
        if (!$lecture && $correctLecture) {
            $log = $this->createLog($instructorName, $roomNo, $correctLecture, 'wrong_classroom', $now, 0, $ipAddress, $userAgent,
                "Expected room: {$correctLecture->room_no}"
            );
            return [
                'status' => 'wrong_classroom',
                'message' => __('attendance.wrong_classroom'),
                'correct_room' => $correctLecture->room_no,
                'log' => $log,
            ];
        }

        // Check for duplicate scan today
        $existing = AttendanceLog::where('instructor_name', $instructorName)
            ->where('room_schedule_id', $lecture->id)
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

        // Calculate delay
        $startTime = Carbon::createFromFormat('H:i', $lecture->start_time);
        $delayMinutes = max(0, (int) $now->diffInMinutes($startTime, false) * -1);
        $gracePeriod = config('attendance.grace_period_minutes', 11);

        if ($delayMinutes > $gracePeriod) {
            $log = $this->createLog($instructorName, $roomNo, $lecture, 'late', $now, $delayMinutes, $ipAddress, $userAgent);
            SendLateNotificationJob::dispatch($log);

            return [
                'status' => 'late',
                'message' => __('attendance.marked_late', ['minutes' => $delayMinutes]),
                'delay_minutes' => $delayMinutes,
                'lecture' => $lecture,
                'log' => $log,
            ];
        }

        // Present on time
        $log = $this->createLog($instructorName, $roomNo, $lecture, 'present', $now, $delayMinutes, $ipAddress, $userAgent);

        return [
            'status' => 'present',
            'message' => __('attendance.success'),
            'lecture' => $lecture,
            'log' => $log,
        ];
    }

    protected function createLog(
        string $instructorName,
        string $roomNo,
        ?RoomSchedule $lecture,
        string $status,
        Carbon $scannedAt,
        int $delayMinutes,
        ?string $ipAddress,
        ?string $userAgent,
        ?string $notes = null
    ): AttendanceLog {
        return AttendanceLog::create([
            'instructor_name' => $instructorName,
            'room_no' => $roomNo,
            'room_schedule_id' => $lecture?->id,
            'dept_no' => $lecture?->dept_no,
            'course_name' => $lecture?->course_name,
            'status' => $status,
            'scanned_at' => $scannedAt,
            'delay_minutes' => $delayMinutes,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'notes' => $notes,
        ]);
    }
}
