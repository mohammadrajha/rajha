<?php

namespace App\Jobs;

use App\Mail\LateAttendanceMail;
use App\Models\AttendanceLog;
use App\Models\DepartmentEmail;
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
        // Find the HOD email for this department
        if ($this->log->dept_no) {
            $dept = DepartmentEmail::where('dept_no', $this->log->dept_no)->first();

            if ($dept && $dept->head_email) {
                Mail::to($dept->head_email)->queue(new LateAttendanceMail($this->log));
                Log::info("Late notification sent to HOD {$dept->head_email} for instructor {$this->log->instructor_name}");
            } else {
                Log::warning("No HOD email configured for dept_no {$this->log->dept_no}");
            }
        }

        // Also notify admins
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            Mail::to($admin->email)->queue(new LateAttendanceMail($this->log));
        }
    }
}
