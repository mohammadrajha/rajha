<?php

namespace App\Mail;

use App\Models\AttendanceLog;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LateAttendanceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public AttendanceLog $log
    ) {}

    public function envelope(): Envelope
    {
        $status = $this->log->status === 'missed' ? 'Missed' : 'Late';
        return new Envelope(
            subject: "[Attendance Alert] {$status}: {$this->log->instructor_name} - {$this->log->course_name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.late-attendance',
        );
    }
}
