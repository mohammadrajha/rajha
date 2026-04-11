<?php

namespace App\Console\Commands;

use App\Services\AttendanceService;
use Illuminate\Console\Command;

class CheckMissedAttendance extends Command
{
    protected $signature = 'attendance:check-missed';
    protected $description = 'Check for missed attendance and send notifications';

    public function handle(AttendanceService $service): int
    {
        $this->info('Checking for missed attendance...');
        $service->checkMissedAttendance();
        $this->info('Done.');

        return Command::SUCCESS;
    }
}
