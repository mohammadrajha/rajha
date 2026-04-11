<?php

namespace App\Jobs;

use App\Mail\LateAttendanceMail;
use App\Models\AttendanceLog;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendLateNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public AttendanceLog $log
    ) {}

    public function handle(): void
    {
        $this->log->load(['instructor.department', 'schedule', 'classroom']);

        $department = $this->log->instructor->department;
        if (!$department) {
            Log::warning("No department for instructor {$this->log->instructor_id}, skipping notification");
            return;
        }

        // Send to head of department
        $headUser = $department->head;
        if ($headUser) {
            Mail::to($headUser->email)->queue(new LateAttendanceMail($this->log));
            Log::info("Late notification sent to HOD {$headUser->email} for instructor {$this->log->instructor->name}");
        }

        // Also notify admins
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            Mail::to($admin->email)->queue(new LateAttendanceMail($this->log));
        }
    }
}
